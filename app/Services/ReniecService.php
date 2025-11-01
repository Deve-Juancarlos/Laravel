<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; // <-- ¡IMPORTANTE!

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
     * Consultar DNI con caché
     * ¡CORREGIDO! Usa el formato de 'proxy.php'
     */
    public function consultarDNI($numeroDNI)
    {
        try {
            $cacheKey = "dni_{$numeroDNI}";
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // CORRECCIÓN: Enviamos el payload como tu proxy.php
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($this->baseUrl, [
                'token' => $this->token,
                'type_document' => 'dni',
                'document_number' => $numeroDNI
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Tu proxy.php devuelve {success: true, data: {...}}
                $apiData = $data['data'] ?? $data;
                if(is_array($apiData)) {
                    // La API (según tu proxy) devuelve 'number' y 'full_name'
                    $apiData['numero_documento'] = $apiData['number'] ?? $numeroDNI;
                    $apiData['razon_social'] = $apiData['full_name'] ?? 'N/A'; // Alias
                    $apiData['address'] = $apiData['address'] ?? 'N/A';
                }
                
                Cache::put($cacheKey, $apiData, 900); // 15 minutos
                Log::info('Consulta DNI exitosa', ['dni' => $numeroDNI]);
                return $apiData;
            }

            Log::warning('Consulta DNI fallida', [
                'dni' => $numeroDNI, 
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error en consulta DNI', ['dni' => $numeroDNI, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Consultar RUC con caché
     * ¡CORREGIDO! Usa el formato de 'proxy.php'
     */
    public function consultarRUC($numeroRUC)
    {
        try {
            $cacheKey = "ruc_{$numeroRUC}";
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // CORRECCIÓN: Enviamos el payload como tu proxy.php
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($this->baseUrl, [
                'token' => $this->token,
                'type_document' => 'ruc',
                'document_number' => $numeroRUC
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $apiData = $data['data'] ?? $data;
                if (is_array($apiData)) {
                    // La API (según tu proxy) devuelve 'name' y 'number'
                    $apiData['razon_social'] = $apiData['name'] ?? 'N/A'; 
                    $apiData['numero_documento'] = $apiData['number'] ?? $numeroRUC;
                    $apiData['address'] = $apiData['address'] ?? 'N/A';
                }
                
                Cache::put($cacheKey, $apiData, 1800); // 30 minutos
                Log::info('Consulta RUC exitosa', ['ruc' => $numeroRUC, 'empresa' => $apiData['name'] ?? 'N/A']);
                return $apiData;
            }

            Log::warning('Consulta RUC fallida', [
                'ruc' => $numeroRUC, 
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error en consulta RUC', ['ruc' => $numeroRUC, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validar DNI
     */
    public function validarDNI($numeroDNI)
    {
        if (!preg_match('/^\d{8}$/', $numeroDNI)) {
            return ['valido' => false, 'error' => 'El DNI debe tener exactamente 8 dígitos'];
        }
        return ['valido' => true];
    }

    /**
     * Validar RUC
     */
    public function validarRUC($numeroRUC)
    {
        if (!preg_match('/^\d{11}$/', $numeroRUC)) {
            return ['valido' => false, 'error' => 'El RUC debe tener exactamente 11 dígitos'];
        }
        $validStarts = ['10', '15', '16', '17', '20'];
        if (!in_array(substr($numeroRUC, 0, 2), $validStarts)) {
            return ['valido' => false, 'error' => 'RUC inválido'];
        }
        return ['valido' => true];
    }

    /**
     * Buscar clientes coincidentes en la base de datos local
     */
    public function buscarEnBaseLocal($termino, $tipo = null)
    {
        try {
            $query = DB::connection('sqlsrv') 
                ->table('Clientes')
                ->select([
                    'Codclie',
                    'Razon',
                    'Documento as documento', // Tu columna correcta
                    'Direccion',
                    'Telefono1',
                    'Email',
                    'Activo'
                ])
                ->where(function($q) use ($termino) {
                    $q->where('Razon', 'LIKE', "%{$termino}%")
                      ->orWhere('Documento', 'LIKE', "%{$termino}%"); // Tu columna correcta
                })
                ->where('Activo', 1)
                ->orderBy('Razon')
                ->limit(20);

            $resultados = $query->get();

            return [
                'encontrados' => $resultados->count(),
                'clientes' => $resultados
            ];

        } catch (\Exception $e) {
            Log::error('Error buscando en base local (ReniecService)', ['termino' => $termino, 'error' => $e->getMessage()]);
            return ['encontrados' => 0, 'clientes' => collect(), 'error' => $e->getMessage()];
        }
    }
    
    public function limpiarCache($documento = null)
    {
        try {
            if ($documento) {
                Cache::forget("dni_{$documento}");
                Cache::forget("ruc_{$documento}");
                return ['eliminado' => true, 'documento' => $documento];
            }
            $cacheKeys = DB::table('cache')
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

    public function getEstadisticasUso()
    {
        try {
            $stats = [];
            $stats['elementos_cache'] = DB::table('cache')
                ->where('key', 'LIKE', '%dni_%')
                ->orWhere('key', 'LIKE', '%ruc_%')
                ->count();
            
            $stats['consultas_hoy'] = DB::connection('sqlsrv')
                ->table('Auditoria_Sistema')
                ->where('accion', 'LIKE', '%RENIEC%')
                ->whereDate('fecha', '=', date('Y-m-d'))
                ->count();
                
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas', ['error' => $e->getMessage()]);
            return [];
        }
    }
}