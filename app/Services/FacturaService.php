<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Empleado;
use App\Models\AccesoWeb;
use App\Models\CuentaPorCobrar;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacturaService
{
    protected $sunatService;
    protected $contabilidadService;
    protected $trazabilidadService;

    public function __construct(
        SunatService $sunatService,
        ContabilidadService $contabilidadService,
        TrazabilidadService $trazabilidadService
    ) {
        $this->sunatService = $sunatService;
        $this->contabilidadService = $contabilidadService;
        $this->trazabilidadService = $trazabilidadService;
    }

    /**
     * Crear una nueva factura
     */
    public function crearFactura(array $datos): array
    {
        DB::beginTransaction();
        
        try {
            // Validar datos
            $this->validarDatosFactura($datos);
            
            // Generar número de factura
            $numero = $this->generarNumeroFactura($datos['tipo'] ?? 'FACTURA');
            
            // Crear encabezado de factura
            $factura = $this->crearEncabezadoFactura($datos, $numero);
            
            // Crear detalles de productos
            $total = $this->crearDetallesFactura($factura, $datos['productos']);
            
            // Actualizar totales
            $this->actualizarTotalesFactura($factura, $total);
            
            // Crear cuenta por cobrar si es crédito
            if ($datos['forma_pago'] === 'CREDITO') {
                $this->crearCuentaPorCobrar($factura, $total);
            }
            
            // Procesar con SUNAT si es necesario
            if ($datos['enviar_sunat'] ?? false) {
                $resultadoSunat = $this->sunatService->enviarFactura($factura);
                $factura->sunat_enviado = true;
                $factura->sunat_referencia = $resultadoSunat['referencia'] ?? null;
            }
            
            // Crear movimientos de inventario
            $this->procesarMovimientosInventario($factura);
            
            // Registrar en contabilidad
            $this->contabilidadService->registrarVenta($factura);
            
            // Registrar trazabilidad de medicamentos controlados
            $this->trazabilidadService->registrarSalidaMedicamentos($factura);
            
            DB::commit();
            
            Log::info('Factura creada exitosamente', [
                'numero' => $factura->Numero,
                'cliente' => $factura->CodClie,
                'total' => $factura->Total,
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);
            
            return [
                'success' => true,
                'factura' => $factura->load(['cliente', 'detalles.producto']),
                'message' => 'Factura creada exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear factura', [
                'error' => $e->getMessage(),
                'datos' => $datos,
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al crear la factura'
            ];
        }
    }

    /**
     * Actualizar factura existente
     */
    public function actualizarFactura(string $numero, int $tipo, array $datos): array
    {
        DB::beginTransaction();
        
        try {
            $factura = Venta::where('Numero', $numero)
                ->where('Tipo', $tipo)
                ->where('Eliminado', false)
                ->firstOrFail();
            
            // Validar que la factura no esté enviada a SUNAT
            if ($factura->sunat_enviado && $factura->Tipo === 1) {
                throw new \Exception('No se puede modificar una factura ya enviada a SUNAT');
            }
            
            // Limpiar detalles anteriores si están cambiando productos
            if (isset($datos['productos'])) {
                $factura->detalles()->delete();
                $factura->cuentas()->delete();
            }
            
            // Actualizar encabezado
            $this->actualizarEncabezadoFactura($factura, $datos);
            
            // Actualizar/recrear detalles
            if (isset($datos['productos'])) {
                $total = $this->crearDetallesFactura($factura, $datos['productos']);
                $this->actualizarTotalesFactura($factura, $total);
                
                // Recrear cuenta por cobrar
                $factura->cuentas()->delete();
                if ($datos['forma_pago'] === 'CREDITO') {
                    $this->crearCuentaPorCobrar($factura, $total);
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'factura' => $factura->load(['cliente', 'detalles.producto']),
                'message' => 'Factura actualizada exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar factura', [
                'numero' => $numero,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al actualizar la factura'
            ];
        }
    }

    /**
     * Anular factura
     */
    public function anularFactura(string $numero, int $tipo, string $motivo): array
    {
        DB::beginTransaction();
        
        try {
            $factura = Venta::where('Numero', $numero)
                ->where('Tipo', $tipo)
                ->where('Eliminado', false)
                ->firstOrFail();
            
            // Validar anulación
            $this->validarAnulacionFactura($factura);
            
            // Marcar como eliminada
            $factura->Eliminado = true;
            $factura->fecha_anulacion = now();
            $factura->motivo_anulacion = $motivo;
            $factura->usuario_anulacion = auth()->user()->idusuario ?? null;
            $factura->save();
            
            // Revertir movimientos de inventario
            $this->revertirMovimientosInventario($factura);
            
            // Cancelar cuenta por cobrar
            $factura->cuentas()->update(['Eliminado' => true]);
            
            // Registrar en contabilidad
            $this->contabilidadService->registrarAnulacionVenta($factura);
            
            DB::commit();
            
            Log::info('Factura anulada', [
                'numero' => $factura->Numero,
                'motivo' => $motivo,
                'usuario' => auth()->user()->usuario ?? 'Sistema'
            ]);
            
            return [
                'success' => true,
                'message' => 'Factura anulada exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al anular factura', [
                'numero' => $numero,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al anular la factura'
            ];
        }
    }

    /**
     * Obtener facturas por rango de fechas
     */
    public function obtenerFacturasPorFecha(Carbon $fechaInicio, Carbon $fechaFin, array $filtros = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Venta::query()
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', false);
        
        // Aplicar filtros
        if (!empty($filtros['cliente'])) {
            $query->where('CodClie', $filtros['cliente']);
        }
        
        if (!empty($filtros['vendedor'])) {
            $query->where('Vendedor', $filtros['vendedor']);
        }
        
        if (!empty($filtros['tipo'])) {
            $query->where('Tipo', $filtros['tipo']);
        }
        
        if (!empty($filtros['estado'])) {
            $query->where('Estado', $filtros['estado']);
        }
        
        return $query->with(['cliente', 'vendedor', 'detalles.producto'])
            ->orderBy('Fecha', 'desc')
            ->orderBy('Numero', 'desc')
            ->get();
    }

    /**
     * Generar estadísticas de ventas
     */
    public function generarEstadisticasVentas(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $facturas = $this->obtenerFacturasPorFecha($fechaInicio, $fechaFin);
        
        $estadisticas = [
            'total_facturas' => $facturas->count(),
            'total_ventas' => $facturas->sum('Total'),
            'promedio_venta' => $facturas->avg('Total'),
            'por_vendedor' => [],
            'por_cliente' => [],
            'por_producto' => [],
            'por_dia' => []
        ];
        
        // Estadísticas por vendedor
        $porVendedor = $facturas->groupBy('Vendedor')
            ->map(function ($ventas) {
                return [
                    'cantidad' => $ventas->count(),
                    'total' => $ventas->sum('Total'),
                    'promedio' => $ventas->avg('Total')
                ];
            });
        
        $estadisticas['por_vendedor'] = $porVendedor;
        
        // Estadísticas por cliente
        $porCliente = $facturas->groupBy('CodClie')
            ->sortByDesc(function ($ventas) {
                return $ventas->sum('Total');
            })
            ->take(10)
            ->map(function ($ventas, $codclie) {
                return [
                    'cliente' => Cliente::find($codclie)?->Razon ?? 'Desconocido',
                    'cantidad' => $ventas->count(),
                    'total' => $ventas->sum('Total'),
                    'promedio' => $ventas->avg('Total')
                ];
            });
        
        $estadisticas['por_cliente'] = $porCliente;
        
        // Estadísticas por día
        $porDia = $facturas->groupBy(function ($factura) {
            return $factura->Fecha->format('Y-m-d');
        })->map(function ($ventas, $fecha) {
            return [
                'fecha' => $fecha,
                'cantidad' => $ventas->count(),
                'total' => $ventas->sum('Total')
            ];
        });
        
        $estadisticas['por_dia'] = $porDia;
        
        return $estadisticas;
    }

    // Métodos privados de apoyo

    private function validarDatosFactura(array $datos): void
    {
        $required = ['CodClie', 'Fecha', 'productos'];
        
        foreach ($required as $field) {
            if (!isset($datos[$field])) {
                throw new \Exception("El campo {$field} es obligatorio");
            }
        }
        
        // Validar cliente existe
        if (!Cliente::find($datos['CodClie'])) {
            throw new \Exception('Cliente no encontrado');
        }
        
        // Validar productos
        foreach ($datos['productos'] as $producto) {
            if (!Producto::find($producto['codpro'])) {
                throw new \Exception("Producto {$producto['codpro']} no encontrado");
            }
            
            if ($producto['cantidad'] <= 0) {
                throw new \Exception('La cantidad debe ser mayor a 0');
            }
            
            if ($producto['precio'] <= 0) {
                throw new \Exception('El precio debe ser mayor a 0');
            }
        }
    }

    private function generarNumeroFactura(string $tipo): string
    {
        $prefix = match($tipo) {
            'FACTURA' => 'F',
            'BOLETA' => 'B',
            'COTIZACION' => 'C',
            'PEDIDO' => 'P',
            default => 'F'
        };
        
        $ultimo = Venta::where('Tipo', $this->getTipoCodigo($tipo))
            ->where('Eliminado', false)
            ->orderBy('Numero', 'desc')
            ->first();
        
        $numero = $ultimo ? $ultimo->Numero + 1 : 1;
        
        return $prefix . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    private function getTipoCodigo(string $tipo): int
    {
        return match($tipo) {
            'FACTURA' => 1,
            'BOLETA' => 2,
            'COTIZACION' => 3,
            'PEDIDO' => 4,
            default => 1
        };
    }

    private function crearEncabezadoFactura(array $datos, string $numero): Venta
    {
        return Venta::create([
            'Numero' => $numero,
            'Tipo' => $this->getTipoCodigo($datos['tipo'] ?? 'FACTURA'),
            'CodClie' => $datos['CodClie'],
            'Fecha' => $datos['Fecha'],
            'Vendedor' => $datos['Vendedor'] ?? auth()->user()->idusuario ?? null,
            'TipoPago' => $datos['forma_pago'] ?? 'CONTADO',
            'Estado' => 'PENDIENTE',
            'Total' => 0,
            'Observaciones' => $datos['observaciones'] ?? null
        ]);
    }

    private function crearDetallesFactura(Venta $factura, array $productos): float
    {
        $total = 0;
        
        foreach ($productos as $producto) {
            $subtotal = $producto['cantidad'] * $producto['precio'];
            $total += $subtotal;
            
            $factura->detalles()->create([
                'Codpro' => $producto['codpro'],
                'Lote' => $producto['lote'] ?? null,
                'Cantidad' => $producto['cantidad'],
                'Precio' => $producto['precio'],
                'Subtotal' => $subtotal,
                'Nbonif' => 0
            ]);
        }
        
        return $total;
    }

    private function actualizarTotalesFactura(Venta $factura, float $subtotal): void
    {
        $impuesto = $subtotal * 0.18; // IGV 18%
        $total = $subtotal + $impuesto;
        
        $factura->update([
            'Subtotal' => $subtotal,
            'Impuesto' => $impuesto,
            'Total' => $total
        ]);
    }

    private function crearCuentaPorCobrar(Venta $factura, float $total): void
    {
        CuentaPorCobrar::create([
            'Documento' => $factura->Numero,
            'Tipo' => $factura->Tipo,
            'CodClie' => $factura->CodClie,
            'Fecha' => $factura->Fecha,
            'FechaV' => now()->addDays(30), // 30 días plazo
            'Importe' => $total,
            'Saldo' => $total,
            'Eliminado' => false
        ]);
    }

    private function procesarMovimientosInventario(Venta $factura): void
    {
        foreach ($factura->detalles as $detalle) {
            MovimientoInventario::create([
                'codpro' => $detalle->Codpro,
                'almacen' => 'PRINCIPAL',
                'lote' => $detalle->Lote ?? 'SIN_LOTE',
                'tipo_movimiento' => 2, // Salida
                'cantidad' => $detalle->Cantidad,
                'fecha' => now(),
                'documento' => $factura->Numero,
                'observacion' => "Venta factura {$factura->Numero}"
            ]);
        }
    }

    private function revertirMovimientosInventario(Venta $factura): void
    {
        MovimientoInventario::where('documento', $factura->Numero)
            ->where('tipo_movimiento', 2)
            ->update([
                'tipo_movimiento' => 1, // Entrada
                'observacion' => "Anulación factura {$factura->Numero}"
            ]);
    }

    private function validarAnulacionFactura(Venta $factura): void
    {
        // Validar que no tenga pagos aplicados
        $pagos = $factura->cuentas()
            ->where('Eliminado', false)
            ->where('Saldo', '<', DB::raw('Importe'))
            ->count();
        
        if ($pagos > 0) {
            throw new \Exception('No se puede anular una factura con pagos aplicados');
        }
        
        // Validar tiempo de anulación (ej: máximo 24 horas)
        if ($factura->created_at->diffInHours(now()) > 24) {
            throw new \Exception('No se puede anular facturas después de 24 horas');
        }
    }

    private function actualizarEncabezadoFactura(Venta $factura, array $datos): void
    {
        $factura->update([
            'CodClie' => $datos['CodClie'] ?? $factura->CodClie,
            'Fecha' => $datos['Fecha'] ?? $factura->Fecha,
            'Vendedor' => $datos['Vendedor'] ?? $factura->Vendedor,
            'TipoPago' => $datos['forma_pago'] ?? $factura->TipoPago,
            'Observaciones' => $datos['observaciones'] ?? $factura->Observaciones
        ]);
    }
}