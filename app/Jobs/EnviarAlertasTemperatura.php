<?php

namespace App\Jobs;

use App\Events\TemperaturaFueraRango;
use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnviarAlertasTemperatura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cacheService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900; // 15 minutos

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->cacheService = new CacheService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Iniciando envío de alertas de temperatura', [
            'job_id' => $this->job->getJobId(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        try {
            // 1. Leer sensores de temperatura
            $lecturasTemperatura = $this->obtenerLecturasTemperatura();
            
            // 2. Verificar rangos de temperatura
            $alertasTemperatura = $this->verificarRangosTemperatura($lecturasTemperatura);
            
            // 3. Procesar alertas por ubicación
            $this->procesarAlertasPorUbicacion($alertasTemperatura);
            
            // 4. Generar reportes de temperatura
            $this->generarReportesTemperatura($lecturasTemperatura);
            
            // 5. Limpiar caché de lecturas antiguas
            $this->limpiarLecturasAntiguas();
            
            Log::info('Envío de alertas de temperatura completado', [
                'lecturas_procesadas' => count($lecturasTemperatura),
                'alertas_generadas' => count($alertasTemperatura),
                'job_id' => $this->job->getJobId()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en envío de alertas de temperatura', [
                'error' => $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Obtener lecturas de temperatura de sensores
     */
    private function obtenerLecturasTemperatura(): array
    {
        Log::info('Obteniendo lecturas de temperatura');

        $lecturas = [];

        // Simular lecturas de sensores (en producción esto vendría de IoT devices)
        $ubicaciones = [
            [
                'id' => 'SENSOR_001',
                'ubicacion' => 'Refrigerador Principal',
                'temperatura' => $this->simularLecturaTemperatura(5, 8),
                'humedad' => $this->simularLecturaHumedad(60, 80),
                'fecha_lectura' => now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 'SENSOR_002',
                'ubicacion' => 'Refrigerador Secundario',
                'temperatura' => $this->simularLecturaTemperatura(3, 10),
                'humedad' => $this->simularLecturaHumedad(65, 85),
                'fecha_lectura' => now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 'SENSOR_003',
                'ubicacion' => 'Almacén Principal',
                'temperatura' => $this->simularLecturaTemperatura(18, 25),
                'humedad' => $this->simularLecturaHumedad(45, 65),
                'fecha_lectura' => now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 'SENSOR_004',
                'ubicacion' => 'Área de Ventas',
                'temperatura' => $this->simularLecturaTemperatura(22, 28),
                'humedad' => $this->simularLecturaHumedad(40, 60),
                'fecha_lectura' => now()->format('Y-m-d H:i:s')
            ]
        ];

        foreach ($ubicaciones as $ubicacion) {
            $lecturas[] = $this->procesarLectura($ubicacion);
        }

        // Guardar lecturas en caché para históricos
        $this->cachearLecturas($lecturas);
        
        return $lecturas;
    }

    /**
     * Simular lectura de temperatura (en producción sería real)
     */
    private function simularLecturaTemperatura(float $min, float $max): float
    {
        return round(rand($min * 10, $max * 10) / 10, 1);
    }

    /**
     * Simular lectura de humedad (en producción sería real)
     */
    private function simularLecturaHumedad(float $min, float $max): int
    {
        return rand($min, $max);
    }

    /**
     * Procesar lectura individual
     */
    private function procesarLectura(array $ubicacion): array
    {
        $sensor = DB::table('sensores_temperatura')
            ->where('codigo_sensor', $ubicacion['id'])
            ->first();

        $rangoMinimo = $sensor->rango_minimo ?? 0;
        $rangoMaximo = $sensor->rango_maximo ?? 30;

        return [
            'sensor_id' => $ubicacion['id'],
            'ubicacion' => $ubicacion['ubicacion'],
            'temperatura' => $ubicacion['temperatura'],
            'humedad' => $ubicacion['humedad'],
            'fecha_lectura' => $ubicacion['fecha_lectura'],
            'rango_minimo' => $rangoMinimo,
            'rango_maximo' => $rangoMaximo,
            'fuera_rango' => $ubicacion['temperatura'] < $rangoMinimo || $ubicacion['temperatura'] > $rangoMaximo
        ];
    }

    /**
     * Verificar rangos de temperatura
     */
    private function verificarRangosTemperatura(array $lecturas): array
    {
        Log::info('Verificando rangos de temperatura');

        $alertas = [];

        foreach ($lecturas as $lectura) {
            if ($lectura['fuera_rango']) {
                // Obtener productos en la ubicación
                $productosAfectados = $this->obtenerProductosPorUbicacion($lectura['ubicacion']);
                
                // Crear alerta de temperatura fuera de rango
                $alerta = new TemperaturaFueraRango(
                    $lectura['temperatura'],
                    $lectura['rango_minimo'],
                    $lectura['rango_maximo'],
                    $lectura['ubicacion'],
                    $productosAfectados
                );
                
                // Lanzar evento
                event($alerta);
                
                // Registrar alerta en base de datos
                $this->registrarAlertaTemperatura($lectura, $productosAfectados);
                
                $alertas[] = $lectura;
            }
        }

        return $alertas;
    }

    /**
     * Obtener productos por ubicación
     */
    private function obtenerProductosPorUbicacion(string $ubicacion): array
    {
        // Determinar tipo de productos según ubicación
        $productos = [];
        
        if (strpos($ubicacion, 'Refrigerador') !== false) {
            // Productos refrigerados
            $productosDB = DB::table('productos')
                ->leftJoin('lotes_productos', 'productos.id', '=', 'lotes_productos.producto_id')
                ->where('productos.requiere_refrigeracion', true)
                ->where('lotes_productos.stock', '>', 0)
                ->select('productos.*', 'lotes_productos.stock')
                ->get();
        } else {
            // Productos generales
            $productosDB = DB::table('productos')
                ->leftJoin('lotes_productos', 'productos.id', '=', 'lotes_productos.producto_id')
                ->where('lotes_productos.stock', '>', 0)
                ->select('productos.*', 'lotes_productos.stock')
                ->limit(10) // Limitar para evitar sobrecarga
                ->get();
        }

        foreach ($productosDB as $producto) {
            $productos[] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'stock' => $producto->stock,
                'precio_venta' => $producto->precio_venta,
                'fecha_vencimiento' => $producto->fecha_vencimiento ?? null
            ];
        }

        return $productos;
    }

    /**
     * Procesar alertas por ubicación
     */
    private function procesarAlertasPorUbicacion(array $alertasTemperatura)
    {
        foreach ($alertasTemperatura as $alerta) {
            // Enviar notificaciones push si está configurado
            if (config('sifano.push_notifications', true)) {
                $this->enviarNotificacionPush($alerta);
            }

            // Enviar SMS para alertas críticas
            if ($this->esAlertaCritica($alerta)) {
                $this->enviarSMS($alerta);
            }

            // Enviar email
            $this->enviarEmail($alerta);
        }
    }

    /**
     * Registrar alerta en base de datos
     */
    private function registrarAlertaTemperatura(array $lectura, array $productosAfectados)
    {
        DB::table('alertas_temperatura')->insert([
            'sensor_id' => $lectura['sensor_id'],
            'ubicacion' => $lectura['ubicacion'],
            'temperatura_registrada' => $lectura['temperatura'],
            'rango_minimo' => $lectura['rango_minimo'],
            'rango_maximo' => $lectura['rango_maximo'],
            'humedad' => $lectura['humedad'],
            'productos_afectados' => count($productosAfectados),
            'es_critica' => $this->esAlertaCritica($lectura),
            'fecha_lectura' => $lectura['fecha_lectura'],
            'created_at' => now()
        ]);
    }

    /**
     * Verificar si es alerta crítica
     */
    private function esAlertaCritica(array $lectura): bool
    {
        $temperatura = $lectura['temperatura'];
        
        // Crítico si está muy fuera del rango
        if ($temperatura < 0 || $temperatura > 40) {
            return true;
        }
        
        // Crítico para productos refrigerados
        if (strpos($lectura['ubicacion'], 'Refrigerador') !== false && 
            ($temperatura < 2 || $temperatura > 8)) {
            return true;
        }
        
        return false;
    }

    /**
     * Generar reportes de temperatura
     */
    private function generarReportesTemperatura(array $lecturas)
    {
        Log::info('Generando reportes de temperatura');

        $fecha = now()->format('Y-m-d');
        $hora = now()->format('H:i');

        // Guardar lecturas en históricos
        foreach ($lecturas as $lectura) {
            DB::table('historico_temperatura')->insert([
                'sensor_id' => $lectura['sensor_id'],
                'ubicacion' => $lectura['ubicacion'],
                'temperatura' => $lectura['temperatura'],
                'humedad' => $lectura['humedad'],
                'fecha_lectura' => $lectura['fecha_lectura'],
                'fecha_registro' => $fecha,
                'hora_registro' => $hora
            ]);
        }

        // Generar resumen del día
        $resumen = $this->generarResumenDiario($fecha);
        $this->cacheService->remember(
            "resumen_temperatura_{$fecha}",
            3600 * 24,
            function () use ($resumen) {
                return $resumen;
            }
        );

        // Generar alertas del día
        $this->generarAlertasDiarias($fecha);
    }

    /**
     * Generar resumen diario
     */
    private function generarResumenDiario(string $fecha): array
    {
        $resumen = DB::table('historico_temperatura')
            ->select(
                'ubicacion',
                DB::raw('AVG(temperatura) as temperatura_promedio'),
                DB::raw('MIN(temperatura) as temperatura_minima'),
                DB::raw('MAX(temperatura) as temperatura_maxima'),
                DB::raw('COUNT(*) as total_lecturas'),
                DB::raw('SUM(CASE WHEN temperatura < rango_minimo OR temperatura > rango_maximo THEN 1 ELSE 0 END) as lecturas_fuera_rango')
            )
            ->where('fecha_registro', $fecha)
            ->groupBy('ubicacion')
            ->get();

        return [
            'fecha' => $fecha,
            'resumen_por_ubicacion' => $resumen->toArray(),
            'total_lecturas' => DB::table('historico_temperatura')
                ->where('fecha_registro', $fecha)
                ->count(),
            'fecha_generacion' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Generar alertas diarias
     */
    private function generarAlertasDiarias(string $fecha)
    {
        $alertasDiarias = DB::table('alertas_temperatura')
            ->whereDate('created_at', $fecha)
            ->select(
                DB::raw('COUNT(*) as total_alertas'),
                DB::raw('SUM(CASE WHEN es_critica THEN 1 ELSE 0 END) as alertas_criticas'),
                'ubicacion',
                DB::raw('COUNT(DISTINCT ubicacion) as ubicaciones_afectadas')
            )
            ->groupBy('ubicacion')
            ->get();

        // Enviar resumen diario a administradores
        foreach ($alertasDiarias as $alerta) {
            if ($alerta->alertas_criticas > 0) {
                $this->enviarResumenCritico($alerta, $fecha);
            }
        }
    }

    /**
     * Enviar resumen crítico
     */
    private function enviarResumenCritico($alerta, string $fecha)
    {
        // En una implementación real, aquí enviarías el resumen
        Log::warning('Resumen crítico de temperatura enviado', [
            'ubicacion' => $alerta->ubicacion,
            'fecha' => $fecha,
            'alertas_criticas' => $alerta->alertas_criticas
        ]);
    }

    /**
     * Cachear lecturas
     */
    private function cachearLecturas(array $lecturas)
    {
        $this->cacheService->remember(
            "lecturas_temperatura_" . now()->format('Y-m-d-H-i'),
            3600,
            function () use ($lecturas) {
                return $lecturas;
            }
        );
    }

    /**
     * Limpiar lecturas antiguas
     */
    private function limpiarLecturasAntiguas()
    {
        $fechaLimite = now()->subDays(30)->format('Y-m-d');
        
        DB::table('historico_temperatura')
            ->where('fecha_registro', '<', $fechaLimite)
            ->delete();
    }

    /**
     * Enviar notificación push
     */
    private function enviarNotificacionPush(array $alerta)
    {
        // Implementar notificaciones push
        Log::info('Notificación push enviada', [
            'sensor_id' => $alerta['sensor_id'],
            'ubicacion' => $alerta['ubicacion'],
            'temperatura' => $alerta['temperatura']
        ]);
    }

    /**
     * Enviar SMS
     */
    private function enviarSMS(array $alerta)
    {
        // Implementar envío de SMS
        Log::alert('SMS de alerta enviado', [
            'sensor_id' => $alerta['sensor_id'],
            'ubicacion' => $alerta['ubicacion'],
            'temperatura' => $alerta['temperatura']
        ]);
    }

    /**
     * Enviar email
     */
    private function enviarEmail(array $alerta)
    {
        // Implementar envío de email
        Log::info('Email de alerta enviado', [
            'sensor_id' => $alerta['sensor_id'],
            'ubicacion' => $alerta['ubicacion'],
            'temperatura' => $alerta['temperatura']
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
        Log::error('Job EnviarAlertasTemperatura falló', [
            'error' => $exception->getMessage(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}