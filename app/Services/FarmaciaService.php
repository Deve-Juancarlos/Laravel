<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\TrazabilidadService;

class FarmaciaService
{
    protected $trazabilidadService;

    public function __construct(TrazabilidadService $trazabilidadService)
    {
        $this->trazabilidadService = $trazabilidadService;
    }

    /**
     * Verificar stock mínimo de productos
     */
    public function verificarStockMinimo(): array
    {
        $productosStockMinimo = Producto::where('Stock', '<=', DB::raw('StockMinimo'))
            ->where('StockMinimo', '>', 0)
            ->with(['laboratorio', 'saldo'])
            ->get();

        $productosSinStock = Producto::where('Stock', '=', 0)->get();

        return [
            'stock_minimo' => $productosStockMinimo->map(function ($producto) {
                return [
                    'codpro' => $producto->CodPro,
                    'nombre' => $producto->Nombre,
                    'stock_actual' => $producto->Stock,
                    'stock_minimo' => $producto->StockMinimo,
                    'laboratorio' => $producto->laboratorio?->Descripcion ?? 'No definido',
                    'ubicacion' => $producto->saldo?->almacen ?? 'No definido'
                ];
            }),
            'sin_stock' => $productosSinStock->map(function ($producto) {
                return [
                    'codpro' => $producto->CodPro,
                    'nombre' => $producto->Nombre,
                    'laboratorio' => $producto->laboratorio?->Descripcion ?? 'No definido'
                ];
            })
        ];
    }

    /**
     * Verificar productos próximos a vencer
     */
    public function verificarVencimientos(Carbon $fechaLimite = null): array
    {
        if (!$fechaLimite) {
            $fechaLimite = now()->addDays(90); // 3 meses por defecto
        }

        $productosVencidos = MovimientoInventario::where('vencimiento', '<', now())
            ->where('saldo', '>', 0)
            ->with(['producto', 'almacen'])
            ->get();

        $productosPorVencer = MovimientoInventario::whereBetween('vencimiento', [now(), $fechaLimite])
            ->where('saldo', '>', 0)
            ->with(['producto', 'almacen'])
            ->orderBy('vencimiento')
            ->get();

        return [
            'vencidos' => $productosVencidos->map(function ($movimiento) {
                return [
                    'codpro' => $movimiento->codpro,
                    'nombre' => $movimiento->producto?->Nombre ?? 'Desconocido',
                    'lote' => $movimiento->lote,
                    'fecha_vencimiento' => $movimiento->vencimiento->format('Y-m-d'),
                    'dias_vencido' => abs($movimiento->vencimiento->diffInDays(now())),
                    'saldo' => $movimiento->saldo,
                    'almacen' => $movimiento->almacen,
                    'valor_perdido' => $movimiento->saldo * ($movimiento->producto?->Costo ?? 0)
                ];
            }),
            'proximos_vencer' => $productosPorVencer->map(function ($movimiento) {
                return [
                    'codpro' => $movimiento->codpro,
                    'nombre' => $movimiento->producto?->Nombre ?? 'Desconocido',
                    'lote' => $movimiento->lote,
                    'fecha_vencimiento' => $movimiento->vencimiento->format('Y-m-d'),
                    'dias_restantes' => $movimiento->vencimiento->diffInDays(now()),
                    'saldo' => $movimiento->saldo,
                    'almacen' => $movimiento->almacen,
                    'valor_inventario' => $movimiento->saldo * ($movimiento->producto?->Costo ?? 0)
                ];
            })
        ];
    }

