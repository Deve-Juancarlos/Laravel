<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Services\Contabilidad\FlujoCajaService; 
use App\Services\ReniecService; // 1. Importamos tu servicio
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;         
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FlujoCajaController extends Controller
{
    protected $connection = 'sqlsrv'; // Tu conexión
    protected $flujoCajaService;
    protected $reniecService; // 2. Propiedad para el servicio

    /**
     * Inyectamos ambos servicios
     */
    public function __construct(FlujoCajaService $flujoCajaService, ReniecService $reniecService)
    {
        $this->flujoCajaService = $flujoCajaService;
        $this->reniecService = $reniecService; // 3. Inyectamos
    }

    /*
    |--------------------------------------------------------------------------
    | PASO 1: IDENTIFICAR CLIENTE
    |--------------------------------------------------------------------------
    */

    public function showPaso1()
    {
        session()->forget('flujo_cobranza');
        return view('cobranzas.flujo.paso1_identificar_cliente'); 
    }

    public function handlePaso1(Request $request) // Esta función recibe el POST del Paso 1
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|integer|exists:sqlsrv.Clientes,Codclie' 
        ]);

        if ($validator->fails()) {
            return redirect()->route('contador.flujo.cobranzas.paso1')
                         ->withErrors($validator)
                         ->withInput();
        }

        session([
            'flujo_cobranza' => [
                'cliente_id' => $request->input('cliente_id')
            ]
        ]);

        return redirect()->route('contador.flujo.cobranzas.paso2');
    }

    /*
    |--------------------------------------------------------------------------
    | PASO 2: REGISTRAR INGRESO
    |--------------------------------------------------------------------------
    */
    public function showPaso2()
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['cliente_id'])) {
            return redirect()->route('contador.flujo.cobranzas.paso1')->with('error', 'Seleccione un cliente.');
        }

        $cliente = DB::connection($this->connection)->table('Clientes')
                     ->where('Codclie', $flujoData['cliente_id'])->first();
        
        $cuentasBancarias = DB::connection($this->connection)->table('Bancos')->get();
        $pago = $flujoData['pago'] ?? null;
        
        // Asumo que Vendedores son Empleados (como en tu ClientesController)
        $vendedores = DB::connection($this->connection)->table('Empleados')->get(); 

        // Series de PlanC_cobranza
        $seriesPlanilla = ['P001', 'P002', 'P003']; // Deberías sacar esto de una tabla maestra

        return view('cobranzas.flujo.paso2_registrar_pago', compact('cliente', 'cuentasBancarias', 'pago', 'vendedores', 'seriesPlanilla'));
    }

    public function handlePaso2(Request $request)
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['cliente_id'])) {
            return redirect()->route('contador.flujo.cobranzas.paso1')->with('error', 'Sesión expirada.');
        }

        $validator = Validator::make($request->all(), [
            'monto_pagado'      => 'required|numeric|min:0.01',
            'fecha_pago'        => 'required|date',
            'metodo_pago'       => 'required|string',
            'cuenta_destino'    => 'required|string|exists:sqlsrv.Bancos,Cuenta',
            'referencia'        => 'nullable|string|max:50',
            'serie_planilla'    => 'required|string|max:4',
            'vendedor_id'       => 'required|integer|exists:sqlsrv.Empleados,Codemp',
        ]);

        if ($validator->fails()) {
            return redirect()->route('contador.flujo.cobranzas.paso2')
                         ->withErrors($validator)
                         ->withInput();
        }

        session([
            'flujo_cobranza.pago' => $request->only(
                'monto_pagado', 'fecha_pago', 'metodo_pago', 
                'cuenta_destino', 'referencia', 'serie_planilla', 'vendedor_id'
            )
        ]);

        return redirect()->route('contador.flujo.cobranzas.paso3');
    }

    /*
    |--------------------------------------------------------------------------
    | PASO 3: APLICAR PAGO
    |--------------------------------------------------------------------------
    */
    public function showPaso3()
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['pago'])) {
            return redirect()->route('contador.flujo.cobranzas.paso2')->with('error', 'Registre el monto del pago.');
        }

        $cliente = DB::connection($this->connection)->table('Clientes')
                     ->where('Codclie', $flujoData['cliente_id'])->first();
        $pago = $flujoData['pago'];

        // Buscamos deudas en 'CtaCliente'
        $facturasPendientes = DB::connection($this->connection)->table('CtaCliente')
            ->where('CodClie', $cliente->Codclie)
            ->where('Saldo', '>', 0)
            ->orderBy('FechaV', 'asc') // Antiguas primero
            ->get()
            ->map(function ($factura) {
                // ID única para el formulario (Documento + Tipo)
                $factura->composite_key = $factura->Documento . '_' . $factura->Tipo;
                $factura->FechaEmision = Carbon::parse($factura->FechaF);
                $factura->FechaVencimiento = Carbon::parse($factura->FechaV);
                $factura->Total = $factura->Importe; 
                return $factura;
            });
            
        $aplicaciones = $flujoData['aplicaciones'] ?? [];

        return view('cobranzas.flujo.paso3_aplicar_pago', compact('cliente', 'pago', 'facturasPendientes', 'aplicaciones'));
    }

    public function handlePaso3(Request $request)
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['pago'])) {
            return redirect()->route('contador.flujo.cobranzas.paso2')->with('error', 'Sesión expirada.');
        }

        $aplicaciones = collect($request->input('aplicaciones', []))
                            ->filter(fn ($monto) => is_numeric($monto) && $monto > 0)
                            ->map(fn ($monto) => round($monto, 2));

        $adelanto = $request->input('guardar_como_adelanto') == 1;
        
        session([
            'flujo_cobranza.aplicaciones' => $aplicaciones,
            'flujo_cobranza.adelanto' => $adelanto
        ]);

        return redirect()->route('contador.flujo.cobranzas.paso4');
    }

    /*
    |--------------------------------------------------------------------------
    | PASO 4: RESUMEN Y CONFIRMACIÓN
    |--------------------------------------------------------------------------
    */
    public function showPaso4()
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['aplicaciones']) && empty($flujoData['adelanto'])) {
            return redirect()->route('contador.flujo.cobranzas.paso3')->with('error', 'Debe aplicar el pago.');
        }
        
        $resumen = [
            'cliente' => DB::connection($this->connection)->table('Clientes')->where('Codclie', $flujoData['cliente_id'])->first(),
            'pago' => $flujoData['pago'],
            'aplicaciones' => [],
            'adelanto' => 0
        ];

        $montoTotalAplicado = 0;

        if (!empty($flujoData['aplicaciones'])) {
            foreach ($flujoData['aplicaciones'] as $compositeKey => $monto) {
                list($documento, $tipo) = explode('_', $compositeKey);
                
                $factura = DB::connection($this->connection)->table('CtaCliente')
                            ->where('Documento', $documento)->where('Tipo', $tipo)->first();

                if ($factura) {
                    $factura->Numero = $factura->Documento;
                    $resumen['aplicaciones'][] = [
                        'factura' => $factura,
                        'monto_aplicado' => $monto
                    ];
                    $montoTotalAplicado += $monto;
                }
            }
        }

        if ($flujoData['adelanto']) {
            $resumen['adelanto'] = round($flujoData['pago']['monto_pagado'] - $montoTotalAplicado, 2);
        }
        
        return view('cobranzas.flujo.paso4_resumen_confirmar', compact('resumen'));
    }

    /**
     * PROCESA EL FLUJO COMPLETO (Corregido con tus tablas)
     */
    public function procesar(Request $request)
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData)) {
            return redirect()->route('contador.flujo.cobranzas.paso1')->with('error', 'Sesión expirada.');
        }

        try {
            DB::connection($this->connection)->beginTransaction();
            
            $clienteId = $flujoData['cliente_id'];
            $pago = $flujoData['pago'];
            $aplicaciones = $flujoData['aplicaciones'] ?? collect();
            $esAdelanto = $flujoData['adelanto'] ?? false;
            $montoTotalPagado = (float) $pago['monto_pagado'];
            $montoTotalAplicado = 0;
            $fechaPago = Carbon::parse($pago['fecha_pago']);
            $serie = $pago['serie_planilla'];
            
            // 1. Crear Cabecera de Planilla (PlanC_cobranza)
            $ultimoNum = DB::connection($this->connection)->table('PlanC_cobranza')
                            ->where('Serie', $serie)->max('Numero');
            $nextNum = str_pad((int)$ultimoNum + 1, 8, '0', STR_PAD_LEFT);
            $planillaNumeroCompleto = $serie . '-' . $nextNum;

            DB::connection($this->connection)->table('PlanC_cobranza')->insert([
                'Serie' => $serie,
                'Numero' => $nextNum,
                'Vendedor' => $pago['vendedor_id'],
                'FechaCrea' => now(),
                'FechaIng' => $fechaPago,
                'Confirmacion' => 1,
                'Impreso' => 0,
            ]);

            // 2. Insertar movimiento en el Banco (CtaBanco)
            $clasePago = 3; // 3 = Transferencia (Default)
            if ($pago['metodo_pago'] == 'cheque') $clasePago = 1;
            if ($pago['metodo_pago'] == 'efectivo' || $pago['metodo_pago'] == 'deposito') $clasePago = 2;

            DB::connection($this->connection)->table('CtaBanco')->insert([
                'Tipo' => 1, // 1 = Ingreso
                'Clase' => $clasePago,
                'Cuenta' => $pago['cuenta_destino'],
                'Documento' => $planillaNumeroCompleto, 
                'Monto' => $montoTotalPagado,
                'Fecha' => $fechaPago,
            ]);

            // 3. Recorrer aplicaciones y guardar en 'PlanD_cobranza' y actualizar 'CtaCliente'
            if ($aplicaciones->isNotEmpty()) {
                foreach ($aplicaciones as $compositeKey => $montoAplicado) {
                    list($documento, $tipo) = explode('_', $compositeKey);
                    $montoAplicado = (float) $montoAplicado;

                    $factura = DB::connection($this->connection)->table('CtaCliente')
                                ->where('Documento', $documento)->where('Tipo', $tipo)->first();
                    if (!$factura) continue; 

                    DB::connection($this->connection)->table('PlanD_cobranza')->insert([
                        'Serie' => $serie,
                        'Numero' => $nextNum,
                        'CodClie' => $clienteId,
                        'Documento' => $documento,
                        'TipoDoc' => $tipo,
                        'FechaFac' => $factura->FechaF,
                        'Valor' => $factura->Importe,
                        'NroRecibo' => $planillaNumeroCompleto,
                        'Descuento' => 0,
                        'Efectivo' => ($pago['metodo_pago'] != 'cheque') ? $montoAplicado : 0,
                        'Cheque' => ($pago['metodo_pago'] == 'cheque') ? $montoAplicado : 0,
                        'NroCheque' => ($pago['metodo_pago'] == 'cheque') ? $pago['referencia'] : null,
                        'Moneda' => 1, 'Cambio' => 1, // Asumo Soles
                    ]);
                    
                    DB::connection($this->connection)->table('CtaCliente')
                        ->where('Documento', $documento)
                        ->where('Tipo', $tipo)
                        ->decrement('Saldo', $montoAplicado);
                    
                    $montoTotalAplicado += $montoAplicado;
                }
            }

            // 4. Registrar el adelanto (si existe)
            if ($esAdelanto) {
                $montoAdelanto = round($montoTotalPagado - $montoTotalAplicado, 2);
                if ($montoAdelanto > 0.01) {
                    $adelantoDocNum = 'ANT-' . $nextNum;
                    DB::connection($this->connection)->table('CtaCliente')->insert([
                        'Documento' => $adelantoDocNum,
                        'Tipo' => 99, // 99 = Tipo "Anticipo" (Verifica tu tabla 'Tablas' codtabla=3)
                        'CodClie' => $clienteId,
                        'FechaF' => $fechaPago,
                        'FechaV' => $fechaPago,
                        'Importe' => -$montoAdelanto,
                        'Saldo' => -$montoAdelanto,
                    ]);
                }
            }
            
            DB::connection($this->connection)->commit();
            session()->forget('flujo_cobranza');
            
            return redirect()->route('dashboard.contador') // Ruta de tu dashboard
                             ->with('success', 'Cobranza registrada exitosamente (Planilla: ' . $planillaNumeroCompleto . ')');

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error('Error al procesar cobranza (sqlsrv): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->route('contador.flujo.cobranzas.paso4')
                             ->with('error', 'Error crítico al guardar en BD: ' . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE API (AJAX) - ¡CORRECCIÓN FINAL!
    |--------------------------------------------------------------------------
    */

    public function searchClientes(Request $request)
    {
        $query = $request->input('query');
        if (strlen($query) < 3) return response()->json([]);

        // 1. Usamos tu ReniecService para buscar en la BD local
        // El servicio ya usa 'sqlsrv', 'Clientes', 'Razon' y 'RucDni'
        $data = $this->reniecService->buscarEnBaseLocal($query);
        $clientes = $data['clientes']; // Esto es una Colección

        // 2. Mapeamos los resultados para añadir la deuda (que el servicio no trae)
        $results = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->Codclie,
                'razon_social' => $cliente->Razon,
                'ruc' => $cliente->documento, // Tu servicio renombra 'RucDni' a 'documento'
                'deuda_total' => $this->getDeudaCliente($cliente->Codclie)
            ];
        });

        return response()->json($results);
    }

    private function getDeudaCliente($cliente_id)
    {
        // La deuda real es la suma de saldos en CtaCliente
        $saldo_actual = DB::connection($this->connection)->table('CtaCliente')
            ->where('CodClie', $cliente_id)
            ->sum('Saldo');

        return round($saldo_actual, 2) ?? 0.00;
    }
}