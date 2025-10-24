<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReniecService
{
    protected $apiKey;
    protected $apiBaseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->apiKey = config('reniec.api_key', '');
        $this->apiBaseUrl = config('reniec.api_url', 'https://apisi.net.pe/api');
        $this->timeout = config('reniec.timeout', 30);
    }

    /**
     * Validar DNI de persona natural
     */
    public function validarDni(string $dni): array
    {
        try {
            // Validar formato básico del DNI
            if (!$this->validarFormatoDni($dni)) {
                return [
                    'success' => false,
                    'valido' => false,
                    'error' => 'Formato de DNI inválido',
                    'codigo_error' => 'FORMATO_INVALIDO'
                ];
            }

            // Verificar cache primero
            $cacheKey = "reniec_dni_{$dni}";
            $cached = Cache::get($cacheKey);
            
            if ($cached && $cached['expires_at'] > now()) {
                return [
                    'success' => true,
                    'valido' => $cached['valido'],
                    'datos' => $cached['datos'] ?? null,
                    'fuente' => 'cache',
                    'cached_at' => $cached['cached_at']
                ];
            }

            // Consultar a RENIEC
            $resultado = $this->consultarReniecDni($dni);

            // Cachear resultado por 24 horas
            $cacheData = [
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'datos' => $resultado['datos'] ?? null,
                'cached_at' => now(),
                'expires_at' => now()->addHours(24)
            ];

            Cache::put($cacheKey, $cacheData, now()->addHours(24));

            Log::info('Consulta DNI realizada', [
                'dni' => $dni,
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'fuente' => 'api'
            ]);

            return [
                'success' => $resultado['success'],
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'datos' => $resultado['datos'] ?? null,
                'fuente' => 'api',
                'consultado_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Error al validar DNI', [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'valido' => false,
                'error' => 'Error al validar DNI: ' . $e->getMessage(),
                'codigo_error' => 'ERROR_CONSULTA'
            ];
        }
    }

    /**
     * Validar RUC de empresa
     */
    public function validarRuc(string $ruc): array
    {
        try {
            // Validar formato básico del RUC
            if (!$this->validarFormatoRuc($ruc)) {
                return [
                    'success' => false,
                    'valido' => false,
                    'error' => 'Formato de RUC inválido',
                    'codigo_error' => 'FORMATO_INVALIDO'
                ];
            }

            // Verificar cache primero
            $cacheKey = "reniec_ruc_{$ruc}";
            $cached = Cache::get($cacheKey);
            
            if ($cached && $cached['expires_at'] > now()) {
                return [
                    'success' => true,
                    'valido' => $cached['valido'],
                    'datos' => $cached['datos'] ?? null,
                    'fuente' => 'cache',
                    'cached_at' => $cached['cached_at']
                ];
            }

            // Consultar a SUNAT
            $resultado = $this->consultarSunatRuc($ruc);

            // Cachear resultado por 24 horas
            $cacheData = [
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'datos' => $resultado['datos'] ?? null,
                'cached_at' => now(),
                'expires_at' => now()->addHours(24)
            ];

            Cache::put($cacheKey, $cacheData, now()->addHours(24));

            Log::info('Consulta RUC realizada', [
                'ruc' => $ruc,
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'fuente' => 'api'
            ]);

            return [
                'success' => $resultado['success'],
                'valido' => $resultado['success'] && !empty($resultado['datos']),
                'datos' => $resultado['datos'] ?? null,
                'fuente' => 'api',
                'consultado_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Error al validar RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'valido' => false,
                'error' => 'Error al validar RUC: ' . $e->getMessage(),
                'codigo_error' => 'ERROR_CONSULTA'
            ];
        }
    }

    /**
     * Validar múltiples documentos en lote
     */
    public function validarDocumentosLote(array $documentos): array
    {
        $resultados = [];
        $errores = [];

        foreach ($documentos as $index => $documento) {
            try {
                $tipo = $documento['tipo'] ?? '';
                $numero = $documento['numero'] ?? '';
                
                if (empty($tipo) || empty($numero)) {
                    $errores[] = [
                        'indice' => $index,
                        'error' => 'Tipo y número son obligatorios'
                    ];
                    continue;
                }

                $resultado = match($tipo) {
                    'DNI' => $this->validarDni($numero),
                    'RUC' => $this->validarRuc($numero),
                    default => [
                        'success' => false,
                        'error' => 'Tipo de documento no soportado'
                    ]
                };

                $resultados[] = [
                    'indice' => $index,
                    'tipo' => $tipo,
                    'numero' => $numero,
                    'resultado' => $resultado
                ];

            } catch (\Exception $e) {
                $errores[] = [
                    'indice' => $index,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => empty($errores),
            'total_procesados' => count($resultados),
            'total_errores' => count($errores),
            'resultados' => $resultados,
            'errores' => $errores
        ];
    }

    /**
     * Obtener información detallada de persona por DNI
     */
    public function obtenerInformacionPersona(string $dni): array
    {
        $validacion = $this->validarDni($dni);
        
        if (!$validacion['success'] || !$validacion['valido']) {
            return [
                'success' => false,
                'error' => 'DNI no válido o no encontrado'
            ];
        }

        $datos = $validacion['datos'];
        
        return [
            'success' => true,
            'persona' => [
                'dni' => $dni,
                'nombres' => $datos['nombres'] ?? null,
                'apellido_paterno' => $datos['apellido_paterno'] ?? null,
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'nombre_completo' => trim(($datos['nombres'] ?? '') . ' ' . 
                                       ($datos['apellido_paterno'] ?? '') . ' ' . 
                                       ($datos['apellido_materno'] ?? '')),
                'sexo' => $this->determinarSexo($datos),
                'estado_civil' => $datos['estado_civil'] ?? null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'nacionalidad' => $datos['nacionalidad'] ?? 'Peruana',
                'departamento' => $datos['departamento'] ?? null,
                'provincia' => $datos['provincia'] ?? null,
                'distrito' => $datos['distrito'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'grado_instruccion' => $datos['grado_instruccion'] ?? null,
                'estado' => 'ACTIVO'
            ],
            'metadata' => [
                'fuente' => $validacion['fuente'],
                'consultado_at' => $validacion['consultado_at'] ?? now(),
                'cache_expiracion' => $validacion['cached_at'] ?? null
            ]
        ];
    }

    /**
     * Obtener información detallada de empresa por RUC
     */
    public function obtenerInformacionEmpresa(string $ruc): array
    {
        $validacion = $this->validarRuc($ruc);
        
        if (!$validacion['success'] || !$validacion['valido']) {
            return [
                'success' => false,
                'error' => 'RUC no válido o no encontrado'
            ];
        }

        $datos = $validacion['datos'];
        
        return [
            'success' => true,
            'empresa' => [
                'ruc' => $ruc,
                'razon_social' => $datos['razon_social'] ?? null,
                'nombre_comercial' => $datos['nombre_comercial'] ?? null,
                'estado' => $datos['estado'] ?? 'ACTIVO',
                'condicion' => $datos['condicion'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'distrito' => $datos['distrito'] ?? null,
                'provincia' => $datos['provincia'] ?? null,
                'departamento' => $datos['departamento'] ?? null,
                'fecha_inscripcion' => $datos['fecha_inscripcion'] ?? null,
                'actividad_economica' => $datos['actividad_economica'] ?? null,
                'tipo_empresa' => $this->determinarTipoEmpresa($ruc),
                'tamaño_empresa' => $this->determinarTamañoEmpresa($datos['actividad_economica'] ?? ''),
                'padrones' => $datos['padrones'] ?? []
            ],
            'metadata' => [
                'fuente' => $validacion['fuente'],
                'consultado_at' => $validacion['consultado_at'] ?? now(),
                'cache_expiracion' => $validacion['cached_at'] ?? null
            ]
        ];
    }

    /**
     * Generar reporte de consultas realizadas
     */
    public function generarReporteConsultas(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        try {
            // En implementación real consultaría base de datos de consultas
            // Por ahora simulamos datos
            
            $reportes = [
                'periodo' => [
                    'inicio' => $fechaInicio->format('Y-m-d'),
                    'fin' => $fechaFin->format('Y-m-d')
                ],
                'resumen' => [
                    'total_consultas' => 150,
                    'dnis_consultados' => 120,
                    'rucs_consultados' => 30,
                    'consultas_exitosas' => 145,
                    'consultas_fallidas' => 5,
                    'consultas_cache' => 80,
                    'consultas_api' => 70
                ],
                'por_dia' => [],
                'por_tipo' => [],
                'errores_frecuentes' => []
            ];
            
            // Generar datos por día (simulado)
            $fechaActual = clone $fechaInicio;
            while ($fechaActual->lte($fechaFin)) {
                $reportes['por_dia'][] = [
                    'fecha' => $fechaActual->format('Y-m-d'),
                    'consultas' => rand(5, 25),
                    'exitosas' => rand(4, 24),
                    'cache' => rand(2, 15)
                ];
                $fechaActual->addDay();
            }
            
            // Por tipo
            $reportes['por_tipo'] = [
                [
                    'tipo' => 'DNI',
                    'consultas' => 120,
                    'exitosas' => 115,
                    'porcentaje_exito' => 95.83
                ],
                [
                    'tipo' => 'RUC',
                    'consultas' => 30,
                    'exitosas' => 30,
                    'porcentaje_exito' => 100.00
                ]
            ];
            
            // Errores frecuentes
            $reportes['errores_frecuentes'] = [
                [
                    'error' => 'FORMATO_INVALIDO',
                    'ocurrencias' => 3,
                    'descripcion' => 'Formato de documento inválido'
                ],
                [
                    'error' => 'DOCUMENTO_NO_ENCONTRADO',
                    'ocurrencias' => 2,
                    'descripcion' => 'Documento no encontrado en base de datos'
                ]
            ];
            
            return [
                'success' => true,
                'reporte' => $reportes
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de consultas', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Limpiar cache de consultas
     */
    public function limpiarCache(?string $documento = null): array
    {
        try {
            if ($documento) {
                // Limpiar cache específico
                $cacheKeyDni = "reniec_dni_{$documento}";
                $cacheKeyRuc = "reniec_ruc_{$documento}";
                
                Cache::forget($cacheKeyDni);
                Cache::forget($cacheKeyRuc);
                
                $limpios = 2;
            } else {
                // Limpiar todo el cache relacionado con RENIEC
                $limpios = 0;
                
                // En Laravel, no hay forma directa de limpiar por prefijo
                // En implementación real usaríamos Redis con filtros
                Log::warning('No se puede limpiar todo el cache de RENIEC automáticamente');
            }
            
            Log::info('Cache de RENIEC limpiado', [
                'documento' => $documento,
                'registros_limpiados' => $limpios
            ]);
            
            return [
                'success' => true,
                'registros_limpiados' => $limpios,
                'message' => 'Cache limpiado exitosamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Métodos privados de apoyo

    private function validarFormatoDni(string $dni): bool
    {
        // DNI debe tener 8 dígitos
        return preg_match('/^\d{8}$/', $dni);
    }

    private function validarFormatoRuc(string $ruc): bool
    {
        // RUC debe empezar con 10, 15, 17, 20 y tener 11 dígitos
        return preg_match('/^(10|15|17|20)\d{9}$/', $ruc);
    }

    private function consultarReniecDni(string $dni): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->apiBaseUrl . '/dni/' . $dni, [
                    'api_key' => $this->apiKey
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'datos' => $data,
                    'fuente' => 'reniec_api'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error en respuesta de RENIEC: ' . $response->status()
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function consultarSunatRuc(string $ruc): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->apiBaseUrl . '/ruc/' . $ruc, [
                    'api_key' => $this->apiKey
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'datos' => $data,
                    'fuente' => 'sunat_api'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error en respuesta de SUNAT: ' . $response->status()
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function determinarSexo(array $datos): ?string
    {
        // Determinar sexo basado en nombres (heurística básica)
        $nombres = strtolower($datos['nombres'] ?? '');
        
        $nombresFemeninos = ['maria', 'ana', 'carmen', 'josefa', 'luisa', 'pilar', 'mercedes'];
        $nombresMasculinos = ['juan', 'carlos', 'jose', 'miguel', 'antonio', 'francisco', 'luis'];
        
        foreach ($nombresFemeninos as $nombre) {
            if (strpos($nombres, $nombre) !== false) {
                return 'F';
            }
        }
        
        foreach ($nombresMasculinos as $nombre) {
            if (strpos($nombres, $nombre) !== false) {
                return 'M';
            }
        }
        
        return null;
    }

    private function determinarTipoEmpresa(string $ruc): string
    {
        $prefijo = substr($ruc, 0, 2);
        
        return match($prefijo) {
            '10' => 'PERSONA_NATURAL',
            '15' => 'EMPRESA_INDIVIDUAL_RESPONSABILIDAD_LIMITADA',
            '17' => 'SOCIEDAD_ANONIMA',
            '20' => 'INSTITUCION_PRIVADA',
            default => 'DESCONOCIDO'
        };
    }

    private function determinarTamañoEmpresa(string $actividadEconomica): string
    {
        // Clasificación básica por actividad económica
        $actividadesGrandes = [
            'BANCA', 'SEGUROS', 'PETROLEO', 'MINERIA', 'TELECOMUNICACIONES',
            'ELECTRICIDAD', 'AGRICULTURA', 'CONSTRUCCION'
        ];
        
        $actividad = strtoupper($actividadEconomica);
        
        foreach ($actividadesGrandes as $actividadGrande) {
            if (strpos($actividad, $actividadGrande) !== false) {
                return 'GRAN_EMPRESA';
            }
        }
        
        return 'PYME';
    }
}




namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificacionService
{
    /**
     * Obtener notificaciones pendientes para un usuario
     */
    public function obtenerNotificacionesPendientes($idusuario = null)
    {
        try {
            $query = "
                SELECT 
                    n.id,
                    n.titulo,
                    n.mensaje,
                    n.tipo,
                    n.fecha_creacion,
                    n.leida,
                    n.url_accion,
                    n.prioridad,
                    CASE 
                        WHEN n.prioridad = 'alta' THEN 'danger'
                        WHEN n.prioridad = 'media' THEN 'warning'
                        ELSE 'info'
                    END as badge_class
                FROM notificaciones n
                WHERE n.fecha_vencimiento IS NULL OR n.fecha_vencimiento >= GETDATE()
                AND n.leida = 0
            ";

            $params = [];

            if ($idusuario) {
                $query .= " AND (n.idusuario = ? OR n.idusuario IS NULL)";
                $params[] = $idusuario;
            }

            $query .= " ORDER BY n.fecha_creacion DESC";

            return DB::connection('sqlsrv')->select($query, $params);
        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones pendientes: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Crear nueva notificación
     */
    public function crearNotificacion($datos)
    {
        try {
            $query = "
                INSERT INTO notificaciones (
                    titulo, mensaje, tipo, idusuario, url_accion, prioridad, 
                    fecha_creacion, leida, fecha_vencimiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $datos['titulo'] ?? 'Notificación',
                $datos['mensaje'] ?? '',
                $datos['tipo'] ?? 'info',
                $datos['idusuario'] ?? null,
                $datos['url_accion'] ?? null,
                $datos['prioridad'] ?? 'baja',
                Carbon::now(),
                0,
                $datos['fecha_vencimiento'] ?? null
            ];

            DB::connection('sqlsrv')->insert($query, $params);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al crear notificación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida($id)
    {
        try {
            $query = "
                UPDATE notificaciones 
                SET leida = 1, fecha_leida = ? 
                WHERE id = ?
            ";

            DB::connection('sqlsrv')->update($query, [Carbon::now(), $id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al marcar notificación como leída: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener contadores de notificaciones
     */
    public function obtenerContadores($idusuario = null)
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_pendientes,
                    SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as altas,
                    SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as medias,
                    SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as bajas
                FROM notificaciones n
                WHERE n.leida = 0
                AND (n.fecha_vencimiento IS NULL OR n.fecha_vencimiento >= GETDATE())
            ";

            $params = [];

            if ($idusuario) {
                $query .= " AND (n.idusuario = ? OR n.idusuario IS NULL)";
                $params[] = $idusuario;
            }

            $result = DB::connection('sqlsrv')->selectOne($query, $params);

            return [
                'total' => $result->total_pendientes ?? 0,
                'altas' => $result->altas ?? 0,
                'medias' => $result->medias ?? 0,
                'bajas' => $result->bajas ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Error al obtener contadores de notificaciones: ' . $e->getMessage());
            return ['total' => 0, 'altas' => 0, 'medias' => 0, 'bajas' => 0];
        }
    }

    /**
     * Notificaciones específicas del sistema contable
     */
    public function notificarSaldoVencido($documentos)
    {
        try {
            foreach ($documentos as $doc) {
                $this->crearNotificacion([
                    'titulo' => 'Saldo Vencido',
                    'mensaje' => "El documento {$doc['serie']}-{$doc['numero']} tiene saldo vencido de S/ {$doc['saldo']}",
                    'tipo' => 'warning',
                    'idusuario' => $doc['idusuario_vendedor'] ?? null,
                    'url_accion' => "/contabilidad/ctacliente/{$doc['id']}",
                    'prioridad' => 'alta'
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error al notificar saldos vencidos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar cambio de estado en planilla
     */
    public function notificarCambioEstadoPlanilla($planillaId, $estado, $idusuario)
    {
        try {
            $mensajes = [
                'guardado' => 'Planilla guardada correctamente',
                'enviado' => 'Planilla enviada para revisión',
                'aprobado' => 'Planilla aprobada',
                'rechazado' => 'Planilla rechazada - requiere correcciones'
            ];

            return $this->crearNotificacion([
                'titulo' => 'Estado de Planilla Actualizado',
                'mensaje' => $mensajes[$estado] ?? "Planilla cambió a estado: {$estado}",
                'tipo' => 'info',
                'idusuario' => $idusuario,
                'url_accion' => "/contabilidad/planillas/{$planillaId}",
                'prioridad' => $estado === 'rechazado' ? 'alta' : 'media'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al notificar cambio de estado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar notificaciones expiradas
     */
    public function limpiarNotificacionesExpiradas()
    {
        try {
            $query = "
                DELETE FROM notificaciones 
                WHERE fecha_vencimiento < GETDATE() 
                OR (leida = 1 AND fecha_leida < DATEADD(day, -30, GETDATE()))
            ";

            $affected = DB::connection('sqlsrv')->delete($query);
            
            Log::info("Limpieza de notificaciones: {$affected} registros eliminados");
            
            return $affected;
        } catch (\Exception $e) {
            Log::error('Error al limpiar notificaciones expiradas: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estadísticas de notificaciones para admin
     */
    public function obtenerEstadisticas()
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_notificaciones,
                    SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN prioridad = 'alta' AND leida = 0 THEN 1 ELSE 0 END) as altas_pendientes,
                    SUM(CASE WHEN fecha_creacion >= DATEADD(day, -7, GETDATE()) THEN 1 ELSE 0 END) as ultimos_7_dias
                FROM notificaciones
            ";

            return DB::connection('sqlsrv')->selectOne($query);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return null;
        }
    }
}
