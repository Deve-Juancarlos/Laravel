<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\BancoService;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    protected $bancoService;

    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    /**
     * Dashboard principal de bancos y cuentas
     * Vista de saldos consolidados
     */
    public function index()
    {
        $cuentas = $this->bancoService->obtenerCuentasConSaldos();
        $resumen = $this->bancoService->obtenerResumenGeneral();
        
        return view('admin.bancos.index', compact('cuentas', 'resumen'));
    }

    /**
     * Ver movimientos de una cuenta específica
     */
    public function movimientos(Request $request, $cuenta)
{
    $filtros = [
        'fecha_inicio' => $request->get('fecha_inicio'),
        'fecha_fin' => $request->get('fecha_fin'),
        'tipo' => $request->get('tipo'), // 1=Ingreso, 2=Egreso
        'clase' => $request->get('clase'),
    ];

    $cuentaData = $this->bancoService->obtenerCuenta($cuenta);

    // Obtener movimientos con alias "Estado" seguro
    $movimientos = $this->bancoService->obtenerMovimientos($cuenta, $filtros)
        ->map(function($mov) {
            // Si la columna real se llama diferente, cámbiala aquí
            if(!isset($mov->Estado)) {
                $mov->Estado = $mov->Confirmado ?? 'PENDIENTE';
            }
            return $mov;
        });

    $estadisticas = $this->bancoService->obtenerEstadisticasCuenta($cuenta, $filtros);

    return view('admin.bancos.movimientos', compact('cuentaData', 'movimientos', 'estadisticas', 'filtros'));
}


    /**
     * Dashboard de análisis de flujo de caja
     */
    public function estadisticas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');
        
        $estadisticas = $this->bancoService->obtenerEstadisticasDetalladas($periodo);
        $flujoCaja = $this->bancoService->obtenerFlujoCaja($periodo);
        
        return view('admin.bancos.estadisticas', compact('estadisticas', 'flujoCaja', 'periodo'));
    }

    /**
     * Vista de saldos consolidados
     */
    public function saldos()
    {
        $saldosPorBanco = $this->bancoService->obtenerSaldosPorBanco();
        $saldosPorMoneda = $this->bancoService->obtenerSaldosPorMoneda();
        $liquidez = $this->bancoService->obtenerLiquidez();
        
        return view('admin.bancos.saldos', compact('saldosPorBanco', 'saldosPorMoneda', 'liquidez'));
    }

    /**
     * Proceso de conciliación bancaria
     */
    public function conciliacion(Request $request, $cuenta)
{
    $fecha = $request->get('fecha', now()->toDateString());

    $cuentaData = $this->bancoService->obtenerCuenta($cuenta);
    
    // Mapear id para evitar errores en la vista
    $movimientosSinConciliar = $this->bancoService->obtenerMovimientosSinConciliar($cuenta, $fecha)
        ->map(function($mov) {
            $mov->id = $mov->Numero; // aquí aseguras que siempre exista 'id'
            return $mov;
        });

    $historialConciliaciones = $this->bancoService->obtenerHistorialConciliaciones($cuenta);

    return view('admin.bancos.conciliacion', compact(
        'cuentaData',
        'movimientosSinConciliar',
        'historialConciliaciones',
        'fecha'
    ));
}


    /**
     * Guardar conciliación
     */
    public function guardarConciliacion(Request $request, $cuenta)
    {
        $request->validate([
            'fecha' => 'required|date',
            'saldo_bancario' => 'required|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $resultado = $this->bancoService->registrarConciliacion(
            $cuenta,
            $request->fecha,
            $request->saldo_bancario,
            $request->observaciones
        );

        if ($resultado['success']) {
            return redirect()->route('admin.bancos.conciliacion', $cuenta)
                ->with('success', 'Conciliación registrada correctamente.');
        }

        return back()->with('error', 'Error al registrar la conciliación.');
    }

    /**
     * Exportar movimientos a Excel
     */
    public function exportar(Request $request, $cuenta)
    {
        $filtros = [
            'fecha_inicio' => $request->get('fecha_inicio'),
            'fecha_fin' => $request->get('fecha_fin'),
        ];

        return $this->bancoService->exportarMovimientos($cuenta, $filtros);
    }

    /**
     * Ver cheques pendientes
     */
    public function chequesPendientes()
    {
        $cheques = $this->bancoService->obtenerChequesPendientes();
        
        return view('admin.bancos.cheques-pendientes', compact('cheques'));
    }

    /**
     * Ver transferencias bancarias
     */
    public function transferencias(Request $request)
    {
        $filtros = [
            'fecha_inicio' => $request->get('fecha_inicio'),
            'fecha_fin' => $request->get('fecha_fin'),
            'estado' => $request->get('estado'),
        ];

        $transferencias = $this->bancoService->obtenerTransferencias($filtros);
        
        return view('admin.bancos.transferencias', compact('transferencias', 'filtros'));
    }
}
