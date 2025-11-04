<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SunatService;

class ProcesarDocumentoSunatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $numeroDoc;
    protected $tipoDoc;

    /**
     * Create a new job instance.
     */
    public function __construct(string $numeroDoc, int $tipoDoc)
    {
        $this->numeroDoc = $numeroDoc;
        $this->tipoDoc = $tipoDoc;
    }

    /**
     * Execute the job.
     */
    public function handle(SunatService $sunatService): void
    {
        try {
            // 1. Llamar al servicio que creamos
            $respuesta = $sunatService->enviarDocumentoVenta($this->numeroDoc, $this->tipoDoc);

            // 2. Actualizar Doccab con la respuesta (ÉXITO O RECHAZO)
            DB::connection('sqlsrv')->table('Doccab')
                ->where('Numero', $this->numeroDoc)
                ->where('Tipo', $this->tipoDoc)
                ->update([
                    'estado_sunat' => $respuesta['estado_sunat'],
                    'hash_cdr' => $respuesta['hash_cdr'],
                    'mensaje_sunat' => $respuesta['mensaje_sunat'],
                    'nombre_archivo' => $respuesta['nombre_archivo'],
                    'qr_data' => $respuesta['qr_data'],
                ]);
            
            Log::info("SUNAT Job: Documento {$this->numeroDoc} actualizado a {$respuesta['estado_sunat']}.");

        } catch (\Exception $e) {
            // 3. Si el envío falla (ej. el PSE se cayó)
            Log::error("SUNAT Job: Falló el envío de {$this->numeroDoc}. Error: " . $e->getMessage());
            
            // Actualizar Doccab para marcar el error
             DB::connection('sqlsrv')->table('Doccab')
                ->where('Numero', $this->numeroDoc)
                ->where('Tipo', $this->tipoDoc)
                ->update([
                    'estado_sunat' => 'ERROR_ENVIO',
                    'mensaje_sunat' => substr($e->getMessage(), 0, 500)
                ]);

            // (Opcional) Podemos re-intentar el job
            // $this->release(60); // Re-intentar en 60 segundos
        }
    }
}