<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Cliente HTTP de Laravel

class SunatService
{
    protected $connection = 'sqlsrv';
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // Cargas tus credenciales del PSE/OSE desde tu .env
        $this->apiUrl = config('services.pse.url');
        $this->apiKey = config('services.pse.token');
    }

    /**
     * Envía el documento al PSE/OSE y devuelve la respuesta.
     */
    public function enviarDocumentoVenta(string $numeroDoc, int $tipoDoc): array
    {
        // 1. Obtenemos todos los datos de la BD
        $doccab = DB::connection($this->connection)->table('Doccab as dc')
                    ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
                    ->where('dc.Numero', $numeroDoc)->where('dc.Tipo', $tipoDoc)
                    ->select('dc.*', 'c.Razon', 'c.Documento as ClienteDoc', 'c.Direccion as ClienteDir')
                    ->first();
        
        $docdet = DB::connection($this->connection)->table('Docdet as dd')
                    ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                    ->where('dd.Numero', $numeroDoc)->where('dd.Tipo', $tipoDoc)
                    ->select('dd.*', 'p.Nombre as ProductoNombre')
                    ->get();
                    
        // 2. Construimos el JSON/XML que pide tu PSE
        //    (Este es un EJEMPLO basado en un PSE genérico)
        $datosParaPse = [
            'tipo_de_comprobante' => $tipoDoc == 1 ? 1 : 2, // 1=Factura, 2=Boleta
            'serie' => substr($numeroDoc, 0, 4),
            'numero' => (int)substr($numeroDoc, 5),
            'fecha_de_emision' => now()->format('Y-m-d'),
            'cliente_tipo_de_documento' => strlen(trim($doccab->ClienteDoc)) == 11 ? 6 : 1, // 6=RUC, 1=DNI
            'cliente_numero_de_documento' => trim($doccab->ClienteDoc),
            'cliente_denominacion' => $doccab->Razon,
            'cliente_direccion' => $doccab->ClienteDir,
            'total_gravada' => $doccab->Subtotal,
            'total_igv' => $doccab->Igv,
            'total' => $doccab->Total,
            'items' => $docdet->map(function ($item) {
                return [
                    'unidad_de_medida' => 'NIU', // Asumir "Unidad"
                    'codigo' => $item->Codpro,
                    'descripcion' => $item->ProductoNombre,
                    'cantidad' => $item->Cantidad,
                    'valor_unitario' => $item->Precio / 1.18, // Precio sin IGV
                    'precio_unitario' => $item->Precio, // Precio con IGV
                    'subtotal' => $item->Subtotal,
                    'tipo_de_igv' => 1, // Gravado
                    'igv' => $item->Subtotal * 0.18,
                    'total' => $item->Subtotal * 1.18
                ];
            })
        ];

        // 3. Enviamos al PSE
        Log::info("Enviando a SUNAT: {$numeroDoc}");
        $response = Http::withToken($this->apiKey)
                        ->post($this->apiUrl . '/api/v1/documents', $datosParaPse);

        if (!$response->successful()) {
            // Si el PSE falla (ej. 500, 401)
            throw new \Exception("Error del PSE: " . $response->body());
        }

        $data = $response->json();

        // 4. Analizamos la respuesta del PSE
        if (isset($data['errors'])) {
            // Error de SUNAT (Ej: "El RUC no existe")
            return [
                'estado_sunat' => 'RECHAZADO',
                'mensaje_sunat' => $data['errors'],
                'hash_cdr' => null,
                'nombre_archivo' => null,
                'qr_data' => null
            ];
        }

        // ¡ÉXITO!
        return [
            'estado_sunat' => 'ACEPTADO',
            'mensaje_sunat' => $data['sunat_description'] ?? 'Aceptado',
            'hash_cdr' => $data['sunat_hash'] ?? '',
            'nombre_archivo' => $data['filename'] ?? '',
            'qr_data' => $data['qr_data'] ?? ''
        ];
    }
}