<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Services\Contabilidad\FlujoCajaService; 
use App\Services\ReniecService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\ContabilidadService;

class FlujoCajaController extends Controller
{
    protected $connection = 'sqlsrv'; 
    protected $flujoCajaService;
    protected $reniecService; 
    protected $contabilidadService;

    public function __construct(
        FlujoCajaService $flujoCajaService, 
        ReniecService $reniecService,
        ContabilidadService $contabilidadService // <-- 4. INYECTAR SERVICIO
    ) {
        $this->flujoCajaService = $flujoCajaService;
        $this->reniecService = $reniecService; 
        $this->contabilidadService = $contabilidadService; // <-- 5. ASIGNAR SERVICIO
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
        $clienteInput = $request->input('cliente_id');
        $clienteId = null;

        // 1. EL USUARIO HIZO CLIC EN UN CLIENTE "NUEVO" (ej: 'NUEVO-1047...')
        if (Str::startsWith($clienteInput, 'NUEVO-')) {
            $documento = Str::after($clienteInput, 'NUEVO-');
            $isRuc = strlen($documento) == 11;
            
            // 2. REVISAMOS QUE NO EXISTA YA (POR SI ACASO)
            $existente = DB::connection($this->connection)->table('Clientes')
                           ->where('Documento', $documento)->first();
            
            if ($existente) {
                // Si ya existe, solo usamos su ID
                $clienteId = $existente->Codclie;
            } else {
                // 3. NO EXISTE. LLAMAMOS A LA API (¡QUE AHORA SÍ FUNCIONARÁ!)
                Log::info("Registrando nuevo cliente desde flujo: " . $documento);
                $apiData = $isRuc ? $this->reniecService->consultarRUC($documento) : $this->reniecService->consultarDNI($documento);

                if (!$apiData) { // La API (corregida) devuelve NULL si falla
                     return redirect()->route('contador.flujo.cobranzas.paso1')
                                  ->with('error', 'No se pudo obtener datos de RENIEC/SUNAT para ' . $documento);
                }

                // 4. ¡LO CREAMOS DIRECTAMENTE EN LA TABLA 'Clientes'!
                $clienteId = DB::connection($this->connection)->table('Clientes')->insertGetId([
                    'tipoDoc' => $isRuc ? 'R' : 'D',
                    'Documento' => $documento,
                    // Usamos los alias 'razon_social' y 'address' que definimos en ReniecService
                    'Razon' => $apiData['razon_social'] ?? 'N/A', 
                    'Direccion' => $apiData['address'] ?? 'N/A',
                    'Maymin' => 0,
                    'Fecha' => now(),
                    'Zona' => '001', // Zona por defecto
                    'Activo' => 1
                ]);
            }

        } else {
            // 5. SI ES UN CLIENTE ANTIGUO, VALIDAMOS
            $validator = Validator::make(['cliente_id' => $clienteInput], [
                'cliente_id' => 'required|integer|exists:sqlsrv.Clientes,Codclie' 
            ]);
            if ($validator->fails()) {
                return redirect()->route('contador.flujo.cobranzas.paso1')->withErrors($validator)->withInput();
            }
            $clienteId = $clienteInput;
        }

        // 6. Guardamos el ID (ya sea el antiguo o el NUEVO) en la sesión
        session([
            'flujo_cobranza' => [
                'cliente_id' => $clienteId
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
        $vendedores = DB::connection($this->connection)->table('Empleados')->get(); 
        $seriesPlanilla = ['P001', 'P002', 'P003']; // Pendiente: Mover a tabla maestra

        return view('cobranzas.flujo.paso2_registrar_pago', compact('cliente', 'cuentasBancarias', 'pago', 'vendedores', 'seriesPlanilla'));
    }

    public function handlePaso2(Request $request)
    {
        $flujoData = session('flujo_cobranza');
        if (empty($flujoData['cliente_id'])) {
            return redirect()->route('contador.flujo.cobranzas.paso1')->with('error', 'Sesión expirada.');
        }

        $validator = Validator::make($request->all(), [
            'monto_pagado'   => 'required|numeric|min:0.01',
            'fecha_pago'     => 'required|date',
            'metodo_pago'    => 'required|string',
            'cuenta_destino' => 'required|string|exists:sqlsrv.Bancos,Cuenta',
            'referencia'     => 'nullable|string|max:50',
            'serie_planilla' => 'required|string|max:4',
            'vendedor_id'    => 'required|integer|exists:sqlsrv.Empleados,Codemp',
        ]);

        if ($validator->fails()) {
            return redirect()->route('contador.flujo.cobranzas.paso2')->withErrors($validator)->withInput();
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

        $facturasPendientes = DB::connection($this->connection)->table('CtaCliente')
            ->where('CodClie', $cliente->Codclie)
            ->where('Saldo', '>', 0)
            ->orderBy('FechaV', 'asc')
            ->get()
            ->map(function ($factura) {
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
        if (empty($flujoData['aplicaciones']) && !isset($flujoData['adelanto'])) { // Corregido: !isset
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
            
            $cliente = DB::connection($this->connection)->table('Clientes')
                            ->where('Codclie', $flujoData['cliente_id'])->first();
            $pago = $flujoData['pago'];
            $aplicaciones = $flujoData['aplicaciones'] ?? collect();
            $esAdelanto = $flujoData['adelanto'] ?? false;
            $montoTotalPagado = (float) $pago['monto_pagado'];
            $montoTotalAplicado = 0;
            $montoAdelanto = 0; // <-- Variable para el asiento
            $fechaPago = Carbon::parse($pago['fecha_pago']);
            $serie = $pago['serie_planilla'];
            
            // 1. Crear Cabecera de Planilla (PlanC_cobranza)
            $ultimoNum = DB::connection($this->connection)->table('PlanC_cobranza')
                            ->where('Serie', $serie)->max('Numero');
            $nextNum = str_pad((int)$ultimoNum + 1, 7, '0', STR_PAD_LEFT);
            $planillaNumeroCompleto = $serie . '-' . $nextNum; // <-- Lo usaremos para el asiento

            DB::connection($this->connection)->table('PlanC_cobranza')->insert([
                'Serie' => $serie,
                'Numero' => $nextNum,
                // ... (resto de campos)
                'Vendedor' => $pago['vendedor_id'],
                'FechaCrea' => now(),
                'FechaIng' => $fechaPago,
                'Confirmacion' => 1, 'Impreso' => 0,
            ]);

            // 2. Insertar movimiento en el Banco (CtaBanco)
            $clasePago = 3; // Transferencia
            if ($pago['metodo_pago'] == 'cheque') $clasePago = 1;
            if ($pago['metodo_pago'] == 'efectivo' || $pago['metodo_pago'] == 'deposito') $clasePago = 2;

            DB::connection($this->connection)->table('CtaBanco')->insert([
                'Tipo' => 1, 'Clase' => $clasePago,
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
                        'CodClie' => $cliente->Codclie,
                        'Documento' => $documento,
                        // ... (resto de campos)
                        'TipoDoc' => $tipo, 'FechaFac' => $factura->FechaF,
                        'Valor' => $factura->Importe, 'NroRecibo' => $planillaNumeroCompleto,
                        'Descuento' => 0,
                        'Efectivo' => ($pago['metodo_pago'] != 'cheque') ? $montoAplicado : 0,
                        'Cheque' => ($pago['metodo_pago'] == 'cheque') ? $montoAplicado : 0,
                        'NroCheque' => ($pago['metodo_pago'] == 'cheque') ? $pago['referencia'] : null,
                        'Moneda' => 1, 'Cambio' => 1,
                    ]);
                    
                    DB::connection($this->connection)->table('CtaCliente')
                        ->where('Documento', $documento)->where('Tipo', $tipo)
                        ->decrement('Saldo', $montoAplicado);
                    
                    $montoTotalAplicado += $montoAplicado;
                }
            }

            // 4. Registrar el adelanto (si existe)
            if ($esAdelanto) {
                $montoAdelanto = round($montoTotalPagado - $montoTotalAplicado, 2); // <-- Calculamos el adelanto
                if ($montoAdelanto > 0.01) {
                    $adelantoDocNum = 'ANT-' . $nextNum;
                    DB::connection($this->connection)->table('CtaCliente')->insert([
                        'Documento' => $adelantoDocNum,
                        'Tipo' => 99, // 99 = Tipo "Anticipo"
                        'CodClie' => $cliente->Codclie,
                        'FechaF' => $fechaPago, 'FechaV' => $fechaPago,
                        'Importe' => -$montoAdelanto,
                        'Saldo' => -$montoAdelanto,
                    ]);
                }
            }
            
            // ==========================================================
            // 5. 👨‍💼 LLAMADA AL MOTOR CONTABLE (COBRANZA) 👨‍💼
            // ==========================================================
            $this->contabilidadService->registrarAsientoCobranza(
                $planillaNumeroCompleto,
                $cliente,
                $pago,
                $montoTotalPagado,
                $montoTotalAplicado,
                $montoAdelanto,
                Auth::id() // ID del usuario logueado
            );

            DB::connection($this->connection)->commit();
            session()->forget('flujo_cobranza');
            
            return redirect()->route('dashboard.contador') // Ruta de tu dashboard
                            ->with('success', 'Cobranza registrada (Planilla: ' . $planillaNumeroCompleto . ') y Asiento Contable generado.');

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error('Error al procesar cobranza (sqlsrv): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->route('contador.flujo.cobranzas.paso4')
                            ->with('error', 'Error crítico al guardar: ' . $e->getMessage());
        }
    }
   
}