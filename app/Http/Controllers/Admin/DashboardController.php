<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
           
            'cobranzaPendiente' => $this->getCobranzaPendiente(),
            'saldoTotalClientes' => $this->getSaldoTotalClientes(),
            'cajaHoy' => $this->getCajaHoy(),
            'alertasCriticas' => $this->getAlertasCriticas(),
            'planillasPendientes' => $this->getPlanillasPendientes(),
            'variacionSaldo' => 2.5, // Puedes calcular esto si lo deseas

          
            'planillasRecientes' => DB::select('
                SELECT TOP 5 Serie, Numero, Vendedor, FechaCrea 
                FROM PlanC_cobranza 
                ORDER BY FechaCrea DESC
            '),
            'movimientosCaja' => DB::select('
                SELECT TOP 5 Documento, Tipo, Fecha, Monto 
                FROM Caja 
                WHERE Fecha >= CAST(GETDATE() AS DATE)
                ORDER BY Fecha DESC
            '),

         
            'graficoPlanillasVendedor' => $this->getGraficoPlanillasVendedor(),
            'graficoNotasCredito' => $this->getGraficoNotasCredito(),
        ];

        return view('admin.dashboard.index', compact('data'));
    }

    private function getCobranzaPendiente()
    {
        $result = DB::select("SELECT ISNULL(SUM(Valor), 0) as total FROM PlanD_cobranza");
        return $result[0]->total ?? 0;
    }

    private function getSaldoTotalClientes()
    {
        $result = DB::select("SELECT ISNULL(SUM(Importe), 0) as total FROM CtaCliente");
        return $result[0]->total ?? 0;
    }

    private function getCajaHoy()
    {
        $result = DB::select("SELECT ISNULL(SUM(Monto), 0) as total FROM Caja WHERE Fecha >= CAST(GETDATE() AS DATE)");
        return $result[0]->total ?? 0;
    }

    private function getAlertasCriticas()
    {
        
        $planillasSinConfirmar = DB::select("SELECT COUNT(*) as c FROM PlanC_cobranza WHERE Confirmacion = 0")[0]->c;
        $saldosNegativos = DB::select("SELECT COUNT(*) as c FROM CtaCliente WHERE Importe < 0")[0]->c;
        return $planillasSinConfirmar + $saldosNegativos;
    }

    private function getPlanillasPendientes()
    {
        $result = DB::select("SELECT COUNT(*) as c FROM PlanC_cobranza WHERE Confirmacion = 0");
        return $result[0]->c ?? 0;
    }

    private function getGraficoPlanillasVendedor()
    {
        $rows = DB::select("
            SELECT e.Nombre, COUNT(p.Vendedor) as total
            FROM PlanC_cobranza p
            JOIN Empleados e ON p.Vendedor = e.Codemp
            WHERE p.FechaCrea >= DATEADD(DAY, -30, GETDATE())
            GROUP BY e.Nombre
        ");
        return [
            'labels' => array_column($rows, 'Nombre'),
            'data' => array_column($rows, 'total')
        ];
    }

    private function getGraficoNotasCredito()
    {
        $rows = DB::select("
            SELECT 
                CASE 
                    WHEN Anulado = 1 THEN 'Anuladas'
                    ELSE 'Disponibles' 
                END as estado,
                COUNT(*) as total
            FROM notas_credito
            GROUP BY Anulado
        ");
        return [
            'labels' => array_column($rows, 'estado'),
            'data' => array_column($rows, 'total')
        ];
    }
}