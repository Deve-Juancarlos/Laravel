<?php

namespace App\Jobs;

use App\Events\MedicamentoVencido;
use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificarVencimientos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fechaVerificacion;
    protected $cacheService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutos

    /**
     * Create a new job instance.
     *
     * @param string|null $fechaVerificacion
     */
    public function __construct(string $fechaVerificacion = null)
    {
        $this->fechaVerificacion = $fechaVerificacion ? Carbon::parse($fechaVerificacion) : Carbon::now();
        $this->cacheService = new CacheService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Iniciando verificación de vencimientos', [
            'fecha_verificacion' => $this->fechaVerificacion->format('Y-m-d'),
            'job_id' => $this->job->getJobId()
        ]);

        try {
            // 1. Verificar medicamentos próximos a vencer
            $medicamentosVencidos = $this->verificarMedicamentosVencidos();
            
            // 2. Verificar documentos próximos a vencer
            $documentosVencidos = $this->verificarDocumentosVencidos();
            
            // 3. Verificar licencias y permisos
            $licenciasVencidas = $this->verificarLicenciasVencidas();
            
            // 4. Generar reportes de vencimiento
            $reportesGenerados = $this->generarReportesVencimiento();
            
            // 5. Limpiar caché de vencimientos
            $this->cacheService->invalidateGlobalCache(['vencimientos']);

            Log::info('Verificación de vencimientos completada', [
                'medicamentos_vencidos' => $medicamentosVencidos,
                'documentos_vencidos' => $documentosVencidos,
                'licencias_vencidas' => $licenciasVencidas,
                'reportes_generados' => count($reportesGenerados),
                'job_id' => $this->job->getJobId()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en verificación de vencimientos', [
                'error' => $e->getMessage(),
                'fecha_verificacion' => $this->fechaVerificacion->format('Y-m-d'),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Verificar medicamentos próximos a vencer
     */
    private function verificarMedicamentosVencidos(): int
    {
        Log::info('Verificando medicamentos próximos a vencer');

        $medicamentosVencidos = 0;
        $umbralVencimiento = $this->fechaVerificacion->copy()->addDays(30); // 30 días de anticipación

        // Obtener medicamentos con stock y próximos a vencer
        $productos = DB::table('productos')
            ->leftJoin('lotes_productos', 'productos.id', '=', 'lotes_productos.producto_id')
            ->where('productos.activo', true)
            ->where('lotes_productos.stock', '>', 0)
            ->where('lotes_productos.fecha_vencimiento', '>', $this->fechaVerificacion->format('Y-m-d'))
            ->where('lotes_productos.fecha_vencimiento', '<=', $umbralVencimiento->format('Y-m-d'))
            ->select(
                'productos.*',
                'lotes_productos.lote',
                'lotes_productos.fecha_vencimiento',
                'lotes_productos.stock'
            )
            ->get();

        foreach ($productos as $producto) {
            $fechaVencimiento = Carbon::parse($producto->fecha_vencimiento);
            $diasParaVencer = $this->fechaVerificacion->diffInDays($fechaVencimiento);
            
            // Determinar si es crítico (7 días o menos)
            $esCritico = $diasParaVencer <= 7;

            // Lanzar evento MedicamentoVencido
            event(new MedicamentoVencido($producto, $diasParaVencer, $esCritico));

            $medicamentosVencidos++;
            
            Log::info("Medicamento próximo a vencer detectado", [
                'producto_id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'fecha_vencimiento' => $producto->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
                'es_critico' => $esCritico
            ]);
        }

        return $medicamentosVencidos;
    }

    /**
     * Verificar documentos próximos a vencer
     */
    private function verificarDocumentosVencidos(): int
    {
        Log::info('Verificando documentos próximos a vencer');

        $documentosVencidos = 0;
        $umbralVencimiento = $this->fechaVerificacion->copy()->addDays(60); // 60 días de anticipación

        // Verificar licencias sanitarias
        $licenciasSanitarias = DB::table('licencias_sanitarias')
            ->where('fecha_vencimiento', '>', $this->fechaVerificacion->format('Y-m-d'))
            ->where('fecha_vencimiento', '<=', $umbralVencimiento->format('Y-m-d'))
            ->where('estado', 'VIGENTE')
            ->get();

        foreach ($licenciasSanitarias as $licencia) {
            $fechaVencimiento = Carbon::parse($licencia->fecha_vencimiento);
            $diasParaVencer = $this->fechaVerificacion->diffInDays($fechaVencimiento);
            $esCritico = $diasParaVencer <= 15; // Crítico 15 días o menos

            // Registrar vencimiento de licencia
            $this->registrarVencimientoDocumento([
                'tipo' => 'LICENCIA_SANITARIA',
                'numero' => $licencia->numero_licencia,
                'fecha_vencimiento' => $licencia->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
                'es_critico' => $esCritico,
                'entidad' => $licencia->entidad_emisora ?? 'DIGESA'
            ]);

            $documentosVencidos++;
        }

        // Verificar seguros
        $seguros = DB::table('seguros')
            ->where('fecha_vencimiento', '>', $this->fechaVerificacion->format('Y-m-d'))
            ->where('fecha_vencimiento', '<=', $umbralVencimiento->format('Y-m-d'))
            ->where('estado', 'VIGENTE')
            ->get();

        foreach ($seguros as $seguro) {
            $fechaVencimiento = Carbon::parse($seguro->fecha_vencimiento);
            $diasParaVencer = $this->fechaVerificacion->diffInDays($fechaVencimiento);
            $esCritico = $diasParaVencer <= 30;

            $this->registrarVencimientoDocumento([
                'tipo' => 'SEGURO',
                'numero' => $seguro->numero_poliza,
                'fecha_vencimiento' => $seguro->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
                'es_critico' => $esCritico,
                'entidad' => $seguro->compania_seguro ?? 'N/A'
            ]);

            $documentosVencidos++;
        }

        return $documentosVencidos;
    }

    /**
     * Verificar licencias y permisos
     */
    private function verificarLicenciasVencidas(): int
    {
        Log::info('Verificando licencias y permisos');

        $licenciasVencidas = 0;
        $umbralVencimiento = $this->fechaVerificacion->copy()->addDays(90); // 90 días de anticipación

        // Verificar licencias de funcionamiento
        $licenciasFuncionamiento = DB::table('licencias_funcionamiento')
            ->where('fecha_vencimiento', '>', $this->fechaVerificacion->format('Y-m-d'))
            ->where('fecha_vencimiento', '<=', $umbralVencimiento->format('Y-m-d'))
            ->where('estado', 'VIGENTE')
            ->get();

        foreach ($licenciasFuncionamiento as $licencia) {
            $fechaVencimiento = Carbon::parse($licencia->fecha_vencimiento);
            $diasParaVencer = $this->fechaVerificacion->diffInDays($fechaVencimiento);
            $esCritico = $diasParaVencer <= 30;

            $this->registrarVencimientoDocumento([
                'tipo' => 'LICENCIA_FUNCIONAMIENTO',
                'numero' => $licencia->numero_licencia,
                'fecha_vencimiento' => $licencia->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
                'es_critico' => $esCritico,
                'entidad' => $licencia->municipalidad ?? 'Municipalidad'
            ]);

            $licenciasVencidas++;
        }

        // Verificar autorizaciones especiales
        $autorizaciones = DB::table('autorizaciones_especiales')
            ->where('fecha_vencimiento', '>', $this->fechaVerificacion->format('Y-m-d'))
            ->where('fecha_vencimiento', '<=', $umbralVencimiento->format('Y-m-d'))
            ->where('estado', 'VIGENTE')
            ->get();

        foreach ($autorizaciones as $autorizacion) {
            $fechaVencimiento = Carbon::parse($autorizacion->fecha_vencimiento);
            $diasParaVencer = $this->fechaVerificacion->diffInDays($fechaVencimiento);
            $esCritico = $diasParaVencer <= 45;

            $this->registrarVencimientoDocumento([
                'tipo' => 'AUTORIZACION_ESPECIAL',
                'numero' => $autorizacion->numero_autorizacion,
                'fecha_vencimiento' => $autorizacion->fecha_vencimiento,
                'dias_para_vencer' => $diasParaVencer,
                'es_critico' => $esCritico,
                'entidad' => $autorizacion->entidad_reguladora ?? 'MINSA'
            ]);

            $licenciasVencidas++;
        }

        return $licenciasVencidas;
    }

    /**
     * Generar reportes de vencimiento
     */
    private function generarReportesVencimiento(): array
    {
        Log::info('Generando reportes de vencimiento');

        $reportesGenerados = [];
        $fecha = $this->fechaVerificacion->format('Y-m-d');

        // Reporte de medicamentos por vencer
        $reportesGenerados[] = $this->generarReporteMedicamentosVencidos($fecha);
        
        // Reporte de documentos por vencer
        $reportesGenerados[] = $this->generarReporteDocumentosVencidos($fecha);
        
        // Reporte de licencias por vencer
        $reportesGenerados[] = $this->generarReporteLicenciasVencidas($fecha);

        return $reportesGenerados;
    }

    /**
     * Generar reporte de medicamentos vencidos
     */
    private function generarReporteMedicamentosVencidos(string $fecha): array
    {
        $datos = $this->cacheService->get("medicamentos_vencidos_{$fecha}") ?? [];
        
        $reporte = [
            'tipo' => 'MEDICAMENTOS_VENCIDOS',
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'fecha_referencia' => $fecha,
            'total_registros' => count($datos),
            'datos' => $datos
        ];

        // Guardar reporte
        $this->cacheService->remember(
            "reporte_medicamentos_vencidos_{$fecha}",
            3600 * 24,
            function () use ($reporte) {
                return $reporte;
            }
        );

        return $reporte;
    }

    /**
     * Generar reporte de documentos vencidos
     */
    private function generarReporteDocumentosVencidos(string $fecha): array
    {
        $datos = DB::table('vencimientos_documentos')
            ->where('fecha_referencia', $fecha)
            ->where('tipo', 'in', ['LICENCIA_SANITARIA', 'SEGURO'])
            ->get()
            ->toArray();

        $reporte = [
            'tipo' => 'DOCUMENTOS_VENCIDOS',
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'fecha_referencia' => $fecha,
            'total_registros' => count($datos),
            'datos' => $datos
        ];

        $this->cacheService->remember(
            "reporte_documentos_vencidos_{$fecha}",
            3600 * 24,
            function () use ($reporte) {
                return $reporte;
            }
        );

        return $reporte;
    }

    /**
     * Generar reporte de licencias vencidas
     */
    private function generarReporteLicenciasVencidas(string $fecha): array
    {
        $datos = DB::table('vencimientos_documentos')
            ->where('fecha_referencia', $fecha)
            ->whereIn('tipo', ['LICENCIA_FUNCIONAMIENTO', 'AUTORIZACION_ESPECIAL'])
            ->get()
            ->toArray();

        $reporte = [
            'tipo' => 'LICENCIAS_VENCIDAS',
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'fecha_referencia' => $fecha,
            'total_registros' => count($datos),
            'datos' => $datos
        ];

        $this->cacheService->remember(
            "reporte_licencias_vencidas_{$fecha}",
            3600 * 24,
            function () use ($reporte) {
                return $reporte;
            }
        );

        return $reporte;
    }

    /**
     * Registrar vencimiento de documento
     */
    private function registrarVencimientoDocumento(array $datos)
    {
        DB::table('vencimientos_documentos')->insert([
            'tipo' => $datos['tipo'],
            'numero_documento' => $datos['numero'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'dias_para_vencer' => $datos['dias_para_vencer'],
            'es_critico' => $datos['es_critico'],
            'entidad' => $datos['entidad'],
            'fecha_referencia' => $this->fechaVerificacion->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('Job VerificarVencimientos falló', [
            'error' => $exception->getMessage(),
            'fecha_verificacion' => $this->fechaVerificacion->format('Y-m-d'),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}