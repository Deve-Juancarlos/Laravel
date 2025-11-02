<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReniecService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = '83c009f9b7a09201d8a0638a6dfb06b408247b573c5fc378e8b8fd2a524c2e8f';
        $this->baseUrl = 'https://api.consultasperu.com/api/v1/query';
    }

    public function consultarDNI($numeroDNI)
    {
        try {
            $cacheKey = "dni_{$numeroDNI}";
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $data = [
                'type_document' => 'dni',
                'document_number' => $numeroDNI,
                'token' => $this->token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('Error de conexión: ' . $error);
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('Respuesta API DNI [cURL]', [
                'dni' => $numeroDNI,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            $responseData = json_decode($response, true);
            
            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
                $apiData = $responseData['data'];
                $apiData['numero_documento'] = $apiData['number'] ?? $numeroDNI;
                $apiData['razon_social'] = $apiData['full_name'] ?? 'N/A'; 
                $apiData['address'] = $apiData['address'] ?? 'N/A';
                
                Cache::put($cacheKey, $apiData, 900);
                Log::info('✅ Consulta DNI exitosa [cURL]', ['dni' => $numeroDNI]);
                return $apiData;
            }

            Log::warning('❌ Consulta DNI fallida [cURL]', [
                'dni' => $numeroDNI, 
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('💥 Error en consulta DNI [cURL]', [
                'dni' => $numeroDNI, 
                'error' => $e->getMessage()
            ]);
            throw new \Exception('No se pudo conectar con el servicio. Por favor, intente nuevamente.');
        }
    }

    public function consultarRUC($numeroRUC)
    {
        try {
            $cacheKey = "ruc_{$numeroRUC}";
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $data = [
                'type_document' => 'ruc',
                'document_number' => $numeroRUC,
                'token' => $this->token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('Error de conexión: ' . $error);
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('Respuesta API RUC [cURL]', [
                'ruc' => $numeroRUC,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            $responseData = json_decode($response, true);
            
            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
                $apiData = $responseData['data'];
                
                if (is_array($apiData)) {
                    $apiData['razon_social'] = $apiData['name'] ?? 'N/A'; 
                    $apiData['numero_documento'] = $apiData['number'] ?? $numeroRUC;
                    $apiData['address'] = $apiData['address'] ?? 'N/A';
                }
                
                Cache::put($cacheKey, $apiData, 1800);
                Log::info('✅ Consulta RUC exitosa [cURL]', ['ruc' => $numeroRUC]);
                return $apiData;
            }

            Log::warning('❌ Consulta RUC fallida [cURL]', [
                'ruc' => $numeroRUC, 
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('💥 Error en consulta RUC [cURL]', [
                'ruc' => $numeroRUC, 
                'error' => $e->getMessage()
            ]);
            throw new \Exception('No se pudo conectar con el servicio. Por favor, intente nuevamente.');
        }
    }

    public function validarDNI($numeroDNI)
    {
        if (!preg_match('/^\d{8}$/', $numeroDNI)) {
            return ['valido' => false, 'error' => 'El DNI debe tener exactamente 8 dígitos'];
        }
        return ['valido' => true];
    }

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

    public function buscarEnBaseLocal($termino, $tipo = null)
    {
        try {
            $query = DB::connection('sqlsrv') 
                ->table('Clientes')
                ->select(['Codclie', 'Razon', 'Documento as documento', 'Direccion', 'Telefono1', 'Email', 'Activo'])
                ->where(function($q) use ($termino) {
                    $q->where('Razon', 'LIKE', "%{$termino}%")->orWhere('Documento', 'LIKE', "%{$termino}%");
                })
                ->where('Activo', 1)
                ->orderBy('Razon')
                ->limit(20);

            $resultados = $query->get();
            return ['encontrados' => $resultados->count(), 'clientes' => $resultados];

        } catch (\Exception $e) {
            Log::error('Error buscando en base local', ['termino' => $termino, 'error' => $e->getMessage()]);
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
            $cacheKeys = DB::table('cache')->where('key', 'LIKE', '%dni_%')->orWhere('key', 'LIKE', '%ruc_%')->pluck('key');
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
            $stats['elementos_cache'] = DB::table('cache')->where('key', 'LIKE', '%dni_%')->orWhere('key', 'LIKE', '%ruc_%')->count();
            $stats['consultas_hoy'] = DB::connection('sqlsrv')->table('Auditoria_Sistema')->where('accion', 'LIKE', '%RENIEC%')->whereDate('fecha', '=', date('Y-m-d'))->count();
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas', ['error' => $e->getMessage()]);
            return [];
        }
    }
}