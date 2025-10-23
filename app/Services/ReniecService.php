<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReniecService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.reniec.token', '83c009f9b7a09201d8a0638a6dfb06b408247b573c5fc378e8b8fd2a524c2e8f');
        $this->baseUrl = config('services.reniec.url', 'https://api.consultasperu.com/api/v1/query');
    }

    /**
     * Consultar DNI con caché para optimizar rendimiento
     */
    public function consultarDNI($numeroDNI)
    {
        try {
            // Verificar caché primero (15 minutos)
            $cacheKey = "dni_{$numeroDNI}";
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($this->baseUrl, [
                'dni' => $numeroDNI
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Guardar en caché
                Cache::put($cacheKey, $data, 900); // 15 minutos
                
                // Log para auditoría
                Log::info('Consulta DNI exitosa', [
                    'dni' => $numeroDNI,
                    'ip' => request()->ip(),
                    'timestamp' => now()
                ]);
                
                return $data;
            }

            Log::warning('Consulta DNI fallida', [
                'dni' => $numeroDNI,
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error en consulta DNI', [
                'dni' => $numeroDNI,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Consultar RUC con caché
     */
    public function consultarRUC($numeroRUC)
    {
        try {
            // Verificar caché primero
            $cacheKey = "ruc_{$numeroRUC}";
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($this->baseUrl, [
                'ruc' => $numeroRUC
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Guardar en caché (30 minutos para RUC)
                Cache::put($cacheKey, $data, 1800);
                
                Log::info('Consulta RUC exitosa', [
                    'ruc' => $numeroRUC,
                    'empresa' => $data['razon_social'] ?? 'N/A',
                    'timestamp' => now()
                ]);
                
                return $data;
            }

            Log::warning('Consulta RUC fallida', [
                'ruc' => $numeroRUC,
                'status_code' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error en consulta RUC', [
                'ruc' => $numeroRUC,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Validar DNI con reglas específicas de Perú
     */
    public function validarDNI($numeroDNI)
    {
        // Validar formato
        if (!preg_match('/^\d{8}$/', $numeroDNI)) {
            return [
                'valido' => false,
                'error' => 'El DNI debe tener exactamente 8 dígitos'
            ];
        }

        // Algoritmo de validación de DNI peruano
        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        
        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$numeroDNI[$i] * $weights[$i + 1];
        }
        
        $checkDigit = (11 - ($sum % 11)) % 10;
        
        if ($checkDigit !== (int)$numeroDNI[8]) {
            return [
                'valido' => false,
                'error' => 'DNI inválido según algoritmo de validación'
            ];
        }

        return ['valido' => true];
    }

    /**
     * Validar RUC con reglas específicas
     */
    public function validarRUC($numeroRUC)
    {
        // Validar formato
        if (!preg_match('/^\d{11}$/', $numeroRUC)) {
            return [
                'valido' => false,
                'error' => 'El RUC debe tener exactamente 11 dígitos'
            ];
        }

        // Validar que empiece con 10, 15, 16, 17 o 20
        $firstTwo = substr($numeroRUC, 0, 2);
        $validStarts = ['10', '15', '16', '17', '20'];
        
        if (!in_array($firstTwo, $validStarts)) {
            return [
                'valido' => false,
                'error' => 'RUC inválido: debe empezar con 10, 15, 16, 17 o 20'
            ];
        }

        // Algoritmo de validación RUC
        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$numeroRUC[$i] * $weights[$i];
        }
        
        $checkDigit = ($sum % 11) < 2 ? ($sum % 11) : (11 - ($sum % 11));
        
        if ($checkDigit !== (int)$numeroRUC[10]) {
            return [
                'valido' => false,
                'error' => 'RUC inválido según algoritmo de validación'
            ];
        }

        return ['valido' => true];
    }

    /**
     * Buscar clientes coincidentes en la base de datos local
     */
    public function buscarEnBaseLocal($termino, $tipo = null)
    {
        try {
            $query = \DB::connection('sqlsrv')
                ->table('Clientes')
                ->select([
                    'Codclie',
                    'Razon',
                    'RucDni as documento',
                    'Direccion',
                    'Telefono1',
                    'Email',
                    'Estado'
                ])
                ->where(function($q) use ($termino) {
                    $q->where('Razon', 'LIKE', "%{$termino}%")
                      ->orWhere('RucDni', 'LIKE', "%{$termino}%")
                      ->orWhere('Direccion', 'LIKE', "%{$termino}%");
                })
                ->where('Activo', 1)
                ->orderBy('Razon')
                ->limit(20);

            if ($tipo) {
                $query->where('Tipo', $tipo);
            }

            $resultados = $query->get();

            return [
                'encontrados' => $resultados->count(),
                'clientes' => $resultados
            ];

        } catch (\Exception $e) {
            Log::error('Error buscando en base local', [
                'termino' => $termino,
                'error' => $e->getMessage()
            ]);

            return [
                'encontrados' => 0,
                'clientes' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Limpiar caché RENIEC
     */
    public function limpiarCache($documento = null)
    {
        try {
            if ($documento) {
                Cache::forget("dni_{$documento}");
                Cache::forget("ruc_{$documento}");
                return ['eliminado' => true, 'documento' => $documento];
            }

            // Limpiar todo el caché RENIEC (búsqueda por patrones)
            $keys = Cache::getStore()->getPrefix() . 'dni_*';
            $cacheKeys = \DB::table('cache')
                ->where('key', 'LIKE', '%dni_%')
                ->orWhere('key', 'LIKE', '%ruc_%')
                ->pluck('key');

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            return ['eliminado' => count($cacheKeys), 'tipo' => 'todo'];

        } catch (\Exception $e) {
            Log::error('Error limpiando caché', ['error' => $e->getMessage()]);
            return ['eliminado' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de uso de RENIEC
     */
    public function getEstadisticasUso()
    {
        try {
            $stats = [];
            
            // Contar elementos en caché
            $cacheKeys = \DB::table('cache')
                ->where('key', 'LIKE', '%dni_%')
                ->orWhere('key', 'LIKE', '%ruc_%')
                ->count();
            
            $stats['elementos_cache'] = $cacheKeys;
            
            // Consultas del día actual (desde logs)
            $consultasHoy = \DB::connection('sqlsrv')
                ->table('Auditoria_Sistema')
                ->where('accion', 'LIKE', '%RENIEC%')
                ->whereDate('fecha', '=', date('Y-m-d'))
                ->count();
                
            $stats['consultas_hoy'] = $consultasHoy;
            
            return $stats;

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas', ['error' => $e->getMessage()]);
            return [];
        }
    }
}