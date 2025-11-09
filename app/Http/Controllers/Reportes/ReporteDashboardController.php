<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;       // <--- ¡Necesario para el PDF adjunto!
use App\Mail\EnviarDocumentoMail;   // <--- ¡Reutilizamos tu Mailable!

class ReporteDashboardController extends Controller
{
    /**
     * Muestra el reporte principal de Antigüedad de Cuentas por Cobrar.
     * Esta es la nueva página "índice" de reportes.
     */
    public function agingCartera()
    {
        // 1. La Consulta (Pivote de la v_aging_cartera)
        // Agrupamos la deuda por cliente en "buckets" de antigüedad
        $reporte = DB::table('v_aging_cartera as v')
            ->join('Clientes as c', 'v.CodClie', '=', 'c.Codclie')
            ->select(
                'v.CodClie',
                'c.Razon',
                'c.Email as ClienteEmail', // <-- ¡Importante para el modal!
                DB::raw('SUM(v.Saldo) as DeudaTotal'),
                DB::raw("SUM(CASE WHEN v.rango = 'VIGENTE' THEN v.Saldo ELSE 0 END) as Vigente"),
                DB::raw("SUM(CASE WHEN v.rango = '1-30' THEN v.Saldo ELSE 0 END) as Rango1_30"),
                DB::raw("SUM(CASE WHEN v.rango = '31-60' THEN v.Saldo ELSE 0 END) as Rango31_60"),
                DB::raw("SUM(CASE WHEN v.rango = '61-90' THEN v.Saldo ELSE 0 END) as Rango61_90"),
                DB::raw("SUM(CASE WHEN v.rango = '90+' THEN v.Saldo ELSE 0 END) as Rango90Mas")
            )
            ->groupBy('v.CodClie', 'c.Razon', 'c.Email')
            ->having(DB::raw('SUM(v.Saldo)'), '>', 0)
            ->orderBy('DeudaTotal', 'desc')
            ->get();
        
        // 2. Calcular los totales generales para la cabecera
        $totales = [
            'Total' => $reporte->sum('DeudaTotal'),
            'Vigente' => $reporte->sum('Vigente'),
            'Rango1_30' => $reporte->sum('Rango1_30'),
            'Rango31_60' => $reporte->sum('Rango31_60'),
            'Rango61_90' => $reporte->sum('Rango61_90'),
            'Rango90Mas' => $reporte->sum('Rango90Mas'),
        ];

        return view('reportes.index', [
            'reporte' => $reporte,
            'totales' => $totales
        ]);
    }

    /**
     * Envía un email de recordatorio de cobranza al cliente.
     */
    public function enviarRecordatorioEmail(Request $request, $clienteId)
    {
        // 1. Validar los datos del formulario del modal
        $request->validate([
            'email_destino' => 'required|email',
            'email_asunto'  => 'required|string|max:255',
            'email_cuerpo'  => 'required|string',
        ]);

        $emailDestino = $request->input('email_destino');

        try {
            // 2. Obtener los datos del cliente y su deuda DETALLADA
            $cliente = DB::table('Clientes')->where('Codclie', $clienteId)->first();
            
            $deudaDetallada = DB::table('v_aging_cartera')
                ->select('Documento', 'FechaF', 'FechaV', 'Importe', 'Saldo', 'dias_vencidos')
                ->where('CodClie', $clienteId)
                ->where('Saldo', '>', 0)
                ->orderBy('FechaV', 'asc')
                ->get();
            
            if ($deudaDetallada->isEmpty()) {
                return back()->with('error', 'Este cliente no presenta deuda.');
            }

            // 3. Preparar los datos para la vista del PDF
            $dataParaPdf = [
                'cliente' => $cliente,
                'deudaDetallada' => $deudaDetallada,
                'totalDeuda' => $deudaDetallada->sum('Saldo'),
                'empresa' => ['nombre' => 'SEDIMCORP SAC'] // <-- Poner tu nombre de empresa
            ];

            // 4. Generar el PDF adjunto (Estado de Cuenta)
            $pdf = Pdf::loadView('reportes.pdf_recordatorio_deuda', $dataParaPdf);
            
            // 5. Preparar los datos para el Mailable (reutilizando el tuyo)
            $emailData = [
                'asunto' => $request->input('email_asunto'),
                'titulo' => "Estimado(a) {$cliente->Razon},",
                'cuerpo' => $request->input('email_cuerpo'),
                'pdf' => $pdf->output(),
                'nombreArchivo' => "Estado_de_Cuenta_{$cliente->Codclie}.pdf"
            ];

            // 6. Enviar el correo
            Mail::to($emailDestino)->send(new EnviarDocumentoMail($emailData));

            return redirect()->back()->with('success', "Recordatorio enviado a {$emailDestino} exitosamente.");

        } catch (\Exception $e) {
            Log::error("Error al enviar recordatorio de cobranza: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al enviar el email: ' . $e->getMessage());
        }
    }
}