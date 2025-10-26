<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DevController extends Controller
{
    /**
     * HERRAMIENTAS DE DESARROLLO - Debugging y desarrollo para SIFANO
     * Solo accesible por administradores
     */

    public function __construct()
    {
        $this->middleware(['auth', 'check.admin']);
    }

    /**
     * Dashboard de desarrollo
     */
    public function index()
    {
        try {
            $info_sistema = $this->obtenerInfoSistema();
            $estadisticas_dev = $this->obtenerEstadisticasDesarrollo();
            $herramientas_disponibles = $this->obtenerHerramientasDev();

            return [
                'sistema' => $info_sistema,
                'estadisticas' => $estadisticas_dev,
                'herramientas' => $herramientas_disponibles
            ];

        } catch (\Exception $e) {
            \Log::error('Error en DevController::index: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar dashboard de desarrollo'], 500);
        }
    }

    /**
     * Estado de la base de datos
     */
    public function databaseStatus()
    {
        try {
            $conexiones = $this->verificarConexionesBD();
            $estadisticas_bd = $this->obtenerEstadisticasBD();
            $consultas_lentas = $this->obtenerConsultasLentas();
            $tablas_info = $this->obtenerInfoTablas();

            return [
                'conexiones' => $conexiones,
                'estadisticas' => $estadisticas_bd,
                'consultas_lentas' => $consultas_lentas,
                'tablas' => $tablas_info
            ];

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al verificar base de datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Información de tablas SIFANO
     */
    public function tablasSIFANO()
    {
        try {
            $tablas_principales = [
                'accesoweb' => [
                    'descripcion' => 'Tabla de usuarios del sistema',
                    'campos_clave' => ['idusuario', 'usuario', 'tipousuario', 'password'],
                    'registros' => DB::table('accesoweb')->count()
                ],
                'Productos' => [
                    'descripcion' => 'Catálogo de productos',
                    'campos_clave' => ['CodPro', 'Nombre', 'Stock', 'Costo', 'PventaMa'],
                    'registros' => DB::table('Productos')->count()
                ],
                'Clientes' => [
                    'descripcion' => 'Base de datos de clientes',
                    'campos_clave' => ['Codclie', 'Razon', 'Documento', 'Limite'],
                    'registros' => DB::table('Clientes')->count()
                ],
                'Doccab' => [
                    'descripcion' => 'Cabecera de documentos',
                    'campos_clave' => ['Numero', 'Tipo', 'CodClie', 'Fecha', 'Total'],
                    'registros' => DB::table('Doccab')->count()
                ],
                'Docdet' => [
                    'descripcion' => 'Detalle de documentos',
                    'campos_clave' => ['Numero', 'Codpro', 'Cantidad', 'Precio'],
                    'registros' => DB::table('Docdet')->count()
                ],
                'Saldos' => [
                    'descripcion' => 'Control de stock por lote',
                    'campos_clave' => ['codpro', 'almacen', 'lote', 'saldo'],
                    'registros' => DB::table('Saldos')->count()
                ],
                'CtaCliente' => [
                    'descripcion' => 'Cuenta corriente de clientes',
                    'campos_clave' => ['Documento', 'Tipo', 'CodClie', 'Saldo'],
                    'registros' => DB::table('CtaCliente')->count()
                ]
            ];

            return $tablas_principales;

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener información de tablas'], 500);
        }
    }

    /**
     * Test de consultas SQL
     */
    public function testConsultas(Request $request)
    {
        $request->validate([
            'consulta' => 'required|string'
        ]);

        try {
            $consulta = trim($request->consulta);
            
            // Solo permitir consultas SELECT para seguridad
            if (!preg_match('/^select\s+/i', $consulta)) {
                return response()->json(['error' => 'Solo se permiten consultas SELECT'], 400);
            }

            // Ejecutar consulta con límite
            $consulta_limitada = $consulta . ' LIMIT 100';
            $resultados = DB::select($consulta_limitada);

            // Obtener tiempo de ejecución
            $inicio = microtime(true);
            DB::select($consulta_limitada);
            $tiempo_ejecucion = round((microtime(true) - $inicio) * 1000, 2);

            return [
                'resultados' => $resultados,
                'tiempo_ejecucion' => $tiempo_ejecucion . ' ms',
                'total_registros' => count($resultados),
                'consulta' => $consulta
            ];

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en consulta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Monitoreo de rendimiento
     */
    public function rendimiento()
    {
        try {
            $metricas = [
                'cpu' => [
                    'uso_actual' => $this->obtenerUsoCPU(),
                    'carga_sistema' => sys_getloadavg()
                ],
                'memoria' => [
                    'usada' => memory_get_usage(true),
                    'limite' => ini_get('memory_limit'),
                    'pico' => memory_get_peak_usage(true)
                ],
                'base_datos' => [
                    'conexiones_activas' => $this->contarConexionesDB(),
                    'consultas_en_cache' => Cache::get('consultas_count', 0)
                ],
                'aplicacion' => [
                    'sesiones_activas' => Cache::get('sesiones_activas', 0),
                    'requerimientos_minuto' => Cache::get('req_minuto', 0)
                ]
            ];

            return $metricas;

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener métricas'], 500);
        }
    }

    /**
     * Limpiar cache del sistema
     */
    public function limpiarCache(Request $request)
    {
        $tipo = $request->tipo ?? 'all';

        try {
            $resultados = [];

            if ($tipo === 'all' || $tipo === 'cache') {
                Cache::flush();
                $resultados['cache'] = 'Limpiado';
            }

            if ($tipo === 'all' || $tipo === 'views') {
                Artisan::call('view:clear');
                $resultados['views'] = 'Limpiado';
            }

            if ($tipo === 'all' || $tipo === 'config') {
                Artisan::call('config:clear');
                $resultados['config'] = 'Limpiado';
            }

            if ($tipo === 'all' || $tipo === 'routes') {
                Artisan::call('route:clear');
                $resultados['routes'] = 'Limpiado';
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Cache limpiado exitosamente',
                'resultados' => $resultados
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al limpiar cache'], 500);
        }
    }

    /**
     * Logs del sistema en tiempo real
     */
    public function logs(Request $request)
    {
        $nivel = $request->nivel ?? 'all';
        $limite = $request->limite ?? 50;

        try {
            $archivo_log = storage_path('logs/laravel.log');
            
            if (!file_exists($archivo_log)) {
                return ['logs' => [], 'mensaje' => 'Archivo de log no encontrado'];
            }

            $contenido = file_get_contents($archivo_log);
            $lineas = explode("\n", $contenido);
            $logs_filtrados = [];

            foreach (array_reverse($lineas) as $linea) {
                if (trim($linea) === '') continue;
                
                if ($nivel === 'all' || strpos(strtolower($linea), $nivel) !== false) {
                    $logs_filtrados[] = $this->parsearLogLine($linea);
                    if (count($logs_filtrados) >= $limite) break;
                }
            }

            return [
                'logs' => $logs_filtrados,
                'total' => count($logs_filtrados),
                'archivo' => $archivo_log
            ];

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al leer logs'], 500);
        }
    }

    /**
     * Generar datos de prueba
     */
    public function generarDatosPrueba(Request $request)
    {
        $tipo = $request->tipo;
        $cantidad = min($request->cantidad ?? 10, 100); // Máximo 100 registros

        try {
            DB::beginTransaction();

            switch ($tipo) {
                case 'productos':
                    $this->generarProductosPrueba($cantidad);
                    break;
                    
                case 'clientes':
                    $this->generarClientesPrueba($cantidad);
                    break;
                    
                case 'ventas':
                    $this->generarVentasPrueba($cantidad);
                    break;
                    
                default:
                    return response()->json(['error' => 'Tipo de datos no válido'], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => "{$cantidad} registros de {$tipo} generados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al generar datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Herramientas de debugging
     */
    public function debug()
    {
        $info = [
            'php_info' => [
                'version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'extensions' => get_loaded_extensions()
            ],
            'laravel_info' => [
                'version' => app()->version(),
                'environment' => app()->environment(),
                'debug' => config('app.debug')
            ],
            'config_debug' => [
                'database_default' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver')
            ],
            'memoria' => [
                'uso_actual' => memory_get_usage(true),
                'limite' => ini_get('memory_limit'),
                'pico' => memory_get_peak_usage(true)
            ]
        ];

        return $info;
    }

    /**
     * API de prueba de endpoints
     */
    public function testAPI(Request $request)
    {
        $endpoint = $request->endpoint;
        $metodo = $request->method ?? 'GET';
        $datos = $request->datos ? json_decode($request->datos, true) : [];

        try {
            // Simular llamada API (en un entorno real se haría con HTTP client)
            $respuesta_simulada = [
                'status' => 200,
                'mensaje' => 'OK',
                'datos' => [
                    'timestamp' => now(),
                    'endpoint' => $endpoint,
                    'method' => $metodo,
                    'datos_enviados' => $datos
                ]
            ];

            return $respuesta_simulada;

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en test API'], 500);
        }
    }

    /**
     * ===============================================
     * MÉTODOS PRIVADOS DE APOYO
     * ===============================================
     */

    /**
     * Obtiene información general del sistema
     */
    private function obtenerInfoSistema()
    {
        return [
            'php' => PHP_VERSION,
            'sistema_operativo' => php_uname(),
            'laravel' => app()->version(),
            'ambiente' => app()->environment(),
            'espacio_disco' => [
                'libre' => disk_free_space('/'),
                'total' => disk_total_space('/'),
                'porcentaje_usado' => round((1 - (disk_free_space('/') / disk_total_space('/'))) * 100, 2)
            ]
        ];
    }

    /**
     * Obtiene estadísticas de desarrollo
     */
    private function obtenerEstadisticasDesarrollo()
    {
        return [
            'archivos_codigo' => count(glob(app_path('Http/Controllers/*.php'))),
            'rutas_definidas' => count(app('router')->getRoutes()),
            'configuraciones' => count(config()),
            'queries_pendientes' => 0 // Se podría implementar un query log
        ];
    }

    /**
     * Obtiene herramientas de desarrollo disponibles
     */
    private function obtenerHerramientasDev()
    {
        return [
            'database' => 'Verificar estado de BD',
            'performance' => 'Monitoreo de rendimiento',
            'logs' => 'Visualizar logs',
            'cache' => 'Gestión de cache',
            'testing' => 'Generar datos de prueba',
            'debug' => 'Información de debugging'
        ];
    }

    /**
     * Verifica conexiones a base de datos
     */
    private function verificarConexionesBD()
    {
        $conexiones = [];
        
        try {
            // Test conexión principal
            DB::connection()->getPdo();
            $conexiones['principal'] = 'Conectada';
        } catch (\Exception $e) {
            $conexiones['principal'] = 'Error: ' . $e->getMessage();
        }

        return $conexiones;
    }

    /**
     * Obtiene estadísticas de base de datos
     */
    private function obtenerEstadisticasBD()
    {
        return [
            'usuarios' => DB::table('accesoweb')->count(),
            'productos' => DB::table('Productos')->count(),
            'clientes' => DB::table('Clientes')->count(),
            'facturas' => DB::table('Doccab')->count(),
            'stock_movimientos' => DB::table('Saldos')->count()
        ];
    }

    /**
     * Obtiene información de tablas
     */
    private function obtenerInfoTablas()
    {
        $tablas = ['accesoweb', 'Productos', 'Clientes', 'Doccab', 'Docdet', 'Saldos', 'CtaCliente'];
        $info = [];

        foreach ($tablas as $tabla) {
            try {
                $info[$tabla] = [
                    'registros' => DB::table($tabla)->count(),
                    'tamaño' => 'N/A' // Se implementaría con información de BD
                ];
            } catch (\Exception $e) {
                $info[$tabla] = [
                    'registros' => 0,
                    'tamaño' => 'Error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $info;
    }

    /**
     * Obtiene consultas lentas (simulado)
     */
    private function obtenerConsultasLentas()
    {
        // En un entorno real se obtendría del query log o slow query log
        return [
            ['consulta' => 'SELECT * FROM Productos WHERE Stock < 10', 'tiempo' => '150ms'],
            ['consulta' => 'SELECT * FROM Doccab WHERE Fecha = ?', 'tiempo' => '200ms']
        ];
    }

    /**
     * Obtiene uso de CPU
     */
    private function obtenerUsoCPU()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100, 2);
        }
        return 'N/A';
    }

    /**
     * Cuenta conexiones activas de BD
     */
    private function contarConexionesDB()
    {
        // Implementación simplificada
        return DB::getPdo() ? 1 : 0;
    }

    /**
     * Parsea línea de log
     */
    private function parsearLogLine($linea)
    {
        $pattern = '/\[(.*?)\] (.*?)\.(.*?): (.*)/';
        if (preg_match($pattern, $linea, $matches)) {
            return [
                'fecha' => $matches[1],
                'nivel' => strtoupper($matches[2]),
                'archivo' => $matches[3],
                'mensaje' => $matches[4]
            ];
        }
        
        return ['linea' => $linea];
    }

    /**
     * Genera productos de prueba
     */
    private function generarProductosPrueba($cantidad)
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            DB::table('Productos')->insert([
                'CodPro' => 'PROD' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'Nombre' => 'Producto de Prueba ' . $i,
                'Stock' => rand(1, 100),
                'Costo' => rand(10, 100) / 10,
                'PventaMa' => rand(20, 200) / 10,
                'RegSanit' => 'REG' . rand(100000, 999999),
                'TemMax' => 30,
                'TemMin' => 15
            ]);
        }
    }

    /**
     * Genera clientes de prueba
     */
    private function generarClientesPrueba($cantidad)
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            DB::table('Clientes')->insert([
                'Razon' => 'Cliente de Prueba ' . $i,
                'Documento' => '20' . str_pad(rand(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
                'Direccion' => 'Dirección de Prueba ' . $i,
                'Zona' => 'L01',
                'Limite' => rand(1000, 50000),
                'Activo' => 1
            ]);
        }
    }

    /**
     * Genera ventas de prueba
     */
    private function generarVentasPrueba($cantidad)
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $cliente = DB::table('Clientes')->inRandomOrder()->first();
            $producto = DB::table('Productos')->inRandomOrder()->first();
            
            $numero = 'FAC' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            // Cabecera
            DB::table('Doccab')->insert([
                'Numero' => $numero,
                'Tipo' => 1, // 1 = FACTURA según el schema SQL
                'CodClie' => $cliente->Codclie,
                'Fecha' => now()->subDays(rand(0, 30)),
                'Subtotal' => rand(100, 10000),
                'Total' => rand(100, 10000),
                'Moneda' => 1,
                'Vendedor' => 1
            ]);
            
            // Detalle
            DB::table('Docdet')->insert([
                'Numero' => $numero,
                'Codpro' => $producto->CodPro,
                'Cantidad' => rand(1, 10),
                'Precio' => rand(10, 500) / 10
            ]);
        }
    }
}