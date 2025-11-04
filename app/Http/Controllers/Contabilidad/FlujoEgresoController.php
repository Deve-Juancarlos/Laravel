<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ContabilidadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FlujoEgresoController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $contabilidadService;
    protected $sessionKey = 'flujo_egreso';

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->middleware('auth');
        $this->contabilidadService = $contabilidadService;
    }

  
    public function showPaso1(Request $request)
    {
        session()->forget($this->sessionKey);
        
        $proveedorId = $request->query('proveedor_id');
        $proveedor = null;
        if ($proveedorId) {
             $proveedor = DB::connection($this->connection)->table('Proveedores')
                            ->where('CodProv', $proveedorId)->first();
        }

        if (!$proveedor) {
           
             return redirect()->route('contador.cxp.index')->with('error', 'Debe seleccionar un proveedor desde la lista de CxP.');
        }

        $cuentasBancarias = DB::connection($this->connection)->table('Bancos')->get();

        session([
            $this->sessionKey => [ 'proveedor' => $proveedor ]
        ]);

        // Esta es la VISTA 4.1
        return view('egresos.flujo.paso1_registrar_pago', [
            'proveedor' => $proveedor,
            'cuentasBancarias' => $cuentasBancarias
        ]);
    }

    public function handlePaso1(Request $request)
    {
        $flujoData = session($this->sessionKey);
        if (empty($flujoData['proveedor'])) {
            return redirect()->route('contador.cxp.index')->with('error', 'Seleccione un proveedor.');
        }

        $validator = Validator::make($request->all(), [
            'monto_pagado'   => 'required|numeric|min:0.01',
            'fecha_pago'     => 'required|date',
            'metodo_pago'    => 'required|string', 
            'cuenta_origen'  => 'required|string|exists:sqlsrv.Bancos,Cuenta',
            'referencia'     => 'required|string|max:50', 
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $flujoData['pago'] = $request->only('monto_pagado', 'fecha_pago', 'metodo_pago', 'cuenta_origen', 'referencia');
        session([$this->sessionKey => $flujoData]);

        return redirect()->route('contador.flujo.egresos.paso2');
    }


    public function showPaso2()
    {
        $flujoData = session($this->sessionKey);
        if (empty($flujoData['pago'])) {
            return redirect()->route('contador.cxp.index')->with('error', 'Registre el monto del pago.');
        }

        $proveedor = $flujoData['proveedor'];
        $pago = $flujoData['pago'];

        $facturasPendientes = DB::connection($this->connection)->table('CtaProveedor')
            ->where('CodProv', $proveedor->CodProv)
            ->where('Saldo', '>', 0)
            ->orderBy('FechaV', 'asc')
            ->get()
            ->map(function ($factura) {
                
                $factura->composite_key = $factura->Documento . '_' . $factura->Tipo . '_' . $factura->CodProv;
                $factura->FechaEmision = Carbon::parse($factura->FechaF);
                $factura->FechaV = Carbon::parse($factura->FechaV);
                $factura->Total = $factura->Importe;
                return $factura;
            });
            
        $aplicaciones = $flujoData['aplicaciones'] ?? [];

       
        return view('egresos.flujo.paso2_aplicar_pago', compact('proveedor', 'pago', 'facturasPendientes', 'aplicaciones'));
    }

 
    public function handlePaso2(Request $request)
    {
        $flujoData = session($this->sessionKey);
        if (empty($flujoData['pago'])) {
            return redirect()->route('contador.cxp.index')->with('error', 'Sesión expirada.');
        }

        $aplicaciones = collect($request->input('aplicaciones', []))
                            ->filter(fn ($monto) => is_numeric($monto) && $monto > 0)
                            ->map(fn ($monto) => round($monto, 2));

        $flujoData['aplicaciones'] = $aplicaciones;
        session([$this->sessionKey => $flujoData]);

        return redirect()->route('contador.flujo.egresos.paso3');
    }

    public function showPaso3()
    {
        $flujoData = session($this->sessionKey);
        if (empty($flujoData['aplicaciones'])) {
            return redirect()->route('contador.flujo.egresos.paso2')->with('error', 'Debe aplicar el pago.');
        }
        
        $resumen = [
            'proveedor' => $flujoData['proveedor'],
            'pago' => $flujoData['pago'],
            'aplicaciones' => [],
        ];

        $montoTotalAplicado = 0;
        foreach ($flujoData['aplicaciones'] as $compositeKey => $monto) {
            list($documento, $tipo, $codprov) = explode('_', $compositeKey);
            
            $factura = DB::connection($this->connection)->table('CtaProveedor')
                            ->where('Documento', $documento)->where('Tipo', $tipo)->where('CodProv', $codprov)
                            ->first();

            if ($factura) {
                $resumen['aplicaciones'][] = ['factura' => $factura, 'monto_aplicado' => $monto];
                $montoTotalAplicado += $monto;
            }
        }
        
        $resumen['pago']['monto_aplicado'] = $montoTotalAplicado;
        $resumen['pago']['diferencia'] = round((float)$flujoData['pago']['monto_pagado'] - $montoTotalAplicado, 2);

        
        return view('egresos.flujo.paso3_resumen_confirmar', compact('resumen'));
    }


    public function procesar(Request $request)
    {
        $flujoData = session($this->sessionKey);
        if (empty($flujoData)) {
            return redirect()->route('contador.cxp.index')->with('error', 'Sesión expirada.');
        }

        $proveedor = $flujoData['proveedor'];
        $pago = $flujoData['pago'];
        $aplicaciones = $flujoData['aplicaciones'] ?? collect();
        $montoTotalPagado = (float) $pago['monto_pagado'];
        $fechaPago = Carbon::parse($pago['fecha_pago']);

        DB::connection($this->connection)->beginTransaction();
        try {
            
           
            DB::connection($this->connection)->table('CtaBanco')->insert([
                'Tipo' => 2, 
                'Clase' => ($pago['metodo_pago'] == 'cheque') ? 1 : 3, 
                'Cuenta' => $pago['cuenta_origen'],
                'Documento' => $pago['referencia'], 
                'Monto' => $montoTotalPagado,
                'Fecha' => $fechaPago,
            ]);

         
            foreach ($aplicaciones as $compositeKey => $montoAplicado) {
                list($documento, $tipo, $codprov) = explode('_', $compositeKey);
                
                $afectado = DB::connection($this->connection)->table('CtaProveedor')
                    ->where('Documento', $documento)
                    ->where('Tipo', $tipo)
                    ->where('CodProv', $codprov)
                    ->where('Saldo', '>=', (float)$montoAplicado) 
                    ->decrement('Saldo', (float)$montoAplicado);
                
                if ($afectado == 0) {
                     throw new \Exception("No se pudo aplicar S/ {$montoAplicado} a la factura {$documento}. Saldo insuficiente o factura no encontrada.");
                }
            }


            $this->contabilidadService->registrarAsientoPagoProveedor(
                $pago['referencia'],
                $proveedor,
                $pago['cuenta_origen'],
                $montoTotalPagado,
                $fechaPago,
                Auth::id()
            );

     
            DB::connection($this->connection)->commit();
            session()->forget($this->sessionKey);
      
            return redirect()->route('contador.cxp.index')
                            ->with('success', 'Pago a Proveedor registrado (Egreso: ' . $pago['referencia'] . ') y Asiento Contable generado.');

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error('Error al procesar Pago a Proveedor: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('contador.flujo.egresos.paso3')
                            ->with('error', 'Error crítico al guardar: ' . $e->getMessage());
        }
    }
}