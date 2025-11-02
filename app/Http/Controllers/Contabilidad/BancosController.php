<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\BancoService; // 1. Importamos el Servicio
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BancosController extends Controller
{
    protected $bancoService;

    // 2. Inyectamos el servicio
    public function __construct(BancoService $bancoService)
    {
        $this->bancoService = $bancoService;
    }

    /**
     * Muestra el dashboard principal de Bancos.
     * Ruta: contador.bancos.index
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');

            $data = $this->bancoService->getDashboardData($fechaInicio, $fechaFin, $cuenta);
            $listaBancos = DB::table('Bancos')->orderBy('Banco')->get();

            return view('contabilidad.bancos.index', array_merge($data, [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'cuentaSeleccionada' => $cuenta,
                'listaBancos' => $listaBancos,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el dashboard de bancos: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle de una cuenta bancaria específica.
     * Ruta: contador.bancos.detalle
     */
    public function detalle(Request $request, $cuenta)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            $data = $this->bancoService->getAccountDetail($cuenta, $fechaInicio, $fechaFin);

            if (!$data) {
                return redirect()->route('contador.bancos.index')->with('error', 'La cuenta bancaria no fue encontrada.');
            }

            return view('contabilidad.bancos.detalle', array_merge($data, [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@detalle: ' . $e->getMessage());
            return redirect()->route('contador.bancos.index')->with('error', 'Error al cargar el detalle de la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista de Conciliación Bancaria.
     * Ruta: contador.bancos.conciliacion
     */
    public function conciliacion(Request $request)
    {
        try {
            $cuenta = $request->input('cuenta');
            $fecha = $request->input('fecha_corte', Carbon::now()->format('Y-m-d'));
            
            $data = null;
            if ($cuenta) {
                $data = $this->bancoService->getReconciliationData($cuenta, $fecha);
            }
            
            $listaBancos = DB::table('Bancos')->orderBy('Banco')->get();

            return view('contabilidad.bancos.conciliacion', [
                'data' => $data,
                'listaBancos' => $listaBancos,
                'cuentaSeleccionada' => $cuenta,
                'fechaCorte' => $fecha,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController@conciliacion: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la conciliación: ' . $e->getMessage());
        }
    }

    /**
     * Guarda la conciliación bancaria.
     * Ruta: contador.bancos.conciliacion.store
     */
    public function storeConciliacion(Request $request)
    {
        $validatedData = $request->validate([
            'cuenta' => 'required|string',
            'fecha_conciliacion' => 'required|date',
            'saldo_bancario' => 'required|numeric',
            'observaciones' => 'nullable|string',
        ]);

        try {
            // Esta función llamará al SP que acabamos de crear.
            $this->bancoService->saveReconciliation($validatedData);
            
            return redirect()->back()->with('success', 'Conciliación guardada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error en BancosController@storeConciliacion: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar la conciliación: ' . $e->getMessage());
        }
    }


    /**
     * Muestra el Flujo de Caja Diario.
     * Ruta: contador.bancos.flujo-diario
     */
    public function flujoDiario(Request $request)
    {
         try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));
            $bancoId = $request->input('banco_id'); // Filtro opcional

            $data = $this->bancoService->getDailyCashFlow($fecha, $bancoId);
            $listaBancos = DB::table('Bancos')->orderBy('Banco')->get();

            return view('contabilidad.bancos.flujo-diario', array_merge($data, [
                'fecha' => $fecha,
                'listaBancos' => $listaBancos,
                'bancoSeleccionado' => $bancoId
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@flujoDiario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el flujo diario: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el reporte diario (movimientos por día).
     * Ruta: contador.bancos.diario
     */
    public function diario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));
            $data = $this->bancoService->getDailyMovements($fecha);

            return view('contabilidad.bancos.diario', array_merge($data, [
                'fecha' => $fecha,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@diario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el reporte diario: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el resumen mensual.
     * Ruta: contador.bancos.mensual
     */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);
            $mes = $request->input('mes', Carbon::now()->month);
            
            $data = $this->bancoService->getMonthlySummary($anio, $mes);

            // CORRECCIÓN (Error 2): Pasamos las variables que faltaban
            return view('contabilidad.bancos.mensual', array_merge($data, [
                'anioSeleccionado' => $anio,
                'mesSeleccionado' => $mes,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@resumenMensual: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el resumen mensual: ' . $e->getMessage());
        }
    }
    
    /**
     * Muestra la lista de transferencias.
     * Ruta: contador.bancos.transferencias
     */
    public function transferencias(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            $data = $this->bancoService->getTransfersData($fechaInicio, $fechaFin);

            return view('contabilidad.bancos.transferencias', array_merge($data, [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@transferencias: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar transferencias: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la página para generar reportes generales.
     * Ruta: contador.bancos.reporte
     */
    public function generarReporte(Request $request)
    {
        try {
            $tipoReporte = $request->input('tipo_reporte', 'general');
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $cuenta = $request->input('cuenta');
            
            $listaBancos = DB::table('Bancos')->orderBy('Banco')->get();
            
            $data = $this->bancoService->getReportData($tipoReporte, $fechaInicio, $fechaFin, $cuenta, $listaBancos);

            // CORRECCIÓN (Error 4): Pasamos la variable que faltaba
            return view('contabilidad.bancos.reporte', array_merge($data, [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'cuentaSeleccionada' => $cuenta,
                'tipoReporte' => $tipoReporte,
                'listaBancos' => $listaBancos,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en BancosController@generarReporte: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }
}

