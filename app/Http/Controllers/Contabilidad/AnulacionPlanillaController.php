<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ContabilidadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnulacionPlanillaController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $contabilidadService;

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->middleware('auth');
        $this->contabilidadService = $contabilidadService;
    }

    /**
     * Muestra la vista para confirmar la anulación (¡Importante!)
     */
    public function show($serie, $numero)
    {
        $planilla = DB::connection($this->connection)->table('PlanC_cobranza as pc')
            ->join('Empleados as e', 'pc.Vendedor', '=', 'e.Codemp')
            ->where('pc.Serie', $serie)->where('pc.Numero', $numero)
            ->select('pc.*', 'e.Nombre as VendedorNombre')
            ->first();

        if (!$planilla) {
            abort(404, 'Planilla no encontrada');
        }

        if ($planilla->Confirmacion == 0) { // O si tienes un campo 'Estado' == 'ANULADO'
             return redirect()->route('contador.planillas.index') // Cambia a tu ruta
                ->with('error', 'Esta planilla ya ha sido anulada.');
        }

        $detalles = DB::connection($this->connection)->table('PlanD_cobranza')
            ->where('Serie', $serie)->where('Numero', $numero)
            ->get();
        
        $total = $detalles->sum('Efectivo') + $detalles->sum('Cheque');

        // Debes crear esta vista
        return view('cobranzas.anular', [
            'planilla' => $planilla,
            'detalles' => $detalles,
            'total' => $total
        ]);
    }

    /**
     * Procesa la anulación.
     * Esta es la función que reemplaza a tu Stored Procedure.
     */
    public function store(Request $request)
    {
        $request->validate([
            'serie' => 'required',
            'numero' => 'required',
            'motivo' => 'required|string|min:10|max:255',
        ]);

        $serie = $request->input('serie');
        $numero = $request->input('numero');
        $motivo = $request->input('motivo');
        $usuario = Auth::user();

        DB::connection($this->connection)->beginTransaction();
        try {
            
            // 1. Bloquear la planilla original
            $planilla = DB::connection($this->connection)->table('PlanC_cobranza')
                ->where('Serie', $serie)->where('Numero', $numero)
                ->lockForUpdate()->first();

            if (!$planilla) throw new \Exception('Planilla no encontrada.');
            if ($planilla->Confirmacion == 0) throw new \Exception('Esta planilla ya fue anulada.'); // O tu campo de estado

            // 2. Obtener los detalles
            $detalles = DB::connection($this->connection)->table('PlanD_cobranza')
                ->where('Serie', $serie)->where('Numero', $numero)
                ->get();
            
            if ($detalles->isEmpty()) throw new \Exception('La planilla no tiene detalles para revertir.');

            $totalAnulado = 0;

            // 3. Revertir saldos de CtaCliente (¡Adiós Cursor!)
            foreach ($detalles as $detalle) {
                $montoRevertir = (float)$detalle->Efectivo + (float)$detalle->Cheque;
                $totalAnulado += $montoRevertir;

                DB::connection($this->connection)->table('CtaCliente')
                    ->where('Documento', $detalle->Documento)
                    ->where('Tipo', $detalle->TipoDoc)
                    ->increment('Saldo', $montoRevertir); // <-- Revierte el saldo (Incrementa la deuda)
                
                // (Opcional) Revertir Nota de Crédito si se usó
                if ($detalle->NotaCred && $detalle->Descuento > 0) {
                     DB::connection($this->connection)->table('CtaCliente')
                        ->where('Documento', $detalle->NotaCred)
                        ->where('Tipo', 8) // Asumo 8 = NC
                        ->decrement('Saldo', (float)$detalle->Descuento); // Devuelve el saldo a la NC
                }
            }

            if ($totalAnulado <= 0) throw new \Exception('El monto a anular es cero.');

            // 4. ¡¡REVERTIR EL BANCO!! (El paso que faltaba en tu SP)
            $planillaNumeroCompleto = $serie . '-' . $numero;
            
            // Buscamos el ingreso original en el banco
            $bancoIngreso = DB::connection($this->connection)->table('CtaBanco')
                ->where('Documento', $planillaNumeroCompleto)
                ->where('Tipo', 1) // Tipo 1 = Ingreso
                ->first();

            if (!$bancoIngreso) {
                throw new \Exception("Error de integridad: No se encontró el ingreso en CtaBanco para la planilla {$planillaNumeroCompleto}.");
            }

            // Creamos el Egreso (Tipo 2) de anulación
            DB::connection($this->connection)->table('CtaBanco')->insert([
                'Tipo' => 2, // 2 = Egreso
                'Clase' => 99, // 99 = Anulación (o el código que definas)
                'Cuenta' => $bancoIngreso->Cuenta,
                'Documento' => 'ANUL-' . $planillaNumeroCompleto,
                'Monto' => $totalAnulado,
                'Fecha' => now(),
            ]);

            // 5. 👨‍💼 LLAMAR AL MOTOR CONTABLE (Anulación) 👨‍💼
            $this->contabilidadService->registrarAsientoAnulacionCobranza(
                $planilla,
                $bancoIngreso,
                $totalAnulado,
                $motivo,
                $usuario->id
            );

            // 6. Marcar la planilla original como Anulada (NO BORRAR)
            DB::connection($this->connection)->table('PlanC_cobranza')
                ->where('Serie', $serie)->where('Numero', $numero)
                ->update([
                    'Confirmacion' => 0, // O 'Estado' = 'ANULADO'
                    // Opcional: Guardar el motivo de la anulación
                    // 'MotivoAnulacion' => $motivo 
                ]);

            DB::connection($this->connection)->commit();

            return redirect()->route('contador.dashboard.contador') // O a la lista de planillas
                ->with('success', "Planilla {$planillaNumeroCompleto} anulada exitosamente. Se revirtieron saldos y se generó el asiento de extorno.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al anular planilla: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error crítico al anular: ' . $e->getMessage());
        }
    }
}