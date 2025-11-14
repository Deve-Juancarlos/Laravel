<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BalanceGeneralService
{
    /**
     * ¡CORREGIDO!
     * Ya no inyectamos el EstadoResultadosService,
     * este servicio ahora es autónomo.
     */
    public function __construct()
    {
        // Constructor vacío
    }

    /**
     * Obtiene los datos para el Balance General.
     */
    public function getBalanceGeneralData(array $filters): array
    {
        $fecha = $filters['fecha'] ?? now()->toDateString();
        $inicioAnio = Carbon::parse($fecha)->startOfYear()->toDateString();

        // --- ACTIVOS ---
        $efectivo = $this->obtenerSaldoCuentaRango('10%', $fecha);
        $cuentasPorCobrar = $this->obtenerSaldoCuentaRango('12%', $fecha);
        $inventarios = $this->obtenerSaldoCuentaRango('20%', $fecha); // ¡CORREGIDO! (Era 13%)
        $gastosAdelantado = $this->obtenerSaldoCuentaRango('18%', $fecha);
        $totalActivosCorrientes = $efectivo + $cuentasPorCobrar + $inventarios + $gastosAdelantado;

        $propiedadPlanta = $this->obtenerSaldoCuentaRango('33%', $fecha);
        $depreciacion = $this->obtenerSaldoCuentaRango('39%', $fecha); // Saldo Acreedor (negativo)
        $intangibles = $this->obtenerSaldoCuentaRango('34%', $fecha);
        $otrosActivos = $this->obtenerSaldoCuentaRango('3[5-8]%', $fecha);
        $totalActivosNoCorrientes = $propiedadPlanta + $depreciacion + $intangibles + $otrosActivos; // Suma directa (39 ya es negativo)
        $totalActivos = $totalActivosCorrientes + $totalActivosNoCorrientes;

        // --- PASIVOS ---
        $cuentasPorPagar = $this->obtenerSaldoCuentaRango('42%', $fecha); // Proveedores
        $tributosPorPagar = $this->obtenerSaldoCuentaRango('40%', $fecha); // IGV, Renta
        $remuneracionesPorPagar = $this->obtenerSaldoCuentaRango('41%', $fecha);
        $otrasCtasPagar = $this->obtenerSaldoCuentaRango('4[4-6]%', $fecha); // 44, 45, 46
        
        // ¡CORREGIDO! Sumamos los saldos (que ya vienen negativos) y usamos abs() para mostrarlos
        $totalPasivosCorrientes = $cuentasPorPagar + $tributosPorPagar + $remuneracionesPorPagar + $otrasCtasPagar;

        $prestamosLargo = $this->obtenerSaldoCuentaRango('47%', $fecha);
        $provisionBeneficios = $this->obtenerSaldoCuentaRango('4[8-9]%', $fecha); // 48, 49
        $totalPasivosNoCorrientes = $prestamosLargo + $provisionBeneficios;
        
        $totalPasivos = $totalPasivosCorrientes + $totalPasivosNoCorrientes;

        // --- PATRIMONIO ---
        $capital = $this->obtenerSaldoCuentaRango('50%', $fecha);
        $reservas = $this->obtenerSaldoCuentaRango('58%', $fecha);
        $resultadosAcum = $this->obtenerSaldoCuentaRango('59%', $fecha);
        
        // ¡CORREGIDO! Usamos nuestro propio helper
        $resultadoEjercicio = $this->obtenerResultadoEjercicio($inicioAnio, $fecha); 
        
        $totalPatrimonio = $capital + $reservas + $resultadosAcum + $resultadoEjercicio; 
        
        // El Total Pasivo y Patrimonio debe ser negativo (Acreedor)
        $totalPasivosPatrimonio = $totalPasivos + $totalPatrimonio;

        // GOLAZO: Ecuación Contable: ACTIVO + PASIVO + PATRIMONIO = 0
        // (Activo es Deudor+, Pasivo y Patrimonio son Acreedor-)
        $diferenciaBalance = $totalActivos + $totalPasivosPatrimonio;
        $estaBalanceado = abs($diferenciaBalance) < 0.01;

        return [
            // Activos (Positivos)
            'efectivo' => $efectivo, 'cuentasPorCobrar' => $cuentasPorCobrar, 'inventarios' => $inventarios, 'gastosAdelantado' => $gastosAdelantado, 'totalActivosCorrientes' => $totalActivosCorrientes,
            'propiedadPlanta' => $propiedadPlanta, 'depreciacion' => $depreciacion, 'intangibles' => $intangibles, 'otrosActivos' => $otrosActivos, 'totalActivosNoCorrientes' => $totalActivosNoCorrientes, 'totalActivos' => $totalActivos,
            
            // Pasivos (Negativos, pero usamos abs() en la vista)
            'cuentasPorPagar' => $cuentasPorPagar, 'tributosPorPagar' => $tributosPorPagar, 'remuneracionesPorPagar' => $remuneracionesPorPagar, 'otrasCtasPagar' => $otrasCtasPagar, 'totalPasivosCorrientes' => $totalPasivosCorrientes,
            'prestamosLargo' => $prestamosLargo, 'provisionBeneficios' => $provisionBeneficios, 'totalPasivosNoCorrientes' => $totalPasivosNoCorrientes, 'totalPasivos' => $totalPasivos,
            
            // Patrimonio (Negativos, excepto Resultado Ejercicio)
            'capital' => $capital, 'reservas' => $reservas, 'resultadosAcum' => $resultadosAcum, 'resultadoEjercicio' => $resultadoEjercicio, 'totalPatrimonio' => $totalPatrimonio, 
            
            // Totales
            'totalPasivosPatrimonio' => $totalPasivosPatrimonio,
            'diferenciaBalance' => $diferenciaBalance, 'estaBalanceado' => $estaBalanceado, 'fecha' => $fecha
        ];
    }

    /**
     * Helper para obtener el saldo (DEBE - HABER) de un rango de cuentas.
     */
    private function obtenerSaldoCuentaRango($patron, $fecha)
    {
        $query = DB::table('libro_diario_detalles as d')
            ->join('libro_diario as c', 'd.asiento_id', '=', 'c.id')
            ->where('c.fecha', '<=', $fecha)
            ->where('c.estado', 'ACTIVO');

        if (is_array($patron)) {
             $query->where(function ($q) use ($patron) {
                 foreach ($patron as $p) {
                     $q->orWhere('d.cuenta_contable', 'LIKE', $p);
                 }
             });
        } else {
            $query->where('d.cuenta_contable', 'LIKE', $patron);
        }

        return $query->selectRaw('ISNULL(SUM(d.debe) - SUM(d.haber), 0) as saldo')
            ->value('saldo') ?? 0;
    }

    /**
     * ¡CORREGIDO!
     * Helper para obtener el Resultado del Ejercicio (Ingresos - Gastos)
     * (Ingresos son Acreedores (-), Gastos son Deudores (+))
     * Resultado = (INGRESOS) - (GASTOS)
     * Resultado = abs(Saldo Clase 7) - (Saldo Clase 6 + Saldo Clase 9)
     */
    private function obtenerResultadoEjercicio($inicioAnio, $fecha)
    {
        $ingresos = $this->obtenerSaldoCuentaRango('7%', $fecha);
        
        $gastos = $this->obtenerSaldoCuentaRango(['6%', '9%'], $fecha);
        return (-$ingresos) - $gastos;
    }
}