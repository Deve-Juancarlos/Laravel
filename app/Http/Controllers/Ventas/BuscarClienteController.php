<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BuscarClienteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $vendedor = $request->input('vendedor');
            $zona = $request->input('zona');
            $tipoCliente = $request->input('tipo_cliente');
            $soloActivos = $request->boolean('solo_activos', true);

            $clientes = $this->buscarClientes($query, $vendedor, $zona, $tipoCliente, $soloActivos);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes encontrados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function busquedaAvanzada(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'query' => 'nullable|string|max:100',
                'vendedor' => 'nullable|integer',
                'zona' => 'nullable|string|max:3',
                'tipo_cliente' => 'nullable|integer',
                'fecha_desde' => 'nullable|date',
                'fecha_hasta' => 'nullable|date',
                'solo_activos' => 'boolean',
                'con_deuda' => 'boolean',
                'limite_credito' => 'nullable|numeric',
                'order_by' => 'nullable|in:razon,documento,zona,fecha,total_compras',
                'order_direction' => 'nullable|in:asc,desc',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $clientes = $this->busquedaAvanzadaClientes($validatedData);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Búsqueda avanzada completada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en búsqueda avanzada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mostrarCliente($codigo)
    {
        try {
            // Obtener datos básicos del cliente
            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Obtener información financiera
            $informacionFinanciera = $this->obtenerInformacionFinanciera($codigo);
            
            // Obtener historial de compras
            $historialCompras = $this->obtenerHistorialCompras($codigo);
            
            // Obtener deuda pendiente
            $deudaPendiente = $this->obtenerDeudaPendiente($codigo);
            
            // Obtener límites de crédito
            $limiteCredito = $this->obtenerLimiteCredito($codigo);
            
            // Obtener último movimiento
            $ultimoMovimiento = $this->obtenerUltimoMovimiento($codigo);

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente,
                    'informacion_financiera' => $informacionFinanciera,
                    'historial_compras' => $historialCompras,
                    'deuda_pendiente' => $deudaPendiente,
                    'limite_credito' => $limiteCredito,
                    'ultimo_movimiento' => $ultimoMovimiento
                ],
                'message' => 'Información del cliente obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autocompletar(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $limite = $request->input('limit', 10);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Ingrese al menos 2 caracteres para autocompletar'
                ]);
            }

            $clientes = DB::table('Clientes')
                ->where('Activo', 1)
                ->where(function ($q) use ($query) {
                    $q->where('Razon', 'like', '%' . $query . '%')
                      ->orWhere('Documento', 'like', '%' . $query . '%');
                })
                ->select(
                    'Codclie',
                    'Razon',
                    'Documento',
                    'Zona',
                    'Telefono1',
                    'Celular'
                )
                ->orderBy('Razon')
                ->limit($limite)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Autocompletado completado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en autocompletado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validarCliente(Request $request)
    {
        try {
            $codigo = $request->input('codigo');
            
            if (!$codigo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de cliente es requerido'
                ], 400);
            }

            $cliente = DB::table('Clientes')
                ->where('Codclie', $codigo)
                ->where('Activo', 1)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Cliente no encontrado o inactivo'
                ]);
            }

            // Validar límite de crédito
            $deudaActual = $this->obtenerDeudaPendiente($codigo);
            $limiteCredito = $cliente->Limite ?? 0;
            
            $puedeComprar = true;
            $mensajeValidacion = '';

            if ($limiteCredito > 0) {
                if ($deudaActual >= $limiteCredito) {
                    $puedeComprar = false;
                    $mensajeValidacion = 'Límite de crédito alcanzado. Deuda actual: S/ ' . number_format($deudaActual, 2);
                }
            }

            return response()->json([
                'success' => true,
                'valid' => true,
                'cliente' => [
                    'codigo' => $cliente->Codclie,
                    'razon' => $cliente->Razon,
                    'documento' => $cliente->Documento,
                    'zona' => $cliente->Zona,
                    'limite_credito' => floatval($limiteCredito),
                    'deuda_actual' => $deudaActual
                ],
                'validacion' => [
                    'puede_comprar' => $puedeComprar,
                    'mensaje' => $mensajeValidacion
                ],
                'message' => 'Cliente validado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buscarClientes($query, $vendedor = null, $zona = null, $tipoCliente = null, $soloActivos = true)
    {
        $q = DB::table('Clientes as c')
            ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
            ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp')
            ->select(
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
                'c.Fecha'
            );

        if ($soloActivos) {
            $q->where('c.Activo', 1);
        }

        if ($query) {
            $q->where(function ($subquery) use ($query) {
                $subquery->where('c.Razon', 'like', '%' . $query . '%')
                    ->orWhere('c.Documento', 'like', '%' . $query . '%')
                    ->orWhere('c.Codclie', $query);
            });
        }

        if ($vendedor) {
            $q->where('c.Vendedor', $vendedor);
        }

        if ($zona) {
            $q->where('c.Zona', $zona);
        }

        if ($tipoCliente) {
            $q->where('c.TipoClie', $tipoCliente);
        }

        return $q->orderBy('c.Razon')
                ->paginate(20);
    }

    private function busquedaAvanzadaClientes($filtros)
    {
        $q = DB::table('Clientes as c')
            ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
            ->leftJoin('Empleados as e', 'c.Vendedor', '=', 'e.Codemp')
            ->leftJoin('CtaCliente as cc', 'c.Codclie', '=', 'cc.CodClie')
            ->select(
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
                'c.Fecha',
                DB::raw('SUM(cc.Saldo) as total_deuda'),
                DB::raw('COUNT(cc.Documento) as documentos_pendientes')
            )
            ->where('c.Activo', 1);

        if ($filtros['query'] ?? false) {
            $q->where(function ($subquery) use ($filtros) {
                $subquery->where('c.Razon', 'like', '%' . $filtros['query'] . '%')
                    ->orWhere('c.Documento', 'like', '%' . $filtros['query'] . '%');
            });
        }

        if ($filtros['vendedor'] ?? false) {
            $q->where('c.Vendedor', $filtros['vendedor']);
        }

        if ($filtros['zona'] ?? false) {
            $q->where('c.Zona', $filtros['zona']);
        }

        if ($filtros['tipo_cliente'] ?? false) {
            $q->where('c.TipoClie', $filtros['tipo_cliente']);
        }

        if ($filtros['con_deuda'] ?? false) {
            $q->where('cc.Saldo', '>', 0);
        }

        if ($filtros['limite_credito'] ?? false) {
            $q->where('c.Limite', '>=', $filtros['limite_credito']);
        }

        if ($filtros['fecha_desde'] ?? false) {
            $q->where('c.Fecha', '>=', $filtros['fecha_desde']);
        }

        if ($filtros['fecha_hasta'] ?? false) {
            $q->where('c.Fecha', '<=', $filtros['fecha_hasta']);
        }

        $orderBy = $filtros['order_by'] ?? 'razon';
        $orderDirection = $filtros['order_direction'] ?? 'asc';
        
        $orderMap = [
            'razon' => 'c.Razon',
            'documento' => 'c.Documento',
            'zona' => 'c.Zona',
            'fecha' => 'c.Fecha',
            'total_compras' => 'total_deuda'
        ];

        $orderColumn = $orderMap[$orderBy] ?? 'c.Razon';

        $q->groupBy(
            'c.Codclie', 'c.Razon', 'c.Documento', 'c.Direccion', 
            'c.Telefono1', 'c.Celular', 'c.Zona', 'z.Descripcion',
            'c.Vendedor', 'e.Nombre', 'c.Limite', 'c.Activo', 'c.Fecha'
        );

        $q->orderBy($orderColumn, $orderDirection);

        $limit = $filtros['limit'] ?? 50;
        $q->limit($limit);

        return $q->get();
    }

    private function obtenerInformacionFinanciera($codigo)
    {
        $ventasTotales = DB::table('Doccab')
            ->where('CodClie', $codigo)
            ->where('Eliminado', 0)
            ->select(
                DB::raw('COUNT(*) as total_compras'),
                DB::raw('SUM(Total) as total_gastado'),
                DB::raw('AVG(Total) as ticket_promedio')
            )
            ->first();

        $deudaTotal = DB::table('CtaCliente')
            ->where('CodClie', $codigo)
            ->where('Saldo', '>', 0)
            ->select(DB::raw('SUM(Saldo) as deuda_pendiente'))
            ->first();

        return [
            'ventas_totales' => [
                'compras' => intval($ventasTotales->total_compras ?? 0),
                'total_gastado' => floatval($ventasTotales->total_gastado ?? 0),
                'ticket_promedio' => floatval($ventasTotales->ticket_promedio ?? 0)
            ],
            'deuda_total' => floatval($deudaTotal->deuda_pendiente ?? 0)
        ];
    }

    private function obtenerHistorialCompras($codigo, $limite = 10)
    {
        return DB::table('Doccab as dc')
            ->leftJoin('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
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

    private function obtenerDeudaPendiente($codigo)
    {
        $deuda = DB::table('CtaCliente')
            ->where('CodClie', $codigo)
            ->where('Saldo', '>', 0)
            ->select(
                DB::raw('SUM(Saldo) as total_deuda'),
                DB::raw('COUNT(*) as documentos_pendientes'),
                DB::raw('MIN(FechaV) as fecha_vencimiento_mas_cercana')
            )
            ->first();

        return floatval($deuda->total_deuda ?? 0);
    }

    private function obtenerLimiteCredito($codigo)
    {
        $cliente = DB::table('Clientes')
            ->where('Codclie', $codigo)
            ->select('Limite')
            ->first();

        return [
            'limite' => floatval($cliente->Limite ?? 0),
            'usado' => $this->obtenerDeudaPendiente($codigo),
            'disponible' => max(0, floatval($cliente->Limite ?? 0) - $this->obtenerDeudaPendiente($codigo))
        ];
    }

    private function obtenerUltimoMovimiento($codigo)
    {
        $ultimo = DB::table('Doccab')
            ->where('CodClie', $codigo)
            ->where('Eliminado', 0)
            ->select('Numero', 'Tipo', 'Fecha', 'Total')
            ->orderBy('Fecha', 'desc')
            ->first();

        return $ultimo;
    }
}