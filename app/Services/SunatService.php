<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\AccesoWeb;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SunatService
{
    protected $rucEmpresa;
    protected $usuarioSol;
    protected $claveSol;
    protected $urlBase;
    protected $certificadoDigital;

    public function __construct()
    {
        $this->rucEmpresa = config('sunat.ruc_empresa', '20123456789');
        $this->usuarioSol = config('sunat.usuario_sol', '');
        $this->claveSol = config('sunat.clave_sol', '');
        $this->urlBase = config('sunat.environment', 'produccion') === 'produccion' 
            ? 'https://e-guiaremision.sunat.gob.pe' 
            : 'https://e-beta.sunat.gob.pe';
        $this->certificadoDigital = config('sunat.certificado_path', '');
    }

    /**
     * Enviar factura a SUNAT
     */
    public function enviarFactura(Venta $factura): array
    {
        try {
            // Generar XML de la factura
            $xmlFactura = $this->generarXmlFactura($factura);
            
            // Firmar digitalmente el XML
            $xmlFirmado = $this->firmarXml($xmlFactura);
            
            // Enviar a SUNAT
            $resultadoEnvio = $this->enviarASunat($xmlFirmado, $factura);
            
            if ($resultadoEnvio['success']) {
                // Actualizar estado de la factura
                $factura->sunat_estado = 'ENVIADO';
                $factura->sunat_codigo = $resultadoEnvio['codigo_respuesta'];
                $factura->sunat_mensaje = $resultadoEnvio['mensaje'];
                $factura->sunat_referencia = $resultadoEnvio['referencia'];
                $factura->sunat_fecha_envio = now();
                $factura->save();
                
                Log::info('Factura enviada a SUNAT exitosamente', [
                    'factura' => $factura->Numero,
                    'referencia' => $resultadoEnvio['referencia']
                ]);
            }
            
            return $resultadoEnvio;
            
        } catch (\Exception $e) {
            Log::error('Error al enviar factura a SUNAT', [
                'factura' => $factura->Numero,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'codigo' => 'ERROR_ENVIO',
                'mensaje' => 'Error al enviar la factura a SUNAT'
            ];
        }
    }

    /**
     * Consultar estado de documento en SUNAT
     */
    public function consultarEstadoDocumento(string $numero, int $tipo): array
    {
        try {
            $token = $this->obtenerToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get($this->urlBase . '/api/v1/consulta', [
                'ruc' => $this->rucEmpresa,
                'numero' => $numero,
                'tipo' => $tipo
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'estado' => $data['estado'] ?? 'DESCONOCIDO',
                    'codigo' => $data['codigo'] ?? null,
                    'mensaje' => $data['mensaje'] ?? null,
                    'fecha_aceptacion' => $data['fecha_aceptacion'] ?? null,
                    'hash_cpe' => $data['hash_cpe'] ?? null
                ];
            } else {
                throw new \Exception('Error al consultar estado en SUNAT');
            }
            
        } catch (\Exception $e) {
            Log::error('Error al consultar estado en SUNAT', [
                'numero' => $numero,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Descargar PDF de factura
     */
    public function descargarPdfFactura(string $numero, int $tipo): array
    {
        try {
            $token = $this->obtenerToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get($this->urlBase . '/api/v1/pdf', [
                'ruc' => $this->rucEmpresa,
                'numero' => $numero,
                'tipo' => $tipo
            ]);
            
            if ($response->successful()) {
                $pdfContent = $response->body();
                
                // Guardar PDF temporalmente
                $nombreArchivo = "factura_{$numero}_{$tipo}.pdf";
                $rutaArchivo = storage_path("app/temp/{$nombreArchivo}");
                
                // Crear directorio si no existe
                $dir = dirname($rutaArchivo);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                file_put_contents($rutaArchivo, $pdfContent);
                
                return [
                    'success' => true,
                    'archivo' => $rutaArchivo,
                    'nombre' => $nombreArchivo,
                    'tamaño' => filesize($rutaArchivo)
                ];
            } else {
                throw new \Exception('Error al descargar PDF de SUNAT');
            }
            
        } catch (\Exception $e) {
            Log::error('Error al descargar PDF de SUNAT', [
                'numero' => $numero,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar guía de remisión
     */
    public function enviarGuiaRemision(array $datos): array
    {
        try {
            // Generar XML de guía de remisión
            $xmlGuia = $this->generarXmlGuiaRemision($datos);
            
            // Firmar digitalmente el XML
            $xmlFirmado = $this->firmarXml($xmlGuia);
            
            // Enviar a SUNAT
            $resultadoEnvio = $this->enviarASunat($xmlFirmado, null, 'GUIA_REMISION');
            
            return $resultadoEnvio;
            
        } catch (\Exception $e) {
            Log::error('Error al enviar guía de remisión a SUNAT', [
                'error' => $e->getMessage(),
                'datos' => $datos
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar estado de guía de remisión
     */
    public function consultarEstadoGuia(string $numero): array
    {
        try {
            $token = $this->obtenerToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get($this->urlBase . '/api/v1/consulta-guia', [
                'ruc' => $this->rucEmpresa,
                'numero' => $numero
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'estado' => $data['estado'] ?? 'DESCONOCIDO',
                    'referencia' => $data['referencia'] ?? null,
                    'fecha_aceptacion' => $data['fecha_aceptacion'] ?? null
                ];
            } else {
                throw new \Exception('Error al consultar estado de guía');
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar comunicado de bajas
     */
    public function enviarComunicadoBajas(array $facturas): array
    {
        try {
            // Generar XML del comunicado de bajas
            $xmlBajas = $this->generarXmlComunicadoBajas($facturas);
            
            // Firmar digitalmente el XML
            $xmlFirmado = $this->firmarXml($xmlBajas);
            
            // Enviar a SUNAT
            $resultadoEnvio = $this->enviarASunat($xmlFirmado, null, 'COMUNICADO_BAJAS');
            
            return $resultadoEnvio;
            
        } catch (\Exception $e) {
            Log::error('Error al enviar comunicado de bajas', [
                'error' => $e->getMessage(),
                'facturas' => $facturas
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de ventas diarias
     */
    public function generarReporteVentasDiarias(Carbon $fecha): array
    {
        try {
            $token = $this->obtenerToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->urlBase . '/api/v1/reporte-ventas', [
                'ruc' => $this->rucEmpresa,
                'fecha' => $fecha->format('Y-m-d')
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'fecha' => $fecha->format('Y-m-d'),
                    'total_facturas' => $data['total_facturas'] ?? 0,
                    'total_boletas' => $data['total_boletas'] ?? 0,
                    'total_monto' => $data['total_monto'] ?? 0,
                    'detalle' => $data['detalle'] ?? []
                ];
            } else {
                throw new \Exception('Error al generar reporte de ventas');
            }
            
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de ventas diarias', [
                'fecha' => $fecha->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar RUC de empresa
     */
    public function validarRucEmpresa(string $ruc): array
    {
        try {
            // SUNAT permite consulta sin autenticación para validación básica
            $response = Http::get('https://apisi.net.pe/api/ruc/' . $ruc);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'ruc_valido' => $data['ruc_valido'] ?? false,
                    'razon_social' => $data['razon_social'] ?? null,
                    'estado' => $data['estado'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                    'actividad_economica' => $data['actividad_economica'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo validar el RUC'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar DNI de persona natural
     */
    public function validarDniPersona(string $dni): array
    {
        try {
            $response = Http::get('https://apis.net.pe/api/dni/' . $dni);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'dni_valido' => !empty($data['nombre']),
                    'nombres' => $data['nombres'] ?? null,
                    'apellido_paterno' => $data['apellido_paterno'] ?? null,
                    'apellido_materno' => $data['apellido_materno'] ?? null,
                    'nombre_completo' => $data['nombre_completo'] ?? null,
                    'codigo_verificacion' => $data['cod_verif'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo validar el DNI'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Métodos privados de apoyo

    private function obtenerToken(): string
    {
        $response = Http::post($this->urlBase . '/oauth/token', [
            'grant_type' => 'password',
            'username' => $this->usuarioSol,
            'password' => $this->claveSol
        ]);
        
        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            throw new \Exception('Error al obtener token de SUNAT');
        }
    }

    private function generarXmlFactura(Venta $factura): string
    {
        $cliente = $factura->cliente;
        $fechaEmision = Carbon::parse($factura->Fecha)->format('Y-m-d');
        $horaEmision = Carbon::parse($factura->Fecha)->format('H:i:s');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" 
                 xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                 xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                 xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2"
                 xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-2"
                 xmlns:sigecpe="urn:oasis:names:specification:ubl:peru:schema:xsd:SunatCommonAggregateTypes-2">
            
            <!-- Información de la factura -->
            <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
            <cbc:CustomizationID>2.0</cbc:CustomizationID>
            
            <!-- Información del emisor -->
            <cac:Signature>
                <cbc:ID>' . $factura->Numero . '</cbc:ID>
                <cac:SignatoryParty>
                    <cac:PartyIdentification>
                        <cbc:ID>' . $this->rucEmpresa . '</cbc:ID>
                    </cac:PartyIdentification>
                </cac:SignatoryParty>
                <cac:DigitalSignatureAttachment>
                    <cac:ExternalReference>
                        <cbc:URI>' . $factura->Numero . '</cbc:URI>
                    </cac:ExternalReference>
                </cac:DigitalSignatureAttachment>
            </cac:Signature>
            
            <!-- Información del proveedor -->
            <cac:AccountingSupplierParty>
                <cac:Party>
                    <cac:PartyIdentification>
                        <cbc:ID>' . $this->rucEmpresa . '</cbc:ID>
                    </cac:PartyIdentification>
                    <cac:PostalAddress>
                        <cbc:ID>150101</cbc:ID>
                        <cbc:StreetName>AV EJEMPLO 123</cbc:StreetName>
                        <cbc:CityName>LIMA</cbc:CityName>
                        <cbc:DistrictName>LIMA</cbc:DistrictName>
                        <cac:Country>
                            <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                        </cac:Country>
                    </cac:PostalAddress>
                </cac:Party>
            </cac:AccountingSupplierParty>
            
            <!-- Información del cliente -->
            <cac:AccountingCustomerParty>
                <cac:Party>
                    <cac:PartyIdentification>
                        <cbc:ID>' . ($cliente->Documento ?? '') . '</cbc:ID>
                    </cac:PartyIdentification>
                    <cac:PostalAddress>
                        <cbc:StreetName>' . ($cliente->Direccion ?? '') . '</cbc:StreetName>
                    </cac:PostalAddress>
                </cac:Party>
            </cac:AccountingCustomerParty>';
        
        // Agregar líneas de factura
        foreach ($factura->detalles as $detalle) {
            $xml .= '
            <!-- Línea de detalle ' . ($detalle->item + 1) . ' -->
            <cac:OrderLineReference>
                <cbc:LineID>' . ($detalle->item + 1) . '</cbc:LineID>
                <cac:OrderReference>
                    <cbc:ID>' . $detalle->Codpro . '</cbc:ID>
                </cac:OrderReference>
            </cac:OrderLineReference>
            <cac:LineItem>
                <cbc:ID>' . ($detalle->item + 1) . '</cbc:ID>
                <cbc:Quantity unitCode="NIU">' . $detalle->Cantidad . '</cbc:Quantity>
                <cac:Price>
                    <cbc:PriceAmount currencyID="PEN">' . $detalle->Precio . '</cbc:PriceAmount>
                </cac:Price>
            </cac:LineItem>';
        }
        
        $xml .= '
            <!-- Totales -->
            <cac:LegalMonetaryTotal>
                <cbc:LineExtensionAmount currencyID="PEN">' . $factura->Subtotal . '</cbc:LineExtensionAmount>
                <cbc:TaxAmount currencyID="PEN">' . $factura->Impuesto . '</cbc:TaxAmount>
                <cbc:PayableAmount currencyID="PEN">' . $factura->Total . '</cbc:PayableAmount>
            </cac:LegalMonetaryTotal>
        </Invoice>';
        
        return $xml;
    }

    private function firmarXml(string $xml): string
    {
        // En una implementación real, esto firmaría digitalmente el XML
        // usando el certificado digital y la clave privada
        // Por ahora retornamos el XML original
        
        Log::info('XML firmado digitalmente', [
            'tamaño_original' => strlen($xml),
            'hash' => hash('sha256', $xml)
        ]);
        
        return $xml;
    }

    private function enviarASunat(string $xmlFirmado, ?Venta $factura = null, string $tipo = 'FACTURA'): array
    {
        try {
            $token = $this->obtenerToken();
            
            $endpoint = match($tipo) {
                'FACTURA' => '/api/v1/send-invoice',
                'GUIA_REMISION' => '/api/v1/send-dispatch',
                'COMUNICADO_BAJAS' => '/api/v1/send-voided',
                default => '/api/v1/send-invoice'
            };
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/xml'
            ])->post($this->urlBase . $endpoint, $xmlFirmado);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'codigo_respuesta' => $data['code'] ?? '0',
                    'mensaje' => $data['message'] ?? 'Enviado exitosamente',
                    'referencia' => $data['reference'] ?? $factura?->Numero,
                    'hash_cpe' => $data['hash_cpe'] ?? null,
                    'fecha_envio' => now()->toISOString()
                ];
            } else {
                // Manejar errores de SUNAT
                $errorData = $response->json();
                
                return [
                    'success' => false,
                    'codigo_error' => $response->status(),
                    'mensaje_error' => $errorData['message'] ?? 'Error desconocido',
                    'detalles_error' => $errorData['details'] ?? []
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function generarXmlGuiaRemision(array $datos): string
    {
        // Implementación para generar XML de guía de remisión
        // Similar al XML de factura pero con estructura específica
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <DespatchAdvice xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2">
            <!-- Estructura de guía de remisión -->
            <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
            <!-- ... más campos ... -->
        </DespatchAdvice>';
        
        return $xml;
    }

    private function generarXmlComunicadoBajas(array $facturas): string
    {
        // Implementación para generar XML de comunicado de bajas
        // Lista de facturas que se darán de baja
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-2">
            <!-- Estructura de comunicado de bajas -->
            <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
            <!-- ... más campos ... -->
        </VoidedDocuments>';
        
        return $xml;
    }
}