    /**
     * Gestionar medicamentos controlados
     */
    public function procesarMedicamentoControlado(array $datos): array
    {
        try {
            $producto = Producto::findOrFail($datos['codpro']);
            
            // Verificar si es medicamento controlado
            if (!$producto->Controlado) {
                throw new \Exception('El producto no es un medicamento controlado');
            }

            DB::beginTransaction();

            // Validar disponibilidad de stock
            $stockDisponible = $this->obtenerStockPorLote($datos['codpro'], $datos['lote'] ?? null);
            if ($stockDisponible < $datos['cantidad']) {
                throw new \Exception('Stock insuficiente para el medicamento controlado');
            }

            // Registrar venta en sistema de trazabilidad
            $resultadoTrazabilidad = $this->trazabilidadService->registrarMedicamentoControlado([
                'producto' => $producto,
                'cantidad' => $datos['cantidad'],
                'lote' => $datos['lote'],
                'cliente' => $datos['cliente'] ?? null,
                'documento' => $datos['documento'] ?? null,
                'fecha' => $datos['fecha'] ?? now(),
                'usuario' => auth()->user()->idusuario ?? null
            ]);

            // Crear movimiento de salida
            $movimiento = MovimientoInventario::create([
                'codpro' => $datos['codpro'],
                'almacen' => $datos['almacen'] ?? 'PRINCIPAL',
                'lote' => $datos['lote'],
                'tipo_movimiento' => 2, // Salida
                'cantidad' => $datos['cantidad'],
                'fecha' => now(),
                'documento' => $datos['documento'] ?? null,
                'observacion' => "Medicamento controlado - Trazabilidad: {$resultadoTrazabilidad['numero_trazabilidad']}"
            ]);

            // Actualizar stock
            $this->actualizarStock($datos['codpro'], $datos['lote'], -$datos['cantidad']);

            DB::commit();

            Log::info('Medicamento controlado procesado', [
                'producto' => $datos['codpro'],
                'lote' => $datos['lote'],
                'cantidad' => $datos['cantidad'],
                'trazabilidad' => $resultadoTrazabilidad['numero_trazabilidad'],
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);

            return [
                'success' => true,
                'movimiento' => $movimiento,
                'trazabilidad' => $resultadoTrazabilidad,
                'message' => 'Medicamento controlado procesado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al procesar medicamento controlado', [
                'producto' => $datos['codpro'] ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al procesar el medicamento controlado'
            ];
        }
    }

    /**
     * Generar reporte de medicamentos controlados
     */
    public function generarReporteMedicamentosControlados(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $movimientosControlados = MovimientoInventario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('producto', function ($query) {
                $query->where('Controlado', true);
            })
            ->with(['producto.laboratorio', 'almacen'])
            ->orderBy('fecha')
            ->get();

        $resumen = [
            'total_movimientos' => $movimientosControlados->count(),
            'total_salidas' => $movimientosControlados->where('tipo_movimiento', 2)->sum('cantidad'),
            'total_entradas' => $movimientosControlados->where('tipo_movimiento', 1)->sum('cantidad'),
            'productos_controlados' => [],
            'por_laboratorio' => [],
            'por_almacen' => []
        ];

        // Agrupar por producto
        $productos = $movimientosControlados->groupBy('codpro');
        foreach ($productos as $codpro => $movs) {
            $producto = $movs->first()->producto;
            $resumen['productos_controlados'][] = [
                'codpro' => $codpro,
                'nombre' => $producto->Nombre,
                'laboratorio' => $producto->laboratorio?->Descripcion ?? 'No definido',
                'total_movimientos' => $movs->count(),
                'salidas' => $movs->where('tipo_movimiento', 2)->sum('cantidad'),
                'entradas' => $movs->where('tipo_movimiento', 1)->sum('cantidad'),
                'ultimo_movimiento' => $movs->max('fecha')
            ];
        }

        // Agrupar por laboratorio
        $laboratorios = $movimientosControlados->groupBy(function ($movimiento) {
            return $movimiento->producto->laboratorio?->Descripcion ?? 'No definido';
        });
        
        foreach ($laboratorios as $laboratorio => $movs) {
            $resumen['por_laboratorio'][] = [
                'laboratorio' => $laboratorio,
                'total_movimientos' => $movs->count(),
                'salidas' => $movs->where('tipo_movimiento', 2)->sum('cantidad'),
                'entradas' => $movs->where('tipo_movimiento', 1)->sum('cantidad')
            ];
        }

        // Agrupar por almacén
        $almacenes = $movimientosControlados->groupBy('almacen');
        foreach ($almacenes as $almacen => $movs) {
            $resumen['por_almacen'][] = [
                'almacen' => $almacen,
                'total_movimientos' => $movs->count(),
                'salidas' => $movs->where('tipo_movimiento', 2)->sum('cantidad'),
                'entradas' => $movs->where('tipo_movimiento', 1)->sum('cantidad')
            ];
        }

        return $resumen;
    }

    /**
     * Gestionar medicamentos refrigerados
     */
    public function verificarTemperaturaMedicamentos(): array
    {
        // En una implementación real, esto conectaría con sensores IoT
        // Por ahora simulamos los datos
        
        $medicamentosRefrigerados = Producto::where('Refrigerado', true)
            ->with(['saldo' => function ($query) {
                $query->where('saldo', '>', 0);
            }])
            ->get();

        $alertas = [];
        $estadoGeneral = 'NORMAL';

        foreach ($medicamentosRefrigerados as $producto) {
            foreach ($producto->saldo as $saldo) {
                // Simulación de temperatura (en producción vendría de sensores)
                $temperatura = $this->obtenerTemperaturaActual($saldo->almacen);
                
                if ($temperatura > 8 || $temperatura < 2) {
                    $alertas[] = [
                        'codpro' => $producto->CodPro,
                        'nombre' => $producto->Nombre,
                        'almacen' => $saldo->almacen,
                        'temperatura' => $temperatura,
                        'rango_ideal' => '2-8°C',
                        'nivel_alerta' => $this->determinarNivelAlerta($temperatura),
                        'cantidad' => $saldo->saldo,
                        'lote' => $saldo->lote
                    ];
                    
                    $estadoGeneral = 'ALERTA';
                }
            }
        }

        return [
            'estado_general' => $estadoGeneral,
            'temperatura_ambiente' => $this->obtenerTemperaturaAmbiente(),
            'medicamentos_monitoreados' => $medicamentosRefrigerados->count(),
            'alertas' => $alertas,
            'ultima_actualizacion' => now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Crear alerta de prescripción médica
     */
    public function crearAlertaPrescripcion(array $datos): array
    {
        try {
            // Validar datos de prescripción
            $this->validarPrescripcion($datos);

            // Verificar interacciones medicamentosas
            $interacciones = $this->verificarInteraccionesMedicamentosas($datos['medicamentos']);

            // Verificar contraindicaciones
            $contraindicaciones = $this->verificarContraindicaciones($datos);

            // Crear alerta
            $alerta = [
                'cliente_id' => $datos['cliente_id'],
                'medico' => $datos['medico'],
                'medicamentos' => $datos['medicamentos'],
                'interacciones' => $interacciones,
                'contraindicaciones' => $contraindicaciones,
                'nivel_riesgo' => $this->calcularNivelRiesgo($interacciones, $contraindicaciones),
                'fecha_prescripcion' => $datos['fecha_prescripcion'],
                'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? now()->addDays(30),
                'estado' => 'PENDIENTE',
                'usuario_creacion' => auth()->user()->idusuario ?? null,
                'fecha_creacion' => now()
            ];

            // Guardar alerta (esto se expandiría a una tabla específica)
            Log::info('Alerta de prescripción creada', $alerta);

            return [
                'success' => true,
                'alerta' => $alerta,
                'message' => 'Alerta de prescripción creada exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error al crear alerta de prescripción', [
                'error' => $e->getMessage(),
                'datos' => $datos
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al crear la alerta de prescripción'
            ];
        }
    }

    /**
     * Obtener historial de medicamentos por cliente
     */
    public function obtenerHistorialMedicamentos(string $clienteId, int $dias = 365): array
    {
        $fechaInicio = now()->subDays($dias);
        
        $historial = DB::table('Doccab as d')
            ->join('Docdet as det', function ($join) {
                $join->on('d.Numero', '=', 'det.Numero')
                    ->on('d.Tipo', '=', 'det.Tipo');
            })
            ->join('Productos as p', 'det.Codpro', '=', 'p.CodPro')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('d.CodClie', $clienteId)
            ->whereBetween('d.Fecha', [$fechaInicio, now()])
            ->where('d.Eliminado', false)
            ->select([
                'd.Fecha',
                'd.Numero',
                'd.Tipo',
                'det.Codpro',
                'p.Nombre as producto_nombre',
                'p.Controlado',
                'l.Descripcion as laboratorio',
                'det.Cantidad',
                'det.Precio',
                'det.Subtotal'
            ])
            ->orderBy('d.Fecha', 'desc')
            ->get();

        $resumen = [
            'total_compras' => $historial->count(),
            'gasto_total' => $historial->sum('Subtotal'),
            'medicamentos_controlados' => $historial->where('Controlado', true)->count(),
            'laboratorios_frecuentes' => [],
            'cronologico' => $historial->groupBy('Fecha')->map(function ($compras, $fecha) {
                return [
                    'fecha' => $fecha,
                    'total_compras' => $compras->count(),
                    'gasto_dia' => $compras->sum('Subtotal'),
                    'medicamentos' => $compras->map(function ($compra) {
                        return [
                            'codpro' => $compra->Codpro,
                            'nombre' => $compra->producto_nombre,
                            'laboratorio' => $compra->laboratorio,
                            'cantidad' => $compra->Cantidad,
                            'controlado' => (bool) $compra->Controlado,
                            'precio_unitario' => $compra->Precio,
                            'subtotal' => $compra->Subtotal
                        ];
                    })->values()
                ];
            })
        ];

        // Top laboratorios
        $laboratorios = $historial->groupBy('laboratorio')
            ->sortByDesc(fn($items) => $items->sum('Subtotal'))
            ->take(5)
            ->map(function ($items, $laboratorio) {
                return [
                    'laboratorio' => $laboratorio,
                    'total_gastado' => $items->sum('Subtotal'),
                    'numero_compras' => $items->count()
                ];
            });

        $resumen['laboratorios_frecuentes'] = $laboratorios;

        return $resumen;
    }

    // Métodos privados de apoyo

    private function obtenerStockPorLote(string $codpro, ?string $lote = null): float
    {
        $query = MovimientoInventario::where('codpro', $codpro)
            ->where('saldo', '>', 0);

        if ($lote) {
            $query->where('lote', $lote);
        }

        return $query->sum('saldo');
    }

    private function actualizarStock(string $codpro, string $lote, float $cantidad): void
    {
        $saldo = MovimientoInventario::where('codpro', $codpro)
            ->where('lote', $lote)
            ->first();

        if ($saldo) {
            $saldo->saldo += $cantidad;
            $saldo->save();
        }
    }

    private function obtenerTemperaturaActual(string $almacen): float
    {
        // En producción esto vendría de sensores IoT
        // Por ahora simulamos temperatura aleatoria
        return rand(15, 25) / 10; // Entre 1.5 y 2.5°C (simulación)
    }

    private function obtenerTemperaturaAmbiente(): float
    {
        return rand(180, 280) / 10; // Entre 18 y 28°C
    }

    private function determinarNivelAlerta(float $temperatura): string
    {
        if ($temperatura > 10 || $temperatura < 1) {
            return 'CRÍTICO';
        } elseif ($temperatura > 8 || $temperatura < 2) {
            return 'ALTO';
        } else {
            return 'MEDIO';
        }
    }

    private function validarPrescripcion(array $datos): void
    {
        $required = ['cliente_id', 'medico', 'medicamentos'];
        foreach ($required as $field) {
            if (!isset($datos[$field]) || empty($datos[$field])) {
                throw new \Exception("El campo {$field} es obligatorio");
            }
        }

        if (!is_array($datos['medicamentos']) || empty($datos['medicamentos'])) {
            throw new \Exception('Debe especificar al menos un medicamento');
        }
    }

    private function verificarInteraccionesMedicamentosas(array $medicamentos): array
    {
        // Implementación básica - en producción usaría una base de datos de interacciones
        $interacciones = [];

        // Ejemplo de interacciones conocidas
        $interaccionesConocidas = [
            'WARFARINA' => ['ASPIRINA', 'IBUPROFENO'],
            'DIGOXINA' => ['FUROSEMIDA', 'VERAPAMILO'],
            'LITIO' => ['IBUPROFENO', 'NAPROXENO']
        ];

        foreach ($medicamentos as $medicamento) {
            $nombre = strtoupper($medicamento['nombre'] ?? '');
            
            foreach ($interaccionesConocidas as $med1 => $interactua_con) {
                foreach ($interactua_con as $med2) {
                    if ($nombre === $med1) {
                        foreach ($medicamentos as $otroMed) {
                            if (in_array(strtoupper($otroMed['nombre'] ?? ''), $interactua_con)) {
                                $interacciones[] = [
                                    'medicamento_1' => $med1,
                                    'medicamento_2' => $med2,
                                    'severidad' => 'ALTA',
                                    'descripcion' => "Interacción entre {$med1} y {$med2}"
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $interacciones;
    }

    private function verificarContraindicaciones(array $datos): array
    {
        $contraindicaciones = [];
        
        // Verificar alergias conocidas
        $cliente = Cliente::find($datos['cliente_id']);
        if ($cliente && $cliente->Alergias) {
            $alergias = explode(',', $cliente->Alergias);
            foreach ($datos['medicamentos'] as $medicamento) {
                foreach ($alergias as $alergia) {
                    if (str_contains(strtoupper($medicamento['nombre'] ?? ''), strtoupper(trim($alergia)))) {
                        $contraindicaciones[] = [
                            'tipo' => 'ALERGIA',
                            'medicamento' => $medicamento['nombre'],
                            'descripcion' => "Alergia conocida a {$alergia}",
                            'severidad' => 'CRÍTICA'
                        ];
                    }
                }
            }
        }

        return $contraindicaciones;
    }

    private function calcularNivelRiesgo(array $interacciones, array $contraindicaciones): string
    {
        $puntuacionRiesgo = 0;

        foreach ($interacciones as $interaccion) {
            $puntuacionRiesgo += match($interaccion['severidad']) {
                'ALTA' => 10,
                'MEDIA' => 5,
                'BAJA' => 2,
                default => 1
            };
        }

        foreach ($contraindicaciones as $contraindicacion) {
            $puntuacionRiesgo += match($contraindicacion['severidad']) {
                'CRÍTICA' => 20,
                'ALTA' => 15,
                'MEDIA' => 10,
                'BAJA' => 5,
                default => 1
            };
        }

        if ($puntuacionRiesgo >= 30) {
            return 'CRÍTICO';
        } elseif ($puntuacionRiesgo >= 15) {
            return 'ALTO';
        } elseif ($puntuacionRiesgo >= 5) {
            return 'MEDIO';
        } else {
            return 'BAJO';
        }
    }
}