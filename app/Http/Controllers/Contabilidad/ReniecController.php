<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReniecController extends Controller
{
    /**
     * CONSULTAR DNI/RENIEC
     * Para validar clientes en tiempo real
     */
    public function consultarDNI(Request $request)
    {
        try {
            $request->validate([
                'dni' => 'required|string|size:8'
            ]);

            $dni = $request->dni;
            
            // Token RENIEC (debería estar en config)
            $token = '83c009f9b7a09201d8a0638a6dfb06b408247b573c5fc378e8b8fd2a524c2e8f';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('https://api.consultasperu.com/api/v1/query', [
                'dni' => $dni
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'nombres' => $data['nombres'] ?? '',
                        'apellido_paterno' => $data['apellido_paterno'] ?? '',
                        'apellido_materno' => $data['apellido_materno'] ?? '',
                        'nombre_completo' => trim(($data['nombres'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                        'direccion' => $data['direccion'] ?? '',
                        'ubigeo' => $data['ubigeo'] ?? '',
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? ''
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo consultar el DNI en RENIEC'
                ], 422);
            }
            
        } catch (\Exception $e) {
            Log::error('Error consultando DNI: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la consulta RENIEC: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CONSULTAR RUC/SUNAT
     * Para validar empresas
     */
    public function consultarRUC(Request $request)
    {
        try {
            $request->validate([
                'ruc' => 'required|string|size:11'
            ]);

            $ruc = $request->ruc;
            
            // Token RENIEC también sirve para RUC
            $token = '83c009f9b7a09201d8a0638a6dfb06b408247b573c5fc378e8b8fd2a524c2e8f';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('https://api.consultasperu.com/api/v1/query', [
                'ruc' => $ruc
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'razon_social' => $data['razon_social'] ?? '',
                        'nombre_comercial' => $data['nombre_comercial'] ?? '',
                        'direccion' => $data['direccion'] ?? '',
                        'estado' => $data['estado'] ?? '',
                        'condicion' => $data['condicion'] ?? '',
                        'ubigeo' => $data['ubigeo'] ?? '',
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? ''
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo consultar el RUC en SUNAT'
                ], 422);
            }
            
        } catch (\Exception $e) {
            Log::error('Error consultando RUC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la consulta SUNAT: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * BUSCAR CLIENTE EXISTENTE
     * Para facturación rápida
     */
    public function buscarCliente(Request $request)
    {
        try {
            $termino = $request->get('termino', '');
            
            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 2 caracteres'
                ]);
            }

            // Buscar en base de datos local
            $clientes = \DB::connection('sqlsrv')
                ->select("
                    SELECT TOP 10
                        Codclie,
                        Razon,
                        Documento,
                        Direccion,
                        Zona,
                        TipoNeg,
                        Telefono1,
                        Celular,
                        Email,
                        Maymin,
                        Limite
                    FROM dbo.Clientes
                    WHERE (Razon LIKE ? OR Documento LIKE ? OR Direccion LIKE ?)
                    AND Activo = 1
                    ORDER BY Razon
                ", ["%{$termino}%", "%{$termino}%", "%{$termino}%"]);

            return response()->json([
                'success' => true,
                'data' => $clientes
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error buscando cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar cliente'
            ], 500);
        }
    }

    /**
     * CREAR NUEVO CLIENTE
     * Desde datos RENIEC
     */
    public function crearCliente(Request $request)
    {
        try {
            $request->validate([
                'tipo_documento' => 'required|in:DNI,RUC',
                'numero_documento' => 'required|string',
                'razon_social' => 'required|string',
                'direccion' => 'nullable|string',
                'zona' => 'nullable|string',
                'telefono' => 'nullable|string',
                'email' => 'nullable|email'
            ]);

            // Verificar que no exista el cliente
            $existe = \DB::connection('sqlsrv')
                ->select("SELECT 1 FROM dbo.Clientes WHERE Documento = ?", [$request->numero_documento]);

            if (!empty($existe)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un cliente con este documento'
                ], 422);
            }

            // Crear cliente en base de datos
            $clienteId = \DB::connection('sqlsrv')
                ->table('Clientes')
                ->insertGetId([
                    'tipoDoc' => $request->tipo_documento === 'DNI' ? 'D' : 'R',
                    'Documento' => $request->numero_documento,
                    'Razon' => $request->razon_social,
                    'Direccion' => $request->direccion,
                    'Zona' => $request->zona ?? '001',
                    'Telefono1' => $request->telefono,
                    'Email' => $request->email,
                    'Fecha' => now(),
                    'Maymin' => 1, // Mayorista por defecto
                    'Activo' => 1
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado correctamente',
                'cliente_id' => $clienteId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creando cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MODAL DE BÚSQUEDA DE CLIENTE
     * Para usar en formularios de facturación
     */
    public function modalBusqueda()
    {
        return view('contabilidad.modals.modal-busqueda-cliente');
    }

    /**
     * CONSULTA PÚBLICA RENIEC
     * Para integraciones sin autenticación
     */
    public function consultaPublica(Request $request)
    {
        try {
            $documento = $request->get('documento');
            $tipo = $request->get('tipo', 'dni');
            
            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento requerido'
                ]);
            }

            $token = config('services.reniec.token', '83c009f9b7a09201d8a0638a6dfb06b408247b573c5fc378e8b8fd2a524c2e8f');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get('https://api.consultasperu.com/api/v1/query', [
                $tipo => $documento
            ]);

            return response()->json($response->json());
            
        } catch (\Exception $e) {
            Log::error('Error consulta pública RENIEC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en consulta pública'
            ], 500);
        }
    }
}
