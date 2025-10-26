<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $buscar = $request->input('buscar', '');
            $vendedor = $request->input('vendedor');
            $zona = $request->input('zona');
            $tipo = $request->input('tipo');
            $activo = $request->input('activo', '1'); // 1=activo, 0=inactivo, ''=todos
            $limite = $request->input('limite', 20);

            $clientes = $this->buscarClientes($buscar, $vendedor, $zona, $tipo, $activo, $limite);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($codigo)
    {
        try {
            // Datos básicos del cliente
            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Datos adicionales
            $datosAdicionales = $this->obtenerDatosAdicionales($codigo);
            
            // Historial de compras
            $historialCompras = $this->obtenerHistorialCompras($codigo);
            
            // Estado financiero
            $estadoFinanciero = $this->obtenerEstadoFinanciero($codigo);

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente,
                    'datos_adicionales' => $datosAdicionales,
                    'historial_compras' => $historialCompras,
                    'estado_financiero' => $estadoFinanciero
                ],
                'message' => 'Cliente obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Documento' => 'nullable|string|max:12',
                'Razon' => 'required|string|max:80',
                'Direccion' => 'nullable|string|max:60',
                'Telefono1' => 'nullable|string|max:10',
                'Telefono2' => 'nullable|string|max:10',
                'Fax' => 'nullable|string|max:15',
                'Celular' => 'nullable|string|max:10',
                'Nextel' => 'nullable|string|max:15',
                'Maymin' => 'boolean',
                'Zona' => 'required|string|max:3',
                'TipoNeg' => 'nullable|integer',
                'TipoClie' => 'nullable|integer',
                'Vendedor' => 'nullable|integer',
                'Email' => 'nullable|email|max:100',
                'Limite' => 'nullable|numeric',
                'Activo' => 'boolean'
            ]);

            // Valores por defecto
            $validatedData['Maymin'] = $validatedData['Maymin'] ?? false;
            $validatedData['Activo'] = $validatedData['Activo'] ?? true;
            $validatedData['Fecha'] = Carbon::now();

            // Validar documento si se proporciona
            if (!empty($validatedData['Documento'])) {
                $documentoExiste = DB::table('Clientes')
                    ->where('Documento', $validatedData['Documento'])
                    ->exists();

                if ($documentoExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El número de documento ya está registrado'
                    ], 422);
                }
            }

            // Insertar cliente
            $codigo = DB::table('Clientes')->insertGetId($validatedData);

            // Registrar en auditoría si existe la tabla
            try {
                DB::table('Auditoria_Sistema')->insert([
                    'usuario' => 'SISTEMA',
                    'accion' => 'CREAR_CLIENTE',
                    'tabla' => 'Clientes',
                    'detalle' => "Cliente creado: {$validatedData['Razon']} (ID: {$codigo})",
                    'fecha' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Silenciar error de auditoría
            }

            return response()->json([
                'success' => true,
                'data' => ['Codclie' => $codigo],
                'message' => 'Cliente creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $codigo)
    {
        try {
            $validatedData = $request->validate([
                'Documento' => 'nullable|string|max:12',
                'Razon' => 'required|string|max:80',
                'Direccion' => 'nullable|string|max:60',
                'Telefono1' => 'nullable|string|max:10',
                'Telefono2' => 'nullable|string|max:10',
                'Fax' => 'nullable|string|max:15',
                'Celular' => 'nullable|string|max:10',
                'Nextel' => 'nullable|string|max:15',
                'Maymin' => 'boolean',
                'Zona' => 'required|string|max:3',
                'TipoNeg' => 'nullable|integer',
                'TipoClie' => 'nullable|integer',
                'Vendedor' => 'nullable|integer',
                'Email' => 'nullable|email|max:100',
                'Limite' => 'nullable|numeric',
                'Activo' => 'boolean'
            ]);

            // Verificar que el cliente existe
            $clienteExistente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$clienteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Validar documento si se proporciona y es diferente al actual
            if (!empty($validatedData['Documento']) && $validatedData['Documento'] !== $clienteExistente->Documento) {
                $documentoExiste = DB::table('Clientes')
                    ->where('Documento', $validatedData['Documento'])
                    ->where('Codclie', '!=', $codigo)
                    ->exists();

                if ($documentoExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El número de documento ya está registrado'
                    ], 422);
                }
            }

            // Actualizar cliente
            $rowsAffected = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->update($validatedData);

            if ($rowsAffected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se realizaron cambios'
                ], 422);
            }

            // Registrar en auditoría si existe la tabla
            try {
                DB::table('Auditoria_Sistema')->insert([
                    'usuario' => 'SISTEMA',
                    'accion' => 'ACTUALIZAR_CLIENTE',
                    'tabla' => 'Clientes',
                    'detalle' => "Cliente actualizado: {$validatedData['Razon']} (ID: {$codigo})",
                    'fecha' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Silenciar error de auditoría
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($codigo)
    {
        try {
            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Verificar si tiene movimientos
            $tieneMovimientos = DB::table('Doccab')
                ->where('CodClie', $codigo)
                ->where('Eliminado', 0)
                ->exists();

            if ($tieneMovimientos) {
                // En lugar de eliminar, desactivar
                DB::table('Clientes')
                    ->where('Codclie', $codigo)
                    ->update(['Activo' => false]);

                $accion = 'DESACTIVAR_CLIENTE';
                $detalle = "Cliente desactivado (tiene movimientos): {$cliente->Razon} (ID: {$codigo})";
            } else {
                // Eliminar físicamente
                DB::table('Clientes')
                    ->where('Codclie', $codigo)
                    ->delete();

                $accion = 'ELIMINAR_CLIENTE';
                $detalle = "Cliente eliminado: {$cliente->Razon} (ID: {$codigo})";
            }

            // Registrar en auditoría si existe la tabla
            try {
                DB::table('Auditoria_Sistema')->insert([
                    'usuario' => 'SISTEMA',
                    'accion' => $accion,
                    'tabla' => 'Clientes',
                    'detalle' => $detalle,
                    'fecha' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Silenciar error de auditoría
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente ' . ($tieneMovimientos ? 'desactivado' : 'eliminado') . ' exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscar(Request $request)
    {
        try {
            $termino = $request->input('termino', '');
            $tipo = $request->input('tipo', 'todo'); // razon, documento, codigo, todo

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 2 caracteres para buscar'
                ], 400);
            }

            $query = DB::table('Clientes')
                ->where('Activo', 1);

            switch ($tipo) {
                case 'razon':
                    $query->where('Razon', 'like', '%' . $termino . '%');
                    break;
                case 'documento':
                    $query->where('Documento', 'like', '%' . $termino . '%');
                    break;
                case 'codigo':
                    $query->where('Codclie', $termino);
                    break;
                default: // todo
                    $query->where(function ($q) use ($termino) {
                        $q->where('Razon', 'like', '%' . $termino . '%')
                          ->orWhere('Documento', 'like', '%' . $termino . '%')
                          ->orWhere('Codclie', $termino);
                    });
            }

            $resultados = $query->select(
                    'Codclie',
                    'Razon',
                    'Documento',
                    'Direccion',
                    'Telefono1',
                    'Celular',
                    'Zona',
                    'Email'
                )
                ->orderBy('Razon')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'message' => 'Búsqueda completada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activar($codigo)
    {
        try {
            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->update(['Activo' => true]);

            // Registrar en auditoría si existe la tabla
            try {
                DB::table('Auditoria_Sistema')->insert([
                    'usuario' => 'SISTEMA',
                    'accion' => 'ACTIVAR_CLIENTE',
                    'tabla' => 'Clientes',
                    'detalle' => "Cliente activado: {$cliente->Razon} (ID: {$codigo})",
                    'fecha' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Silenciar error de auditoría
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente activado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al activar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al activar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desactivar($codigo)
    {
        try {
            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->update(['Activo' => false]);

            // Registrar en auditoría si existe la tabla
            try {
                DB::table('Auditoria_Sistema')->insert([
                    'usuario' => 'SISTEMA',
                    'accion' => 'DESACTIVAR_CLIENTE',
                    'tabla' => 'Clientes',
                    'detalle' => "Cliente desactivado: {$cliente->Razon} (ID: {$codigo})",
                    'fecha' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Silenciar error de auditoría
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al desactivar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buscarClientes($buscar, $vendedor, $zona, $tipo, $activo, $limite)
    {
        $query = DB::table('Clientes as c')
            ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
            ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('c.Razon', 'like', '%' . $buscar . '%')
                  ->orWhere('c.Documento', 'like', '%' . $buscar . '%');
            });
        }

        if ($vendedor) {
            $query->where('c.Vendedor', $vendedor);
        }

        if ($zona) {
            $query->where('c.Zona', $zona);
        }

        if ($tipo) {
            $query->where('c.TipoClie', $tipo);
        }

        if ($activo !== '') {
            $query->where('c.Activo', $activo);
        }

        return $query->select(
                'c.Codclie',
                'c.Razon',
                'c.Documento',
                'c.Direccion',
                'c.Telefono1',
                'c.Celular',
                'c.Zona',
                'z.Descripcion as NombreZona',
                'c.Vendedor',
                'e.Nombre as NombreVendedor',
                'c.Limite',
                'c.Activo',
                'c.Email'
            )
            ->orderBy('c.Razon')
            ->paginate($limite);
    }

    private function obtenerDatosAdicionales($codigo)
    {
        // Datos del vendedor asignado
        $vendedor = DB::table('Empleados')
            ->where('Codemp', function ($query) use ($codigo) {
                $query->select('Vendedor')
                      ->from('Clientes')
                      ->where('Codclie', $codigo);
            })
            ->first();

        // Zona del cliente
        $zona = DB::table('Zonas')
            ->where('Codzona', function ($query) use ($codigo) {
                $query->select('Zona')
                      ->from('Clientes')
                      ->where('Codclie', $codigo);
            })
            ->first();

        return [
            'vendedor_asignado' => $vendedor,
            'zona' => $zona,
            'fecha_registro' => DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->value('Fecha')
        ];
    }

    private function obtenerHistorialCompras($codigo, $limite = 10)
    {
        return DB::table('Doccab as dc')
            ->where('dc.CodClie', $codigo)
            ->where('dc.Eliminado', 0)
            ->select(
                'dc.Numero',
                'dc.Tipo',
                'dc.Fecha',
                'dc.Total'
            )
            ->orderBy('dc.Fecha', 'desc')
            ->limit($limite)
            ->get();
    }

    private function obtenerEstadoFinanciero($codigo)
    {
        // Total de compras históricas
        $totalCompras = DB::table('Doccab')
            ->where('CodClie', $codigo)
            ->where('Eliminado', 0)
            ->sum('Total');

        // Deuda actual
        $deudaActual = DB::table('CtaCliente')
            ->where('CodClie', $codigo)
            ->where('Saldo', '>', 0)
            ->sum('Saldo');

        // Cantidad de compras
        $cantidadCompras = DB::table('Doccab')
            ->where('CodClie', $codigo)
            ->where('Eliminado', 0)
            ->count();

        return [
            'total_compras' => floatval($totalCompras),
            'deuda_actual' => floatval($deudaActual),
            'cantidad_compras' => $cantidadCompras,
            'ticket_promedio' => $cantidadCompras > 0 ? round($totalCompras / $cantidadCompras, 2) : 0
        ];
    }
}