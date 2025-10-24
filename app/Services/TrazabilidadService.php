<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrazabilidadService
{
    /**
     * Registrar medicamento controlado
     */
    public function registrarMedicamentoControlado(array $datos): array
    {
        try {
            DB::beginTransaction();
            
            // Validar que sea medicamento controlado
            $producto = $datos['producto'];
            if (!$producto->Controlado) {
                throw new \Exception('El producto no es un medicamento controlado');
            }
            
            // Generar número de trazabilidad
            $numeroTrazabilidad = $this->generarNumeroTrazabilidad();
            
            // Crear registro de trazabilidad
            $registroTrazabilidad = [
                'numero_trazabilidad' => $numeroTrazabilidad,
                'codigo_producto' => $producto->CodPro,
                'nombre_producto' => $producto->Nombre,
                'lote' => $datos['lote'],
                'cantidad' => $datos['cantidad'],
                'fecha_movimiento' => $datos['fecha'],
                'tipo_movimiento' => 'SALIDA',
                'cliente_documento' => $datos['cliente']['documento'] ?? null,
                'cliente_nombre' => $datos['cliente']['nombre'] ?? null,
                'documento_venta' => $datos['documento'],
                'usuario_responsable' => $datos['usuario'],
                'observaciones' => "Salida controlada - Factura {$datos['documento']}",
                'fecha_registro' => now(),
                'estado' => 'REGISTRADO'
            ];
            
            // Guardar registro (en implementación real sería en tabla específica)
            Log::info('Medicamento controlado registrado', $registroTrazabilidad);
            
            // Validar límites de venta si aplica
            $this->validarLimitesVenta($producto, $datos['cantidad'], $datos['cliente']);
            
            // Registrar en historial de trazabilidad
            $this->registrarHistorialTrazabilidad($registroTrazabilidad);
            
            // Generar reporte de seguimiento si es necesario
            if ($datos['generar_reporte'] ?? false) {
                $reporte = $this->generarReporteTrazabilidad($numeroTrazabilidad);
                $registroTrazabilidad['reporte_generado'] = $reporte;
            }
            
            DB::commit();
            
            Log::info('Medicamento controlado registrado exitosamente', [
                'numero_trazabilidad' => $numeroTrazabilidad,
                'producto' => $producto->CodPro,
                'cantidad' => $datos['cantidad']
            ]);
            
            return [
                'success' => true,
                'numero_trazabilidad' => $numeroTrazabilidad,
                'registro' => $registroTrazabilidad,
                'message' => 'Medicamento controlado registrado en trazabilidad'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar medicamento controlado', [
                'producto' => $datos['producto']->CodPro ?? null,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al registrar medicamento controlado'
            ];
        }
    }

    /**
     * Registrar salida de medicamentos controlados
     */
    public function registrarSalidaMedicamentos(Venta $factura): array
    {
        try {
            $medicamentosControlados = [];
            $errores = [];
            
            foreach ($factura->detalles as $detalle) {
                $producto = $detalle->producto;
                
                if ($producto->Controlado) {
                    $resultado = $this->registrarMedicamentoControlado([
                        'producto' => $producto,
                        'cantidad' => $detalle->Cantidad,
                        'lote' => $detalle->Lote,
                        'cliente' => [
                            'documento' => $factura->cliente->Documento,
                            'nombre' => $factura->cliente->Razon
                        ],
                        'documento' => $factura->Numero,
                        'fecha' => $factura->Fecha,
                        'usuario' => auth()->user()->idusuario ?? null,
                        'generar_reporte' => true
                    ]);
                    
                    if ($resultado['success']) {
                        $medicamentosControlados[] = $resultado;
                    } else {
                        $errores[] = [
                            'producto' => $producto->CodPro,
                            'error' => $resultado['error']
                        ];
                    }
                }
            }
            
            return [
                'success' => empty($errores),
                'medicamentos_registrados' => $medicamentosControlados,
                'errores' => $errores,
                'total_registrados' => count($medicamentosControlados),
                'total_errores' => count($errores)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al registrar salida de medicamentos controlados', [
                'factura' => $factura->Numero,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar trazabilidad por producto y lote
     */
    public function consultarTrazabilidad(string $codigoProducto, string $lote = null): array
    {
        try {
            $query = DB::table('trazabilidad_medicamentos')
                ->where('codigo_producto', $codigoProducto);
            
            if ($lote) {
                $query->where('lote', $lote);
            }
            
            $registros = $query->orderBy('fecha_movimiento', 'desc')->get();
            
            $trazabilidad = [
                'producto' => $codigoProducto,
                'lote' => $lote,
                'total_registros' => $registros->count(),
                'movimientos' => [],
                'resumen' => [
                    'primer_ingreso' => null,
                    'ultima_salida' => null,
                    'stock_actual' => 0,
                    'ventas_totales' => 0
                ]
            ];
            
            $stockActual = 0;
            $ventasTotales = 0;
            $primerIngreso = null;
            $ultimaSalida = null;
            
            foreach ($registros as $registro) {
                $movimiento = [
                    'numero_trazabilidad' => $registro->numero_trazabilidad,
                    'fecha' => $registro->fecha_movimiento,
                    'tipo' => $registro->tipo_movimiento,
                    'cantidad' => $registro->cantidad,
                    'cliente' => $registro->cliente_nombre,
                    'documento' => $registro->documento_venta,
                    'observaciones' => $registro->observaciones
                ];
                
                $trazabilidad['movimientos'][] = $movimiento;
                
                // Calcular stock
                if ($registro->tipo_movimiento === 'ENTRADA') {
                    $stockActual += $registro->cantidad;
                    if (!$primerIngreso) {
                        $primerIngreso = $registro->fecha_movimiento;
                    }
                } else if ($registro->tipo_movimiento === 'SALIDA') {
                    $stockActual -= $registro->cantidad;
                    $ventasTotales += $registro->cantidad;
                    $ultimaSalida = $registro->fecha_movimiento;
                }
            }
            
            $trazabilidad['resumen'] = [
                'primer_ingreso' => $primerIngreso,
                'ultima_salida' => $ultimaSalida,
                'stock_actual' => $stockActual,
                'ventas_totales' => $ventasTotales
            ];
            
            return [
                'success' => true,
                'trazabilidad' => $trazabilidad
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al consultar trazabilidad', [
                'codigo_producto' => $codigoProducto,
                'lote' => $lote,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de trazabilidad por período
     */
    public function generarReporteTrazabilidadPorPeriodo(Carbon $fechaInicio, Carbon $fechaFin, array $filtros = []): array
    {
        try {
            $query = DB::table('trazabilidad_medicamentos')
                ->whereBetween('fecha_movimiento', [$fechaInicio, $fechaFin]);
            
            // Aplicar filtros
            if (!empty($filtros['producto'])) {
                $query->where('codigo_producto', $filtros['producto']);
            }
            
            if (!empty($filtros['tipo_movimiento'])) {
                $query->where('tipo_movimiento', $filtros['tipo_movimiento']);
            }
            
            if (!empty($filtros['lote'])) {
                $query->where('lote', $filtros['lote']);
            }
            
            $registros = $query->orderBy('fecha_movimiento')->get();
            
            $reporte = [
                'periodo' => [
                    'inicio' => $fechaInicio->format('Y-m-d'),
                    'fin' => $fechaFin->format('Y-m-d')
                ],
                'total_movimientos' => $registros->count(),
                'resumen_por_producto' => [],
                'resumen_por_lote' => [],
                'resumen_por_tipo' => [],
                'movimientos_detallados' => [],
                'estadisticas' => [
                    'total_entradas' => 0,
                    'total_salidas' => 0,
                    'productos_diferentes' => 0,
                    'lotes_diferentes' => 0
                ]
            ];
            
            // Agrupar por producto
            $porProducto = $registros->groupBy('codigo_producto');
            foreach ($porProducto as $codigo => $movimientos) {
                $totalEntradas = $movimientos->where('tipo_movimiento', 'ENTRADA')->sum('cantidad');
                $totalSalidas = $movimientos->where('tipo_movimiento', 'SALIDA')->sum('cantidad');
                
                $reporte['resumen_por_producto'][] = [
                    'codigo' => $codigo,
                    'nombre' => $movimientos->first()->nombre_producto,
                    'entradas' => $totalEntradas,
                    'salidas' => $totalSalidas,
                    'saldo_neto' => $totalEntradas - $totalSalidas,
                    'movimientos' => $movimientos->count()
                ];
                
                $reporte['estadisticas']['productos_diferentes']++;
            }
            
            // Agrupar por lote
            $porLote = $registros->groupBy('lote');
            foreach ($porLote as $lote => $movimientos) {
                $totalEntradas = $movimientos->where('tipo_movimiento', 'ENTRADA')->sum('cantidad');
                $totalSalidas = $movimientos->where('tipo_movimiento', 'SALIDA')->sum('cantidad');
                
                $reporte['resumen_por_lote'][] = [
                    'lote' => $lote,
                    'producto' => $movimientos->first()->codigo_producto,
                    'entradas' => $totalEntradas,
                    'salidas' => $totalSalidas,
                    'saldo_neto' => $totalEntradas - $totalSalidas,
                    'movimientos' => $movimientos->count()
                ];
                
                $reporte['estadisticas']['lotes_diferentes']++;
            }
            
            // Agrupar por tipo de movimiento
            $porTipo = $registros->groupBy('tipo_movimiento');
            foreach ($porTipo as $tipo => $movimientos) {
                $totalCantidad = $movimientos->sum('cantidad');
                
                $reporte['resumen_por_tipo'][] = [
                    'tipo' => $tipo,
                    'movimientos' => $movimientos->count(),
                    'cantidad_total' => $totalCantidad
                ];
                
                if ($tipo === 'ENTRADA') {
                    $reporte['estadisticas']['total_entradas'] = $totalCantidad;
                } else if ($tipo === 'SALIDA') {
                    $reporte['estadisticas']['total_salidas'] = $totalCantidad;
                }
            }
            
            // Movimientos detallados
            $reporte['movimientos_detallados'] = $registros->map(function ($registro) {
                return [
                    'numero_trazabilidad' => $registro->numero_trazabilidad,
                    'fecha' => $registro->fecha_movimiento,
                    'producto' => $registro->codigo_producto,
                    'lote' => $registro->lote,
                    'tipo' => $registro->tipo_movimiento,
                    'cantidad' => $registro->cantidad,
                    'cliente' => $registro->cliente_nombre,
                    'documento' => $registro->documento_venta,
                    'observaciones' => $registro->observaciones
                ];
            });
            
            return [
                'success' => true,
                'reporte' => $reporte
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de trazabilidad', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar medicamentos próximos a vencer
     */
    public function verificarVencimientosProximos(int $diasAdvertencia = 30): array
    {
        try {
            $fechaLimite = now()->addDays($diasAdvertencia);
            
            $medicamentosVencidos = DB::table('trazabilidad_medicamentos as tm')
                ->join('productos as p', 'tm.codigo_producto', '=', 'p.CodPro')
                ->join('saldos as s', function ($join) {
                    $join->on('tm.codigo_producto', '=', 's.codpro')
                        ->on('tm.lote', '=', 's.lote');
                })
                ->where('p.Controlado', true)
                ->where('s.saldo', '>', 0)
                ->where('s.vencimiento', '<=', $fechaLimite)
                ->select([
                    'tm.codigo_producto',
                    'tm.nombre_producto',
                    'tm.lote',
                    's.vencimiento',
                    's.saldo',
                    's.almacen'
                ])
                ->orderBy('s.vencimiento')
                ->get();
            
            $alertas = [];
            foreach ($medicamentosVencidos as $medicamento) {
                $diasRestantes = now()->diffInDays($medicamento->vencimiento);
                $nivelCritico = $diasRestantes <= 7 ? 'CRÍTICO' : ($diasRestantes <= 15 ? 'ALTO' : 'MEDIO');
                
                $alertas[] = [
                    'codigo_producto' => $medicamento->codigo_producto,
                    'nombre_producto' => $medicamento->nombre_producto,
                    'lote' => $medicamento->lote,
                    'fecha_vencimiento' => $medicamento->vencimiento,
                    'dias_restantes' => $diasRestantes,
                    'cantidad_afectada' => $medicamento->saldo,
                    'almacen' => $medicamento->almacen,
                    'nivel_critico' => $nivelCritico,
                    'valor_estimado' => $this->calcularValorMedicamento($medicamento->codigo_producto, $medicamento->saldo)
                ];
            }
            
            return [
                'success' => true,
                'total_medicamentos_riesgo' => count($alertas),
                'fecha_verificacion' => now()->format('Y-m-d H:i:s'),
                'dias_advertencia' => $diasAdvertencia,
                'alertas' => $alertas
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al verificar vencimientos próximos', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de medicamentos controlados vendidos
     */
    public function generarReporteMedicamentosVendidos(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        try {
            $ventasControladas = DB::table('trazabilidad_medicamentos as tm')
                ->where('tm.tipo_movimiento', 'SALIDA')
                ->whereBetween('tm.fecha_movimiento', [$fechaInicio, $fechaFin])
                ->select([
                    'tm.codigo_producto',
                    'tm.nombre_producto',
                    'tm.lote',
                    'tm.cliente_documento',
                    'tm.cliente_nombre',
                    'tm.cantidad',
                    'tm.documento_venta',
                    'tm.fecha_movimiento'
                ])
                ->orderBy('tm.fecha_movimiento')
                ->get();
            
            $reporte = [
                'periodo' => [
                    'inicio' => $fechaInicio->format('Y-m-d'),
                    'fin' => $fechaFin->format('Y-m-d')
                ],
                'resumen' => [
                    'total_ventas' => $ventasControladas->count(),
                    'total_medicamentos' => $ventasControladas->sum('cantidad'),
                    'productos_diferentes' => $ventasControladas->groupBy('codigo_producto')->count(),
                    'clientes_diferentes' => $ventasControladas->groupBy('cliente_documento')->count()
                ],
                'por_producto' => [],
                'por_cliente' => [],
                'por_fecha' => [],
                'ventas_detalladas' => []
            ];
            
            // Agrupar por producto
            $porProducto = $ventasControladas->groupBy('codigo_producto');
            foreach ($porProducto as $codigo => $ventas) {
                $reporte['por_producto'][] = [
                    'codigo' => $codigo,
                    'nombre' => $ventas->first()->nombre_producto,
                    'cantidad_vendida' => $ventas->sum('cantidad'),
                    'numero_ventas' => $ventas->count(),
                    'clientes_diferentes' => $ventas->groupBy('cliente_documento')->count(),
                    'primer_venta' => $ventas->min('fecha_movimiento'),
                    'ultima_venta' => $ventas->max('fecha_movimiento')
                ];
            }
            
            // Agrupar por cliente
            $porCliente = $ventasControladas->groupBy('cliente_documento');
            foreach ($porCliente as $documento => $ventas) {
                if ($documento) { // Filtrar clientes sin documento
                    $reporte['por_cliente'][] = [
                        'documento' => $documento,
                        'nombre' => $ventas->first()->cliente_nombre,
                        'cantidad_comprada' => $ventas->sum('cantidad'),
                        'numero_compras' => $ventas->count(),
                        'productos_diferentes' => $ventas->groupBy('codigo_producto')->count(),
                        'primer_compra' => $ventas->min('fecha_movimiento'),
                        'ultima_compra' => $ventas->max('fecha_movimiento')
                    ];
                }
            }
            
            // Agrupar por fecha
            $porFecha = $ventasControladas->groupBy(function ($venta) {
                return Carbon::parse($venta->fecha_movimiento)->format('Y-m-d');
            });
            foreach ($porFecha as $fecha => $ventas) {
                $reporte['por_fecha'][] = [
                    'fecha' => $fecha,
                    'cantidad_vendida' => $ventas->sum('cantidad'),
                    'numero_ventas' => $ventas->count(),
                    'productos_vendidos' => $ventas->groupBy('codigo_producto')->count()
                ];
            }
            
            // Ventas detalladas
            $reporte['ventas_detalladas'] = $ventasControladas->map(function ($venta) {
                return [
                    'fecha' => $venta->fecha_movimiento,
                    'documento' => $venta->documento_venta,
                    'codigo_producto' => $venta->codigo_producto,
                    'nombre_producto' => $venta->nombre_producto,
                    'lote' => $venta->lote,
                    'cantidad' => $venta->cantidad,
                    'cliente_documento' => $venta->cliente_documento,
                    'cliente_nombre' => $venta->cliente_nombre
                ];
            });
            
            return [
                'success' => true,
                'reporte' => $reporte
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de medicamentos vendidos', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Métodos privados de apoyo

    private function generarNumeroTrazabilidad(): string
    {
        $prefijo = 'TRZ';
        $fecha = now()->format('Ymd');
        $secuencial = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefijo . $fecha . $secuencial;
    }

    private function validarLimitesVenta(Producto $producto, int $cantidad, array $cliente = null): void
    {
        // Implementar validaciones específicas según tipo de medicamento controlado
        // Por ejemplo, límites por cliente, por período, etc.
        
        if ($cantidad > 10) { // Ejemplo: límite de 10 unidades por venta
            throw new \Exception('Cantidad excede el límite permitido para medicamentos controlados');
        }
        
        // Otras validaciones según regulaciones locales
    }

    private function registrarHistorialTrazabilidad(array $registro): void
    {
        // Registrar en historial para auditoría
        Log::info('Historial de trazabilidad actualizado', [
            'numero_trazabilidad' => $registro['numero_trazabilidad'],
            'accion' => 'REGISTRO_MEDICAMENTO_CONTROLADO'
        ]);
    }

    private function generarReporteTrazabilidad(string $numeroTrazabilidad): array
    {
        return [
            'numero_reporte' => 'RPT-' . $numeroTrazabilidad,
            'fecha_generacion' => now(),
            'url_reporte' => '/reportes/trazabilidad/' . $numeroTrazabilidad
        ];
    }

    private function calcularValorMedicamento(string $codigoProducto, float $cantidad): float
    {
        $producto = Producto::find($codigoProducto);
        return $producto ? $producto->Costo * $cantidad : 0;
    }
}