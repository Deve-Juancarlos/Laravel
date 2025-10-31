<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FlujoCajaService
{
    /**
     * Obtiene todos los datos para la vista principal del Flujo de Caja (Método Indirecto).
     */
    public function getFlujoIndexData(string $fechaInicio, string $fechaFin)
    {
        // 1. Actividades Operativas
        $actividadesOperativas = $this->obtenerActividadesOperativas($fechaInicio, $fechaFin);

        // 2. Actividades de Inversión
        $actividadesInversion = $this->obtenerActividadesInversion($fechaInicio, $fechaFin);

        // 3. Actividades de Financiamiento
        $actividadesFinanciamiento = $this->obtenerActividadesFinanciamiento($fechaInicio, $fechaFin);

        // Calcular totales
        $totalOperativas = $actividadesOperativas['neto'] ?? 0;
        $totalInversion = $actividadesInversion['neto'] ?? 0;
        $totalFinanciamiento = $actividadesFinanciamiento['neto'] ?? 0;

        $flujoNeto = $totalOperativas + $totalInversion + $totalFinanciamiento;

        // *** CORRECCIÓN ***: Saldo inicial debe incluir Caja + Bancos
        $saldoInicial = $this->obtenerSaldoInicial($fechaInicio);
        $saldoFinal = $saldoInicial + $flujoNeto;

        return [
            'actividadesOperativas' => $actividadesOperativas,
            'actividadesInversion' => $actividadesInversion,
            'actividadesFinanciamiento' => $actividadesFinanciamiento,
            'totalOperativas' => $totalOperativas,
            'totalInversion' => $totalInversion,
            'totalFinanciamiento' => $totalFinanciamiento,
            'flujoNeto' => $flujoNeto,
            'saldoInicial' => $saldoInicial,
            'saldoFinal' => $saldoFinal,
            'proyecciones' => $this->obtenerProyecciones(Carbon::now()->format('Y-m-d'), 30) // Proyecciones a 30 días
        ];
    }

    /**
     * Obtiene los datos para el reporte de Flujo Diario.
     */
    public function getFlujoDiarioData(string $fecha)
    {
        // *** CORRECCIÓN ***: No sumar facturación al flujo de caja.
        // Flujo de caja son COBROS, no ventas devengadas.
        
        // (Dato Informativo) Ventas facturadas en el día
        $ventasFacturadas = DB::table('Doccab')
            ->whereDate('Fecha', $fecha)
            ->where('Tipo', 1) // Facturas
            ->where('Eliminado', 0)
            ->get();

        // (Flujo Real) Cobranzas del día (Ingresos a Caja)
        $cobranzas = DB::table('Caja')
            ->whereDate('Fecha', $fecha)
            ->where('Tipo', 1) // Ingresos / cobros
            ->get();
            
        // (Flujo Real) Egresos del día
        $egresosData = $this->obtenerEgresosDiarios($fecha);
        $egresos = $egresosData['egresos_caja']; // o un consolidado si tienes más fuentes

        // Resumen
        $ventasSum = (float) $ventasFacturadas->sum('Total');
        $cobranzasSum = (float) $cobranzas->sum('Monto');
        $totalEgresos = (float) $egresosData['total'];

        $resumenDiario = [
            'fecha' => $fecha,
            'ventas_facturadas_info' => $ventasSum, // Informativo
            'cobranzas' => $cobranzasSum,
            'total_ingresos' => $cobranzasSum, // El ingreso real de efectivo
            'total_egresos' => $totalEgresos,
            'flujo_neto' => $cobranzasSum - $totalEgresos
        ];

        return [
            'ventasFacturadas' => $ventasFacturadas,
            'cobranzas' => $cobranzas,
            'egresos' => $egresos,
            'resumenDiario' => $resumenDiario
        ];
    }

    /**
     * Obtiene datos para el reporte de Farmacia
     */
    public function getFlujoFarmaciaData(string $fechaInicio, string $fechaFin)
    {
        $flujoFarmaceutico = [
            'ingresos' => $this->obtenerIngresosFarmaceuticos($fechaInicio, $fechaFin),
            'egresos' => $this->obtenerEgresosFarmaceuticos($fechaInicio, $fechaFin),
            'mermas' => $this->obtenerCostoMermas($fechaInicio, $fechaFin)
        ];
        
        $rotacionInventario = $this->calcularRotacionInventario($fechaInicio, $fechaFin);
        
        // *** CORRECCIÓN ***: Usar fechaFin para el cálculo de días de inventario
        $diasInventario = $this->calcularDiasInventario($fechaFin);

        return [
            'flujoFarmaceutico' => $flujoFarmaceutico,
            'rotacionInventario' => $rotacionInventario,
            'diasInventario' => $diasInventario
        ];
    }


    // --- MÉTODOS PRIVADOS (LÓGICA INTERNA) ---
    // (Aquí van todas las funciones 'private' de tu controlador original)
    // ...

    /**
     * *** CORRECCIÓN ***
     * Saldo inicial de Efectivo y Equivalentes (Caja + Bancos)
     */
    private function obtenerSaldoInicial($fechaInicio)
    {
        $saldoCaja = DB::table('Caja')
            ->whereDate('Fecha', '<', $fechaInicio)
            ->selectRaw('ISNULL(SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END), 0) as saldo')
            ->value('saldo');

        $saldoBancos = DB::table('CtaBanco')
            ->whereDate('Fecha', '<', $fechaInicio)
            ->selectRaw('ISNULL(SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END), 0) as saldo')
            ->value('saldo');            

        return (float) ($saldoCaja ?? 0) + (float) ($saldoBancos ?? 0);
    }
    
    /**
     * *** CORRECCIÓN ***
     * Días de inventario disponible (Valorizado)
     */
    private function calcularDiasInventario($fechaFin)
    {
        $fechaFinDt = Carbon::parse($fechaFin);
        $desde = $fechaFinDt->copy()->subDays(30)->format('Y-m-d');
        $hasta = $fechaFinDt->format('Y-m-d');

        // 1. Costo de ventas de los últimos 30 días
        $sumaCosto = (float) DB::table('Docdet as dd')
            ->join('Doccab as dc', function($join) {
                $join->on('dd.Numero', '=', 'dc.Numero')
                     ->on('dd.Tipo', '=', 'dc.Tipo');
            })
            ->whereBetween('dc.Fecha', [$desde, $hasta])
            ->where('dc.Tipo', 1)
            ->where('dc.Eliminado', 0)
            ->selectRaw('ISNULL(SUM(CAST(dd.Costo AS MONEY) * dd.Cantidad), 0) as suma')
            ->value('suma');
            
        $costoDiarioPromedio = ($sumaCosto > 0) ? $sumaCosto / 30 : 0;

        // 2. Inventario valorizado actual
        $inventarioValorizado = (float) DB::table('Saldos as s')
            ->join('Productos as p', 's.codpro', '=', 'p.CodPro')
            ->where('s.saldo', '>', 0)
            ->where('p.Eliminado', 0)
            ->selectRaw('ISNULL(SUM(CAST(s.saldo AS MONEY) * p.Costo), 0) as valor_total')
            ->value('valor_total');

        return $costoDiarioPromedio > 0 ? ($inventarioValorizado / $costoDiarioPromedio) : 0;
    }

    

    // --- (Resto de métodos privados sin cambios... los copias aquí) ---
    // ...
    private function obtenerActividadesOperativas($fechaInicio, $fechaFin) { /* ... */ return ['neto' => 0]; }
    private function obtenerActividadesInversion($fechaInicio, $fechaFin) { /* ... */ return ['neto' => 0]; }
    private function obtenerActividadesFinanciamiento($fechaInicio, $fechaFin) { /* ... */ return ['neto' => 0]; }
    private function obtenerProyecciones($fechaFin, $dias) { /* ... */ return []; }
    private function obtenerEgresosDiarios($fecha) { /* ... */ return ['total' => 0, 'egresos_caja' => collect()]; }
    private function obtenerIngresosFarmaceuticos($fechaInicio, $fechaFin) { /* ... */ return collect(); }
    private function obtenerEgresosFarmaceuticos($fechaInicio, $fechaFin) { /* ... */ return []; }
    private function obtenerCostoMermas($fechaInicio, $fechaFin) { /* ... */ return 0; }
    private function calcularRotacionInventario($fechaInicio, $fechaFin) { /* ... */ return 0; }
    // ... etc
}