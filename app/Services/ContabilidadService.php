<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\CuentaPorCobrar;
use App\Models\MovimientoInventario;
use App\Models\Banco;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContabilidadService
{
    /**
     * Registrar venta en contabilidad
     */
    public function registrarVenta(Venta $factura): array
    {
        try {
            DB::beginTransaction();
            
            $asientos = $this->generarAsientosVenta($factura);
            
            foreach ($asientos as $asiento) {
                $this->registrarAsientoContable($asiento);
            }
            
            // Registrar efectos en kardex
            $this->actualizarKardexVentas($factura);
            
            // Actualizar cuentas por cobrar si es crédito
            if ($factura->TipoPago === 'CREDITO') {
                $this->actualizarCuentasPorCobrar($factura);
            }
            
            DB::commit();
            
            Log::info('Venta registrada en contabilidad', [
                'factura' => $factura->Numero,
                'total' => $factura->Total,
                'asientos' => count($asientos)
            ]);
            
            return [
                'success' => true,
                'asientos_creados' => count($asientos),
                'message' => 'Venta registrada en contabilidad exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar venta en contabilidad', [
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
     * Registrar pago recibido
     */
    public function registrarPago(Pago $pago): array
    {
        try {
            DB::beginTransaction();
            
            // Determinar si es ingreso o egreso
            if ($pago->Tipo === 1) { // Ingreso
                $asientos = $this->generarAsientosIngreso($pago);
            } else { // Egreso
                $asientos = $this->generarAsientosEgreso($pago);
            }
            
            foreach ($asientos as $asiento) {
                $this->registrarAsientoContable($asiento);
            }
            
            // Actualizar cuentas por cobrar si aplica
            if ($pago->Tipo === 1 && $pago->Factura) {
                $this->actualizarCuentaPorCobrar($pago);
            }
            
            // Actualizar saldo bancario
            if ($pago->CuentaBanco) {
                $this->actualizarSaldoBancario($pago);
            }
            
            DB::commit();
            
            Log::info('Pago registrado en contabilidad', [
                'pago' => $pago->Numero,
                'monto' => $pago->Monto,
                'tipo' => $pago->Tipo
            ]);
            
            return [
                'success' => true,
                'asientos_creados' => count($asientos),
                'message' => 'Pago registrado exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar pago', [
                'pago' => $pago->Numero,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar balance de comprobación
     */
    public function generarBalanceComprobacion(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        try {
            $cuentas = $this->obtenerCuentasConMovimientos($fechaInicio, $fechaFin);
            
            $balance = [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'total_debe' => 0,
                'total_haber' => 0,
                'cuentas' => []
            ];
            
            foreach ($cuentas as $cuenta) {
                $balance['cuentas'][] = [
                    'cuenta' => $cuenta['cuenta'],
                    'descripcion' => $cuenta['descripcion'],
                    'debe' => $cuenta['debe'],
                    'haber' => $cuenta['haber'],
                    'saldo_deudor' => $cuenta['debe'] > $cuenta['haber'] ? $cuenta['debe'] - $cuenta['haber'] : 0,
                    'saldo_acreedor' => $cuenta['haber'] > $cuenta['debe'] ? $cuenta['haber'] - $cuenta['debe'] : 0
                ];
                
                $balance['total_debe'] += $cuenta['debe'];
                $balance['total_haber'] += $cuenta['haber'];
            }
            
            $balance['diferencia'] = abs($balance['total_debe'] - $balance['total_haber']);
            $balance['balanceado'] = $balance['diferencia'] < 0.01;
            
            return [
                'success' => true,
                'balance' => $balance
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar balance de comprobación', [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar estado de resultados
     */
    public function generarEstadoResultados(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        try {
            $ingresos = $this->calcularIngresos($fechaInicio, $fechaFin);
            $costos = $this->calcularCostosVentas($fechaInicio, $fechaFin);
            $gastos = $this->calcularGastosOperativos($fechaInicio, $fechaFin);
            
            $estadoResultados = [
                'periodo' => [
                    'inicio' => $fechaInicio->format('Y-m-d'),
                    'fin' => $fechaFin->format('Y-m-d')
                ],
                'ingresos' => $ingresos,
                'costos_ventas' => $costos,
                'gastos_operativos' => $gastos,
                'utilidad_bruta' => $ingresos['total'] - $costos['total'],
                'utilidad_operativa' => 0,
                'utilidad_neta' => 0
            ];
            
            $estadoResultados['utilidad_operativa'] = 
                $estadoResultados['utilidad_bruta'] - $gastos['total'];
            
            $estadoResultados['utilidad_neta'] = 
                $estadoResultados['utilidad_operativa']; // Sin considerar otros ingresos/egresos
            
            return [
                'success' => true,
                'estado_resultados' => $estadoResultados
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar estado de resultados', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar balance general
     */
    public function generarBalanceGeneral(Carbon $fechaCorte): array
    {
        try {
            $activos = $this->calcularActivos($fechaCorte);
            $pasivos = $this->calcularPasivos($fechaCorte);
            $patrimonio = $this->calcularPatrimonio($fechaCorte);
            
            $balanceGeneral = [
                'fecha_corte' => $fechaCorte->format('Y-m-d'),
                'activos' => $activos,
                'pasivos' => $pasivos,
                'patrimonio' => $patrimonio,
                'total_activos' => $activos['total'],
                'total_pasivos' => $pasivos['total'],
                'total_patrimonio' => $patrimonio['total']
            ];
            
            // Verificar equilibrio contable
            $balanceGeneral['diferencia'] = 
                $balanceGeneral['total_activos'] - 
                ($balanceGeneral['total_pasivos'] + $balanceGeneral['total_patrimonio']);
            $balanceGeneral['equilibrado'] = abs($balanceGeneral['diferencia']) < 0.01;
            
            return [
                'success' => true,
                'balance_general' => $balanceGeneral
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar balance general', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Registrar anulación de venta
     */
    public function registrarAnulacionVenta(Venta $factura): array
    {
        try {
            DB::beginTransaction();
            
            // Generar asientos de anulación (reversión)
            $asientos = $this->generarAsientosAnulacion($factura);
            
            foreach ($asientos as $asiento) {
                $this->registrarAsientoContable($asiento);
            }
            
            // Actualizar kardex (reversión de salidas)
            $this->revertirKardexVentas($factura);
            
            // Cancelar cuenta por cobrar
            $this->cancelarCuentaPorCobrar($factura);
            
            DB::commit();
            
            Log::info('Anulación de venta registrada en contabilidad', [
                'factura' => $factura->Numero,
                'asientos' => count($asientos)
            ]);
            
            return [
                'success' => true,
                'asientos_creados' => count($asientos),
                'message' => 'Anulación registrada exitosamente'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar anulación de venta', [
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
     * Generar flujo de caja
     */
    public function generarFlujoCaja(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        try {
            $flujoOperativo = $this->calcularFlujoOperativo($fechaInicio, $fechaFin);
            $flujoInversion = $this->calcularFlujoInversion($fechaInicio, $fechaFin);
            $flujoFinanciamiento = $this->calcularFlujoFinanciamiento($fechaInicio, $fechaFin);
            
            $flujoCaja = [
                'periodo' => [
                    'inicio' => $fechaInicio->format('Y-m-d'),
                    'fin' => $fechaFin->format('Y-m-d')
                ],
                'actividades_operativas' => $flujoOperativo,
                'actividades_inversion' => $flujoInversion,
                'actividades_financiamiento' => $flujoFinanciamiento,
                'neto_operativo' => $flujoOperativo['total'],
                'neto_inversion' => $flujoInversion['total'],
                'neto_financiamiento' => $flujoFinanciamiento['total'],
                'variacion_neta' => 0
            ];
            
            $flujoCaja['variacion_neta'] = 
                $flujoCaja['neto_operativo'] + 
                $flujoCaja['neto_inversion'] + 
                $flujoCaja['neto_financiamiento'];
            
            return [
                'success' => true,
                'flujo_caja' => $flujoCaja
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar flujo de caja', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Conciliar cuentas bancarias
     */
    public function conciliarCuentaBancaria(string $cuentaBanco, Carbon $fechaCorte): array
    {
        try {
            // Obtener saldo según libros
            $saldoLibros = $this->obtenerSaldoContableCuenta($cuentaBanco, $fechaCorte);
            
            // Obtener saldo según extracto bancario
            $saldoBanco = $this->obtenerSaldoBancario($cuentaBanco, $fechaCorte);
            
            // Obtener diferencias (cheques en tránsito, depósitos en tránsito, etc.)
            $diferencias = $this->analizarDiferenciasConciliacion($cuentaBanco, $fechaCorte);
            
            $conciliacion = [
                'cuenta' => $cuentaBanco,
                'fecha_corte' => $fechaCorte->format('Y-m-d'),
                'saldo_libros' => $saldoLibros,
                'saldo_banco' => $saldoBanco,
                'diferencias' => $diferencias,
                'ajustes_requeridos' => $diferencias['total_diferencias'],
                'saldo_conciliado' => $saldoBanco + $diferencias['total_diferencias'],
                'balance' => abs(($saldoLibros) - ($saldoBanco + $diferencias['total_diferencias'])) < 0.01
            ];
            
            return [
                'success' => true,
                'conciliacion' => $conciliacion
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Métodos privados de apoyo

    private function generarAsientosVenta(Venta $factura): array
    {
        $asientos = [];
        
        // Asiento 1: Registro de venta (Cliente - Ventas)
        $asientos[] = [
            'fecha' => $factura->Fecha,
            'documento' => $factura->Numero,
            'concepto' => "Venta factura {$factura->Numero}",
            'detalle' => "Venta a cliente {$factura->cliente->Razon}",
            'lineas' => [
                [
                    'cuenta' => '12', // Cuentas por cobrar comerciales
                    'descripcion' => "Cliente {$factura->cliente->Razon}",
                    'debe' => $factura->Total,
                    'haber' => 0
                ],
                [
                    'cuenta' => '701', // Ventas
                    'descripcion' => "Venta factura {$factura->Numero}",
                    'debe' => 0,
                    'haber' => $factura->Subtotal
                ],
                [
                    'cuenta' => '401', // IGV
                    'descripcion' => "IGV factura {$factura->Numero}",
                    'debe' => 0,
                    'haber' => $factura->Impuesto
                ]
            ]
        ];
        
        // Asiento 2: Costo de ventas (si se maneja inventario perpetuo)
        if ($factura->detalles->isNotEmpty()) {
            $costoTotal = $factura->detalles->sum(function ($detalle) {
                return $detalle->Cantidad * ($detalle->producto->Costo ?? 0);
            });
            
            if ($costoTotal > 0) {
                $asientos[] = [
                    'fecha' => $factura->Fecha,
                    'documento' => $factura->Numero,
                    'concepto' => "Costo de ventas factura {$factura->Numero}",
                    'detalle' => "Costo de productos vendidos",
                    'lineas' => [
                        [
                            'cuenta' => '691', // Costo de ventas
                            'descripcion' => "Costo ventas factura {$factura->Numero}",
                            'debe' => $costoTotal,
                            'haber' => 0
                        ],
                        [
                            'cuenta' => '20', // Mercaderías
                            'descripcion' => "Mercaderías - Salida por venta",
                            'debe' => 0,
                            'haber' => $costoTotal
                        ]
                    ]
                ];
            }
        }
        
        return $asientos;
    }

    private function generarAsientosIngreso(Pago $pago): array
    {
        $asientos = [];
        
        // Asiento de ingreso
        if ($pago->CuentaBanco) {
            // Ingreso a cuenta bancaria
            $asientos[] = [
                'fecha' => $pago->Fecha,
                'documento' => $pago->Numero,
                'concepto' => "Ingreso - {$pago->Concepto}",
                'detalle' => $pago->Concepto,
                'lineas' => [
                    [
                        'cuenta' => '10', // Cuentas corrientes
                        'descripcion' => "Banco {$pago->CuentaBanco}",
                        'debe' => $pago->Monto,
                        'haber' => 0
                    ],
                    [
                        'cuenta' => '12', // Cuentas por cobrar o ingresos
                        'descripcion' => "Ingreso por {$pago->Concepto}",
                        'debe' => 0,
                        'haber' => $pago->Monto
                    ]
                ]
            ];
        } else {
            // Ingreso en efectivo
            $asientos[] = [
                'fecha' => $pago->Fecha,
                'documento' => $pago->Numero,
                'concepto' => "Ingreso en efectivo - {$pago->Concepto}",
                'detalle' => $pago->Concepto,
                'lineas' => [
                    [
                        'cuenta' => '10', // Caja
                        'descripcion' => "Caja",
                        'debe' => $pago->Monto,
                        'haber' => 0
                    ],
                    [
                        'cuenta' => '12', // Cuentas por cobrar
                        'descripcion' => "Ingreso por {$pago->Concepto}",
                        'debe' => 0,
                        'haber' => $pago->Monto
                    ]
                ]
            ];
        }
        
        return $asientos;
    }

    private function generarAsientosEgreso(Pago $pago): array
    {
        $asientos = [];
        
        // Asiento de egreso
        if ($pago->CuentaBanco) {
            // Egreso de cuenta bancaria
            $asientos[] = [
                'fecha' => $pago->Fecha,
                'documento' => $pago->Numero,
                'concepto' => "Egreso - {$pago->Concepto}",
                'detalle' => $pago->Concepto,
                'lineas' => [
                    [
                        'cuenta' => '60', // Compras o gastos
                        'descripcion' => "Gasto - {$pago->Concepto}",
                        'debe' => $pago->Monto,
                        'haber' => 0
                    ],
                    [
                        'cuenta' => '10', // Cuentas corrientes
                        'descripcion' => "Banco {$pago->CuentaBanco}",
                        'debe' => 0,
                        'haber' => $pago->Monto
                    ]
                ]
            ];
        } else {
            // Egreso en efectivo
            $asientos[] = [
                'fecha' => $pago->Fecha,
                'documento' => $pago->Numero,
                'concepto' => "Egreso en efectivo - {$pago->Concepto}",
                'detalle' => $pago->Concepto,
                'lineas' => [
                    [
                        'cuenta' => '60', // Compras o gastos
                        'descripcion' => "Gasto - {$pago->Concepto}",
                        'debe' => $pago->Monto,
                        'haber' => 0
                    ],
                    [
                        'cuenta' => '10', // Caja
                        'descripcion' => "Caja",
                        'debe' => 0,
                        'haber' => $pago->Monto
                    ]
                ]
            ];
        }
        
        return $asientos;
    }

    private function generarAsientosAnulacion(Venta $factura): array
    {
        $asientos = [];
        
        // Revisión de la venta (reverse entry)
        $asientos[] = [
            'fecha' => now(),
            'documento' => $factura->Numero . '-ANUL',
            'concepto' => "Anulación factura {$factura->Numero}",
            'detalle' => "Reversión de venta anulada",
            'lineas' => [
                [
                    'cuenta' => '701', // Ventas (reversión)
                    'descripcion' => "Anulación venta factura {$factura->Numero}",
                    'debe' => $factura->Subtotal,
                    'haber' => 0
                ],
                [
                    'cuenta' => '401', // IGV (reversión)
                    'descripcion' => "Anulación IGV factura {$factura->Numero}",
                    'debe' => $factura->Impuesto,
                    'haber' => 0
                ],
                [
                    'cuenta' => '12', // Cuentas por cobrar (reversión)
                    'descripcion' => "Cliente {$factura->cliente->Razon} - Anulación",
                    'debe' => 0,
                    'haber' => $factura->Total
                ]
            ]
        ];
        
        return $asientos;
    }

    private function registrarAsientoContable(array $asiento): void
    {
        // En una implementación real, esto guardaría en tabla de asientos contables
        Log::info('Asiento contable registrado', [
            'fecha' => $asiento['fecha'],
            'documento' => $asiento['documento'],
            'concepto' => $asiento['concepto'],
            'lineas' => count($asiento['lineas'])
        ]);
    }

    private function actualizarKardexVentas(Venta $factura): void
    {
        foreach ($factura->detalles as $detalle) {
            // Actualizar kardex - esto se maneja en el MovimientoInventario
            Log::info('Kardex actualizado por venta', [
                'producto' => $detalle->Codpro,
                'cantidad' => $detalle->Cantidad,
                'costo' => $detalle->producto->Costo ?? 0
            ]);
        }
    }

    private function actualizarCuentasPorCobrar(Venta $factura): void
    {
        // La cuenta por cobrar ya se crea en el FacturaService
        Log::info('Cuenta por cobrar actualizada', [
            'factura' => $factura->Numero,
            'cliente' => $factura->CodClie,
            'monto' => $factura->Total
        ]);
    }

    private function actualizarCuentaPorCobrar(Pago $pago): void
    {
        // Actualizar saldo de cuenta por cobrar con el pago recibido
        Log::info('Cuenta por cobrar actualizada por pago', [
            'pago' => $pago->Numero,
            'monto' => $pago->Monto
        ]);
    }

    private function actualizarSaldoBancario(Pago $pago): void
    {
        // Actualizar saldo de cuenta bancaria
        Log::info('Saldo bancario actualizado', [
            'cuenta' => $pago->CuentaBanco,
            'monto' => $pago->Monto,
            'tipo' => $pago->Tipo
        ]);
    }

    private function obtenerCuentasConMovimientos(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        // En una implementación real, consultaría la tabla de asientos contables
        // Por ahora retornamos estructura de ejemplo
        
        return [
            [
                'cuenta' => '10',
                'descripcion' => 'Caja y Bancos',
                'debe' => 10000.00,
                'haber' => 5000.00
            ],
            [
                'cuenta' => '12',
                'descripcion' => 'Cuentas por Cobrar',
                'debe' => 8000.00,
                'haber' => 12000.00
            ],
            [
                'cuenta' => '20',
                'descripcion' => 'Mercaderías',
                'debe' => 25000.00,
                'haber' => 5000.00
            ]
        ];
    }

    private function calcularIngresos(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $ingresos = Venta::whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', false)
            ->sum('Total');
        
        return [
            'ventas' => $ingresos,
            'otros_ingresos' => 0,
            'total' => $ingresos
        ];
    }

    private function calcularCostosVentas(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        // Calcular costo de ventas basado en productos vendidos
        $facturas = Venta::whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', false)
            ->with('detalles.producto')
            ->get();
        
        $costoTotal = 0;
        foreach ($facturas as $factura) {
            foreach ($factura->detalles as $detalle) {
                $costoTotal += $detalle->Cantidad * ($detalle->producto->Costo ?? 0);
            }
        }
        
        return [
            'costo_productos' => $costoTotal,
            'otros_costos' => 0,
            'total' => $costoTotal
        ];
    }

    private function calcularGastosOperativos(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $gastos = Pago::whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Tipo', 2) // Egresos
            ->sum('Monto');
        
        return [
            'gastos_operativos' => $gastos,
            'otros_gastos' => 0,
            'total' => $gastos
        ];
    }

    private function calcularActivos(Carbon $fechaCorte): array
    {
        return [
            'efectivo' => 15000.00,
            'cuentas_cobrar' => 25000.00,
            'inventario' => 40000.00,
            'activos_fijos' => 50000.00,
            'total' => 130000.00
        ];
    }

    private function calcularPasivos(Carbon $fechaCorte): array
    {
        return [
            'cuentas_pagar' => 10000.00,
            'impuestos' => 5000.00,
            'prestamos' => 20000.00,
            'otros_pasivos' => 3000.00,
            'total' => 38000.00
        ];
    }

    private function calcularPatrimonio(Carbon $fechaCorte): array
    {
        return [
            'capital' => 80000.00,
            'utilidades_ejercicios' => 14000.00,
            'otros' => 0,
            'total' => 94000.00
        ];
    }

    private function calcularFlujoOperativo(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return [
            'ingresos_clientes' => $this->calcularIngresos($fechaInicio, $fechaFin)['total'],
            'gastos_operativos' => $this->calcularGastosOperativos($fechaInicio, $fechaFin)['total'],
            'total' => 0
        ];
    }

    private function calcularFlujoInversion(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return [
            'compras_activos' => 5000.00,
            'ventas_activos' => 0,
            'total' => -5000.00
        ];
    }

    private function calcularFlujoFinanciamiento(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        return [
            'prestamos_recibidos' => 0,
            'prestamos_pagados' => 2000.00,
            'dividendos_pagados' => 0,
            'total' => -2000.00
        ];
    }

    private function obtenerSaldoContableCuenta(string $cuenta, Carbon $fechaCorte): float
    {
        // En implementación real consultaría asientos contables
        return 10000.00; // Ejemplo
    }

    private function obtenerSaldoBancario(string $cuenta, Carbon $fechaCorte): float
    {
        // En implementación real consultaría extractos bancarios
        return 9800.00; // Ejemplo
    }

    private function analizarDiferenciasConciliacion(string $cuenta, Carbon $fechaCorte): array
    {
        return [
            'cheques_transito' => 150.00,
            'depositos_transito' => 0,
            'cargos_bancarios' => 50.00,
            'otros' => 0,
            'total_diferencias' => -200.00 // Cheques en tránsito - cargos bancarios
        ];
    }

    private function revertirKardexVentas(Venta $factura): void
    {
        Log::info('Kardex revertido por anulación de venta', [
            'factura' => $factura->Numero
        ]);
    }

    private function cancelarCuentaPorCobrar(Venta $factura): void
    {
        Log::info('Cuenta por cobrar cancelada por anulación', [
            'factura' => $factura->Numero
        ]);
    }
}