<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\Contabilidad\EstadoResultadosService; // Importamos el servicio de EERR

class BalanceGeneralService
{
    protected $estadoResultadosService;

    // Inyectamos el servicio de EERR para obtener el resultado del ejercicio
    public function __construct(EstadoResultadosService $estadoResultadosService)
    {
        $this->estadoResultadosService = $estadoResultadosService;
    }

    /**
     * Obtiene los datos para el Balance General.
     */
    public function getBalanceGeneralData(array $filters): array
    {
        $fecha = $filters['fecha'] ?? now()->toDateString();

        // ACTIVOS CORRIENTES
        $efectivo = $this->obtenerSaldoCuentaRango('10%', $fecha);
        $cuentasPorCobrar = $this->obtenerSaldoCuentaRango('12%', $fecha);
        $inventarios = $this->obtenerSaldoCuentaRango('13%', $fecha); // Usando 13% en lugar de 20%
        $gastosAdelantado = $this->obtenerSaldoCuentaRango('18%', $fecha); // Usando 18%
        $totalActivosCorrientes = $efectivo + $cuentasPorCobrar + $inventarios + $gastosAdelantado;

        // ACTIVOS NO CORRIENTES
        $propiedadPlanta = $this->obtenerSaldoCuentaRango('33%', $fecha); // Usando 33%
        $depreciacion = $this->obtenerSaldoCuentaRango('39%', $fecha); // Usando 39%
        $intangibles = $this->obtenerSaldoCuentaRango('34%', $fecha); // Usando 34%
        $otrosActivos = $this->obtenerSaldoCuentaRango('3[5-8]%', $fecha); // Resto de la 3
        $totalActivosNoCorrientes = $propiedadPlanta - abs($depreciacion) + $intangibles + $otrosActivos; // Depreciación resta
        $totalActivos = $totalActivosCorrientes + $totalActivosNoCorrientes;

        // PASIVOS CORRIENTES
        $cuentasPorPagar = $this->obtenerSaldoCuentaRango('42%', $fecha); // 42
        $documentosPorPagar = $this->obtenerSaldoCuentaRango('40%', $fecha); // 40
        $prestamosCorto = $this->obtenerSaldoCuentaRango('45%', $fecha); // 45
        $provisionImpuestos = $this->obtenerSaldoCuentaRango('40[17]%', $fecha); // 4017 (IGV)
        $otrosGastosPagar = $this->obtenerSaldoCuentaRango('4[1,4,6]%', $fecha); // 41, 44, 46
        $totalPasivosCorrientes = abs($cuentasPorPagar) + abs($documentosPorPagar) + abs($prestamosCorto) + abs($provisionImpuestos) + abs($otrosGastosPagar);

        // PASIVOS NO CORRIENTES
        $prestamosLargo = $this->obtenerSaldoCuentaRango('47%', $fecha); // 47
        $provisionBeneficios = $this->obtenerSaldoCuentaRango('4[8-9]%', $fecha); // 48, 49
        $totalPasivosNoCorrientes = abs($prestamosLargo) + abs($provisionBeneficios);
        $totalPasivos = $totalPasivosCorrientes + $totalPasivosNoCorrientes;

        // PATRIMONIO
        $capital = $this->obtenerSaldoCuentaRango('50%', $fecha);
        $reservas = $this->obtenerSaldoCuentaRango('58%', $fecha);
        $resultadosAcum = $this->obtenerSaldoCuentaRango('59%', $fecha);
        // El resultado del ejercicio lo calculamos con el servicio de EERR
        $resultadoEjercicio = $this->obtenerResultadoEjercicio($fecha); 
        
        $totalPatrimonio = abs($capital) + abs($reservas) + abs($resultadosAcum) + $resultadoEjercicio; // Resultado Ejercicio suma o resta
        $totalPasivosPatrimonio = $totalPasivos + $totalPatrimonio;

        $diferenciaBalance = $totalActivos - $totalPasivosPatrimonio;
        $estaBalanceado = abs($diferenciaBalance) < 0.01;

        // Devolvemos un array asociativo que el controlador pasará a la vista
        return [
            'efectivo' => $efectivo, 'cuentasPorCobrar' => $cuentasPorCobrar, 'inventarios' => $inventarios, 'gastosAdelantado' => $gastosAdelantado, 'totalActivosCorrientes' => $totalActivosCorrientes,
            'propiedadPlanta' => $propiedadPlanta, 'depreciacion' => $depreciacion, 'intangibles' => $intangibles, 'otrosActivos' => $otrosActivos, 'totalActivosNoCorrientes' => $totalActivosNoCorrientes, 'totalActivos' => $totalActivos,
            'cuentasPorPagar' => $cuentasPorPagar, 'documentosPorPagar' => $documentosPorPagar, 'prestamosCorto' => $prestamosCorto, 'provisionImpuestos' => $provisionImpuestos, 'otrosGastosPagar' => $otrosGastosPagar, 'totalPasivosCorrientes' => $totalPasivosCorrientes,
            'prestamosLargo' => $prestamosLargo, 'provisionBeneficios' => $provisionBeneficios, 'totalPasivosNoCorrientes' => $totalPasivosNoCorrientes, 'totalPasivos' => $totalPasivos,
            'capital' => $capital, 'reservas' => $reservas, 'resultadosAcum' => $resultadosAcum, 'resultadoEjercicio' => $resultadoEjercicio, 'totalPatrimonio' => $totalPatrimonio, 'totalPasivosPatrimonio' => $totalPasivosPatrimonio,
            'diferenciaBalance' => $diferenciaBalance, 'estaBalanceado' => $estaBalanceado, 'fecha' => $fecha
        ];
    }

    /**
     * Helper para obtener el saldo de un rango de cuentas a una fecha
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
     * Helper para obtener el Resultado del Ejercicio (Ingresos - Gastos)
     */
    private function obtenerResultadoEjercicio($fecha)
    {
        $anio = Carbon::parse($fecha)->year;
        $inicioAnio = Carbon::create($anio, 1, 1)->toDateString();

        // Usamos el servicio de EERR para obtener los totales
        $ingresos = $this->estadoResultadosService
                         ->getCuentasPorClase($inicioAnio, $fecha, '7%', 'haber')
                         ->sum('total');
        
        $gastos = $this->estadoResultadosService
                       ->getCuentasPorClase($inicioAnio, $fecha, ['6%', '9%'], 'debe')
                       ->sum('total');

        return ($ingresos ?? 0) - ($gastos ?? 0);
    }
}
