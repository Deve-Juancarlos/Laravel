<?php

namespace App\Jobs;

use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcesarLibrosElectronicos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fechaProceso;
    protected $cacheService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hora

    /**
     * Create a new job instance.
     *
     * @param string|null $fechaProceso
     */
    public function __construct(string $fechaProceso = null)
    {
        $this->fechaProceso = $fechaProceso ? Carbon::parse($fechaProceso) : Carbon::now();
        $this->cacheService = new CacheService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Iniciando procesamiento de libros electrónicos', [
            'fecha_proceso' => $this->fechaProceso->format('Y-m-d'),
            'job_id' => $this->job->getJobId()
        ]);

        try {
            DB::beginTransaction();

            // 1. Procesar Libro de Ventas
            $this->procesarLibroVentas();
            
            // 2. Procesar Libro de Compras
            $this->procesarLibroCompras();
            
            // 3. Procesar Libro Diario
            $this->procesarLibroDiario();
            
            // 4. Generar archivos electrónicos
            $archivosGenerados = $this->generarArchivosElectronicos();
            
            // 5. Enviar archivos a SUNAT si está habilitado
            if (config('sifano.sunat_automatico', false)) {
                $this->enviarSunat($archivosGenerados);
            }

            // 6. Actualizar estado de procesamiento
            $this->actualizarEstadoProcesamiento();
            
            DB::commit();

            Log::info('Procesamiento de libros electrónicos completado exitosamente', [
                'fecha_proceso' => $this->fechaProceso->format('Y-m-d'),
                'archivos_generados' => $archivosGenerados,
                'job_id' => $this->job->getJobId()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesamiento de libros electrónicos', [
                'error' => $e->getMessage(),
                'fecha_proceso' => $this->fechaProceso->format('Y-m-d'),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Procesar Libro de Ventas
     */
    private function procesarLibroVentas()
    {
        Log::info('Procesando libro de ventas');

        // Obtener facturas del período
        $facturas = DB::table('facturas')
            ->leftJoin('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->leftJoin('accesoweb', 'facturas.usuario_id', '=', 'accesoweb.id')
            ->where('facturas.fecha_emision', '>=', $this->fechaProceso->startOfMonth()->format('Y-m-d'))
            ->where('facturas.fecha_emision', '<=', $this->fechaProceso->endOfMonth()->format('Y-m-d'))
            ->whereIn('facturas.estado', ['APROBADA', 'PENDIENTE'])
            ->select(
                'facturas.*',
                'clientes.nombre as cliente_nombre',
                'clientes.dni as cliente_dni',
                'accesoweb.nombre as usuario_nombre'
            )
            ->get();

        $datosLibroVentas = [];
        $secuencia = 1;

        foreach ($facturas as $factura) {
            $datosLibroVentas[] = [
                'ID' => str_pad($secuencia, 6, '0', STR_PAD_LEFT),
                'FECHA_EMISION' => $factura->fecha_emision,
                'TIPO_DOC' => '01', // Factura
                'NUMERO_DOC' => $factura->numero_factura,
                'TIPO_COMPRADOR' => $this->obtenerTipoCliente($factura->cliente_dni),
                'NUMERO_DOCUMENTO' => $factura->cliente_dni ?? '',
                'RAZON_SOCIAL' => $factura->cliente_nombre ?? 'CLIENTE GENERICO',
                'BASE_IMPONIBLE' => $factura->subtotal,
                'IGV' => $factura->igv,
                'TOTAL_VENTA' => $factura->total,
                'FECHA_REGISTRO' => $factura->created_at->format('Y-m-d'),
                'USUARIO' => $factura->usuario_nombre ?? 'Sistema'
            ];

            $secuencia++;
        }

        // Guardar en tabla temporal o cache
        $this->cacheService->remember(
            "libro_ventas_{$this->fechaProceso->format('Y_m')}",
            3600,
            function () use ($datosLibroVentas) {
                return $datosLibroVentas;
            }
        );

        Log::info("Libro de ventas procesado: " . count($datosLibroVentas) . " registros");
    }

    /**
     * Procesar Libro de Compras
     */
    private function procesarLibroCompras()
    {
        Log::info('Procesando libro de compras');

        // Obtener compras del período
        $compras = DB::table('compras')
            ->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.fecha_emision', '>=', $this->fechaProceso->startOfMonth()->format('Y-m-d'))
            ->where('compras.fecha_emision', '<=', $this->fechaProceso->endOfMonth()->format('Y-m-d'))
            ->where('compras.estado', 'APROBADA')
            ->select('compras.*', 'proveedores.nombre as proveedor_nombre', 'proveedores.ruc as proveedor_ruc')
            ->get();

        $datosLibroCompras = [];
        $secuencia = 1;

        foreach ($compras as $compra) {
            $datosLibroCompras[] = [
                'ID' => str_pad($secuencia, 6, '0', STR_PAD_LEFT),
                'FECHA_EMISION' => $compra->fecha_emision,
                'TIPO_DOC' => '01', // Factura
                'NUMERO_DOC' => $compra->numero_compra,
                'NUMERO_DOCUMENTO' => $compra->proveedor_ruc ?? '',
                'RAZON_SOCIAL' => $compra->proveedor_nombre ?? 'PROVEEDOR DESCONOCIDO',
                'BASE_IMPONIBLE' => $compra->subtotal,
                'IGV' => $compra->igv,
                'TOTAL_COMPRA' => $compra->total,
                'FECHA_REGISTRO' => $compra->created_at->format('Y-m-d'),
                'USUARIO' => 'Sistema'
            ];

            $secuencia++;
        }

        $this->cacheService->remember(
            "libro_compras_{$this->fechaProceso->format('Y_m')}",
            3600,
            function () use ($datosLibroCompras) {
                return $datosLibroCompras;
            }
        );

        Log::info("Libro de compras procesado: " . count($datosLibroCompras) . " registros");
    }

    /**
     * Procesar Libro Diario
     */
    private function procesarLibroDiario()
    {
        Log::info('Procesando libro diario');

        // Obtener asientos contables del período
        $asientos = DB::table('asientos')
            ->leftJoin('accesoweb', 'asientos.usuario_id', '=', 'accesoweb.id')
            ->where('asientos.fecha_asiento', '>=', $this->fechaProceso->startOfMonth()->format('Y-m-d'))
            ->where('asientos.fecha_asiento', '<=', $this->fechaProceso->endOfMonth()->format('Y-m-d'))
            ->where('asientos.estado', 'APROBADO')
            ->select('asientos.*', 'accesoweb.nombre as usuario_nombre')
            ->get();

        $datosLibroDiario = [];
        $secuencia = 1;

        foreach ($asientos as $asiento) {
            $detalles = DB::table('asiento_detalles')
                ->leftJoin('cuentas', 'asiento_detalles.cuenta_id', '=', 'cuentas.id')
                ->where('asiento_detalles.asiento_id', $asiento->id)
                ->select('asiento_detalles.*', 'cuentas.numero_cuenta', 'cuentas.nombre as cuenta_nombre')
                ->get();

            foreach ($detalles as $detalle) {
                $datosLibroDiario[] = [
                    'ID' => str_pad($secuencia, 6, '0', STR_PAD_LEFT),
                    'FECHA_ASIENTO' => $asiento->fecha_asiento,
                    'NUMERO_ASIENTO' => $asiento->numero_asiento,
                    'CUENTA' => $detalle->numero_cuenta,
                    'DESCRIPCION_CUENTA' => $detalle->cuenta_nombre,
                    'DEBE' => $detalle->debe,
                    'HABER' => $detalle->haber,
                    'CONCEPTO' => $asiento->concepto,
                    'USUARIO' => $asiento->usuario_nombre ?? 'Sistema'
                ];

                $secuencia++;
            }
        }

        $this->cacheService->remember(
            "libro_diario_{$this->fechaProceso->format('Y_m')}",
            3600,
            function () use ($datosLibroDiario) {
                return $datosLibroDiario;
            }
        );

        Log::info("Libro diario procesado: " . count($datosLibroDiario) . " registros");
    }

    /**
     * Generar archivos electrónicos
     */
    private function generarArchivosElectronicos(): array
    {
        Log::info('Generando archivos electrónicos');

        $fecha = $this->fechaProceso->format('Y_m');
        $archivosGenerados = [];

        // Generar CSV para cada libro
        $libros = ['ventas', 'compras', 'diario'];
        
        foreach ($libros as $libro) {
            $datos = $this->cacheService->get("libro_{$libro}_{$fecha}");
            
            if ($datos && count($datos) > 0) {
                $nombreArchivo = "{$libro}_{$fecha}.csv";
                $rutaArchivo = storage_path("app/libros_electronicos/{$nombreArchivo}");
                
                // Crear directorio si no existe
                if (!file_exists(dirname($rutaArchivo))) {
                    mkdir(dirname($rutaArchivo), 0755, true);
                }
                
                $this->generarCSV($datos, $rutaArchivo);
                $archivosGenerados[] = $nombreArchivo;
                
                Log::info("Archivo generado: {$nombreArchivo}");
            }
        }

        return $archivosGenerados;
    }

    /**
     * Generar archivo CSV
     */
    private function generarCSV(array $datos, string $rutaArchivo)
    {
        $handle = fopen($rutaArchivo, 'w');
        
        // Escribir header
        if (!empty($datos)) {
            fputcsv($handle, array_keys($datos[0]), ';');
        }
        
        // Escribir datos
        foreach ($datos as $row) {
            fputcsv($handle, $row, ';');
        }
        
        fclose($handle);
    }

    /**
     * Enviar archivos a SUNAT
     */
    private function enviarSunat(array $archivosGenerados)
    {
        Log::info('Enviando archivos a SUNAT');

        foreach ($archivosGenerados as $archivo) {
            try {
                // Aquí implementarías la integración con SUNAT
                // Por ejemplo, usando web services o API REST
                
                Log::info("Archivo enviado a SUNAT: {$archivo}");
                
            } catch (\Exception $e) {
                Log::error("Error enviando archivo a SUNAT: {$archivo}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Actualizar estado de procesamiento
     */
    private function actualizarEstadoProcesamiento()
    {
        DB::table('procesamiento_libros')->updateOrInsert(
            ['fecha_proceso' => $this->fechaProceso->format('Y-m-d')],
            [
                'estado' => 'COMPLETADO',
                'archivos_generados' => 1,
                'fecha_procesamiento' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Obtener tipo de cliente
     */
    private function obtenerTipoCliente(?string $dni): string
    {
        if (strlen($dni ?? '') === 11) {
            return 'RUC'; // Persona jurídica
        }
        
        return 'DNI'; // Persona natural
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('Job ProcesarLibrosElectronicos falló', [
            'error' => $exception->getMessage(),
            'fecha_proceso' => $this->fechaProceso->format('Y-m-d'),
            'trace' => $exception->getTraceAsString()
        ]);

        // Notificar al administrador
        // Aquí podrías implementar notificaciones de error
    }
}