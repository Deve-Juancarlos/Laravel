<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class ContabilidadService
{
    protected $connection = 'sqlsrv';

    
    protected function generarNumeroAsiento(Carbon $fecha): string
    {
        // ... (Este método ya lo tienes, no cambia) ...
        $prefijo = 'A-' . $fecha->format('Ymd') . '-';
        
        $ultimoAsiento = DB::connection($this->connection)
            ->table('libro_diario')
            ->where('numero', 'LIKE', $prefijo . '%')
            ->orderBy('numero', 'desc')
            ->value('numero');

        $correlativo = 0;
        if ($ultimoAsiento) {
            $correlativo = (int)substr($ultimoAsiento, -4);
        }

        $nuevoCorrelativo = str_pad($correlativo + 1, 4, '0', STR_PAD_LEFT);

        return $prefijo . $nuevoCorrelativo;
    }

    /**
     * Registra el Asiento de Venta y su Costo de Venta asociado.
     *
     * @param string $numeroDoc Número de Factura/Boleta (F001-0001)
     * @param int $tipoDoc Tipo de Documento (1=Factura, 3=Boleta)
     * @param object $cliente Objeto del cliente
     * @param array $totales Array con ['subtotal', 'igv', 'total']
     * @param float $totalCostoVenta El costo total de los productos vendidos
     * @param int $userId ID del usuario que registra
     * * @return int ID del asiento creado en libro_diario
     * @throws \Exception
     */
    public function registrarAsientoVenta(
        string $numeroDoc,
        int $tipoDoc,
        object $cliente,
        array $totales,
        float $totalCostoVenta,
        int $userId
    ): int {

        $fechaHoy = Carbon::now();
        $docNombre = $tipoDoc == 1 ? 'Factura' : 'Boleta';
        
        // 1. Validar que los montos sean coherentes
        if ($totales['total'] <= 0 || $totalCostoVenta < 0) {
            Log::warning("Asiento contable omitido por montos inválidos.", ['doc' => $numeroDoc]);
            // No lanzamos excepción, solo omitimos el asiento si la venta es 0
            return 0; 
        }

        // Cargar cuentas desde el archivo de configuración
        $ctaCobrar = $tipoDoc == 1 
            ? Config::get('contabilidad.cuentas.ventas.factura_por_cobrar') 
            : Config::get('contabilidad.cuentas.ventas.boleta_por_cobrar');
        $ctaIgv     = Config::get('contabilidad.cuentas.ventas.igv_por_pagar');
        $ctaVenta   = Config::get('contabilidad.cuentas.ventas.ingreso_por_venta');
        $ctaCosto   = Config::get('contabilidad.cuentas.costos.costo_de_venta');
        $ctaMercaderia = Config::get('contabilidad.cuentas.costos.mercaderias');
        
        // 2. Crear la Glosa
        $glosa = "Provisión Venta y Costo de Venta - {$docNombre} {$numeroDoc} - Cliente: {$cliente->Razon}";
        
        // 3. Totales del Asiento
        $totalDebe  = round($totales['total'] + $totalCostoVenta, 2);
        $totalHaber = round($totales['total'] + $totalCostoVenta, 2);

        try {
            // 4. Insertar la cabecera del Asiento (libro_diario)
            // NOTA: Usamos getPdo()->lastInsertId() porque 'id' es IDENTITY
            
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $this->generarNumeroAsiento($fechaHoy),
                $fechaHoy,
                substr($glosa, 0, 500), // Limitar glosa a 500 chars
                $totalDebe,
                $totalHaber,
                1, // Balanceado
                'ACTIVO',
                $userId,
                $fechaHoy,
                $fechaHoy
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) {
                throw new \Exception("No se pudo obtener el ID del asiento insertado.");
            }

            // 5. Insertar los detalles del Asiento (libro_diario_detalles)
            
            $detallesAsiento = [
                // --- Asiento de Venta (Provisión) ---
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaCobrar,
                    'debe' => $totales['total'], 'haber' => 0,
                    'concepto' => "Provisión {$docNombre} {$numeroDoc}"
                ],
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaIgv,
                    'debe' => 0, 'haber' => $totales['igv'],
                    'concepto' => "IGV {$docNombre} {$numeroDoc}"
                ],
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaVenta,
                    'debe' => 0, 'haber' => $totales['subtotal'],
                    'concepto' => "Base Imponible {$docNombre} {$numeroDoc}"
                ],
                
                // --- Asiento de Costo de Venta ---
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaCosto,
                    'debe' => $totalCostoVenta, 'haber' => 0,
                    'concepto' => "Costo de Venta {$docNombre} {$numeroDoc}"
                ],
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaMercaderia,
                    'debe' => 0, 'haber' => $totalCostoVenta,
                    'concepto' => "Salida Mercadería {$docNombre} {$numeroDoc}"
                ],
            ];
            
            // Filtrar movimientos de 0 (ej. ventas exoneradas sin IGV o costo 0)
            $detallesFiltrados = array_filter($detallesAsiento, function($d) {
                return $d['debe'] > 0 || $d['haber'] > 0;
            });

            // Añadir timestamps a los detalles
            $now = now();
            foreach ($detallesFiltrados as &$detalle) {
                $detalle['created_at'] = $now;
                $detalle['updated_at'] = $now;
                $detalle['documento_referencia'] = $numeroDoc;

                // CASTING CORRECTO
                $detalle['debe'] = (float) $detalle['debe'];
                $detalle['haber'] = (float) $detalle['haber'];
                $detalle['cuenta_contable'] = (string) $detalle['cuenta_contable'];
                $detalle['concepto'] = (string) $detalle['concepto'];
            }
            unset($detalle);


            DB::connection($this->connection)->table('libro_diario_detalles')->insert($detallesFiltrados);
            
            Log::info("Motor Contable: Asiento {$asientoId} creado para Venta {$numeroDoc}");

            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Venta): " . $e->getMessage(), [
                'doc' => $numeroDoc, 
                'trace' => $e->getTraceAsString()
            ]);
            // Lanzamos la excepción para que la transacción principal (del Controller) haga rollback
            throw new \Exception("Error al registrar asiento contable: " . $e->getMessage());
        }
    }

    public function registrarAsientoCobranza(
        string $planillaNumero,
        object $cliente,
        array $pago,
        float $montoTotalPagado,
        float $montoTotalAplicado,
        float $montoAdelanto,
        int $userId
    ): int {
        $fechaPago = Carbon::parse($pago['fecha_pago']);

        // 1. --- OBTENER CUENTAS ---

        // 1A. (DEBE) Obtener la cuenta contable del banco de destino
        $cuentaBanco = DB::connection($this->connection)->table('Bancos')
                        ->where('Cuenta', $pago['cuenta_destino'])
                        ->value('cuenta_contable'); // <-- Usamos la nueva columna
        
        if (!$cuentaBanco) {
            // Si el banco no tiene cuenta asignada, usamos la de por defecto
            Log::warning("Contabilidad: Banco {$pago['cuenta_destino']} sin cuenta contable. Usando default.");
            $cuentaBanco = ($pago['metodo_pago'] == 'efectivo')
                ? Config::get('contabilidad.cuentas.cobranzas.caja_default')
                : Config::get('contabilidad.cuentas.cobranzas.banco_default');
        }

        // 1B. (HABER) Obtener las cuentas de contrapartida
        $ctaPorCobrar = Config::get('contabilidad.cuentas.cobranzas.factura_por_cobrar'); // Asumimos 121201 para todo
        $ctaAnticipo  = Config::get('contabilidad.cuentas.cobranzas.anticipo_clientes');

        
        // 2. --- Glosa y Totales ---
        $glosa = "Cobranza s/ Planilla {$planillaNumero} - Cliente: {$cliente->Razon}";
        $totalDebe  = round($montoTotalPagado, 2);
        $totalHaber = round($montoTotalAplicado + $montoAdelanto, 2); // Debe ser igual al totalDebe

        if (abs($totalDebe - $totalHaber) > 0.01) {
            throw new \Exception("Asiento de Cobranza desbalanceado. Debe: {$totalDebe}, Haber: {$totalHaber}");
        }
        if ($totalDebe <= 0) {
            Log::warning("Asiento de Cobranza omitido por monto 0.", ['planilla' => $planillaNumero]);
            return 0;
        }

        try {
            // 3. --- Insertar Cabecera (libro_diario) ---
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $this->generarNumeroAsiento($fechaPago),
                $fechaPago,
                substr($glosa, 0, 500),
                (float) $totalDebe,
                (float) $totalHaber,
                1, 'ACTIVO', $userId,
                now(), now()
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) throw new \Exception("No se pudo obtener el ID del asiento de cobranza.");

            // 4. --- Insertar Detalles (libro_diario_detalles) ---
            $detallesAsiento = [];

            // 4A. El DEBE (Ingreso del dinero)
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $cuentaBanco,
                'debe' => (float) $totalDebe, 'haber' => 0.0,
                'concepto' => "Ingreso por Cobranza Planilla {$planillaNumero}"
            ];

            // 4B. El HABER (Cancelación de Deuda)
            if ($montoTotalAplicado > 0) {
                $detallesAsiento[] = [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaPorCobrar,
                    'debe' => 0.0, 'haber' => (float) $montoTotalAplicado,
                    'concepto' => "Cancelación Facturas Planilla {$planillaNumero}"
                ];
            }
            
            // 4C. El HABER (Anticipo)
            if ($montoAdelanto > 0) {
                 $detallesAsiento[] = [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaAnticipo,
                    'debe' => 0.0, 'haber' => (float) $montoAdelanto,
                    'concepto' => "Anticipo Cliente Planilla {$planillaNumero}"
                ];
            }

            // 5. --- Guardar Detalles ---
            $now = now();
            foreach ($detallesAsiento as &$detalle) {
                $detalle['created_at'] = $now;
                $detalle['updated_at'] = $now;
                $detalle['documento_referencia'] = $planillaNumero;
            }

            DB::connection($this->connection)->table('libro_diario_detalles')->insert($detallesAsiento);
            
            Log::info("Motor Contable: Asiento {$asientoId} creado para Cobranza {$planillaNumero}");

            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Cobranza): " . $e->getMessage(), [
                'planilla' => $planillaNumero, 
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Error al registrar asiento contable de cobranza: " . $e->getMessage());
        }
    }

    public function registrarAsientoCompra(
        string $nroFacturaProveedor,
        object $proveedor,
        float $subtotal,
        float $igv,
        float $total,
        Carbon $fechaFactura,
        int $userId
    ): int {
        
        // 1. --- OBTENER CUENTAS ---
        $ctaCompra     = Config::get('contabilidad.cuentas.compras.compras_mercaderia');
        $ctaIgv        = Config::get('contabilidad.cuentas.compras.igv_compras');
        $ctaPorPagar   = Config::get('contabilidad.cuentas.compras.facturas_por_pagar');
        $ctaAlmacen    = Config::get('contabilidad.cuentas.compras.almacen_mercaderia');
        $ctaVariacion  = Config::get('contabilidad.cuentas.compras.variacion_stock');

        // 2. --- Glosa y Totales ---
        $glosa = "Provisión Compra s/ Factura {$nroFacturaProveedor} - Prov: {$proveedor->RazonSocial}";
        
        // El asiento contable completo (provisión + destino) debe sumar:
        $totalDebe  = round($subtotal + $igv + $subtotal, 2);
        $totalHaber = round($total + $subtotal, 2);

        if (abs($totalDebe - $totalHaber) > 0.01) {
            throw new \Exception("Asiento de Compra desbalanceado. Debe: {$totalDebe}, Haber: {$totalHaber}");
        }
        if ($totalDebe <= 0) {
            Log::warning("Asiento de Compra omitido por monto 0.", ['factura' => $nroFacturaProveedor]);
            return 0;
        }

        try {
            // 3. --- Insertar Cabecera (libro_diario) ---
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $this->generarNumeroAsiento($fechaFactura),
                $fechaFactura,
                substr($glosa, 0, 500),
                (float) $totalDebe,
                (float) $totalHaber,
                1, 'ACTIVO', $userId,
                now(), now()
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) throw new \Exception("No se pudo obtener el ID del asiento de compra.");

            // 4. --- Insertar Detalles (libro_diario_detalles) ---
            $detallesAsiento = [];

            // --- Asiento 1: Provisión (60/40/42) ---
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $ctaCompra,
                'debe' => (float) $subtotal, 'haber' => 0.0,
                'concepto' => "Compra de Mercadería s/ Factura {$nroFacturaProveedor}"
            ];
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $ctaIgv,
                'debe' => (float) $igv, 'haber' => 0.0,
                'concepto' => "IGV Crédito Fiscal s/ Factura {$nroFacturaProveedor}"
            ];
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $ctaPorPagar,
                'debe' => 0.0, 'haber' => (float) $total,
                'concepto' => "Provisión por Pagar s/ Factura {$nroFacturaProveedor}"
            ];
            
            // --- Asiento 2: Destino (20/61) ---
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $ctaAlmacen,
                'debe' => (float) $subtotal, 'haber' => 0.0,
                'concepto' => "Ingreso a Almacén s/ Factura {$nroFacturaProveedor}"
            ];
            $detallesAsiento[] = [
                'asiento_id' => $asientoId, 'cuenta_contable' => $ctaVariacion,
                'debe' => 0.0, 'haber' => (float) $subtotal,
                'concepto' => "Variación de Existencias s/ Factura {$nroFacturaProveedor}"
            ];

            // 5. --- Guardar Detalles ---
            $now = now();
            foreach ($detallesAsiento as &$detalle) {
                // Filtramos líneas con 0 (ej. compras exoneradas)
                if ($detalle['debe'] > 0 || $detalle['haber'] > 0) {
                    $detalle['created_at'] = $now;
                    $detalle['updated_at'] = $now;
                    $detalle['documento_referencia'] = $nroFacturaProveedor;
                } else {
                    $detalle = null; // Marcamos para eliminar
                }
            }
            $detallesFiltrados = array_filter($detallesAsiento);

            DB::connection($this->connection)->table('libro_diario_detalles')->insert($detallesFiltrados);
            
            Log::info("Motor Contable: Asiento {$asientoId} creado para Compra {$nroFacturaProveedor}");

            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Compra): " . $e->getMessage(), [
                'factura' => $nroFacturaProveedor, 
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Error al registrar asiento contable de compra: " . $e->getMessage());
        }
    }

    public function registrarAsientoPagoProveedor(
        string $nroOperacion,
        object $proveedor,
        string $cuentaBancoId,
        float $montoTotalPagado,
        Carbon $fechaPago,
        int $userId
    ): int {
        
        // 1. --- OBTENER CUENTAS ---

        // 1A. (HABER) Obtener la cuenta contable del banco de origen (de donde sale el dinero)
        $cuentaBancoContable = DB::connection($this->connection)->table('Bancos')
                        ->where('Cuenta', $cuentaBancoId)
                        ->value('cuenta_contable'); // <-- Usamos la columna que creamos
        
        if (!$cuentaBancoContable) {
            Log::warning("Contabilidad: Banco {$cuentaBancoId} sin cuenta contable. Usando default.");
            $cuentaBancoContable = Config::get('contabilidad.cuentas.cobranzas.banco_default'); // Re-usamos el default
        }

        // 1B. (DEBE) Obtener la cuenta de contrapartida (la deuda que cancelamos)
        $ctaPorPagar = Config::get('contabilidad.cuentas.compras.facturas_por_pagar'); // 421201

        
        // 2. --- Glosa y Totales ---
        $glosa = "Pago a Proveedor s/ Op. {$nroOperacion} - Prov: {$proveedor->RazonSocial}";
        $totalDebe  = round($montoTotalPagado, 2);
        $totalHaber = round($montoTotalPagado, 2);

        if ($totalDebe <= 0) return 0; // Omitir asiento si es 0

        try {
            // 3. --- Insertar Cabecera (libro_diario) ---
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $this->generarNumeroAsiento($fechaPago),
                $fechaPago,
                substr($glosa, 0, 500),
                (float) $totalDebe,
                (float) $totalHaber,
                1, 'ACTIVO', $userId,
                now(), now()
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) throw new \Exception("No se pudo obtener el ID del asiento de pago.");

            // 4. --- Insertar Detalles (libro_diario_detalles) ---
            $detallesAsiento = [
                // 4A. El DEBE (Cancelación de la deuda 42)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaPorPagar,
                    'debe' => (float) $totalDebe, 'haber' => 0.0,
                    'concepto' => "Cancelación deuda Proveedor s/ Op. {$nroOperacion}"
                ],
                
                // 4B. El HABER (Salida del dinero del banco 10)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $cuentaBancoContable,
                    'debe' => 0.0, 'haber' => (float) $totalHaber,
                    'concepto' => "Egreso de Banco s/ Op. {$nroOperacion}"
                ]
            ];

            // 5. --- Guardar Detalles ---
            $now = now();
            foreach ($detallesAsiento as &$detalle) {
                $detalle['created_at'] = $now;
                $detalle['updated_at'] = $now;
                $detalle['documento_referencia'] = $nroOperacion;
            }

            DB::connection($this->connection)->table('libro_diario_detalles')->insert($detallesAsiento);
            Log::info("Motor Contable: Asiento {$asientoId} creado para Pago a Proveedor {$nroOperacion}");
            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Pago Proveedor): " . $e->getMessage(), ['op' => $nroOperacion]);
            throw new \Exception("Error al registrar asiento contable de pago: " . $e->getMessage());
        }
    }

    public function registrarAsientoNotaCredito(
        string $numeroNC,
        array $carrito,
        object $facturaOriginal,
        int $userId
    ): int {
        
        $total    = (float) $carrito['totales']['total'];
        $subtotal = (float) $carrito['totales']['subtotal'];
        $igv      = (float) $carrito['totales']['igv'];
        $fechaHoy = Carbon::now();

        // 1. --- OBTENER CUENTAS ---

        // 1A. (DEBE) Determinar la cuenta de cargo (704 si es devolución, 67 si es descuento)
        if ($carrito['tipo_operacion'] == 'devolucion') {
            $ctaCargo = Config::get('contabilidad.cuentas.notas_credito.devolucion_ventas');
        } else {
            $ctaCargo = Config::get('contabilidad.cuentas.notas_credito.descuento_ventas');
        }
        
        // 1B. (DEBE) Cuenta de IGV
        $ctaIgv = Config::get('contabilidad.cuentas.notas_credito.igv_nc');
        
        // 1C. (HABER) Cuenta por Cobrar (la 12)
        $ctaPorCobrar = Config::get('contabilidad.cuentas.notas_credito.cuenta_por_cobrar');
        
        // 2. --- Glosa y Totales ---
        $glosa = "Por la NC {$numeroNC} (Afecta Fact. {$facturaOriginal->Numero}) - Motivo: {$carrito['motivo']}";
        $totalDebe  = round($subtotal + $igv, 2);
        $totalHaber = round($total, 2);

        if (abs($totalDebe - $totalHaber) > 0.01) {
            throw new \Exception("Asiento de NC desbalanceado. Debe: {$totalDebe}, Haber: {$totalHaber}");
        }
        if ($totalDebe <= 0) {
            Log::warning("Asiento de NC omitido por monto 0.", ['nc' => $numeroNC]);
            return 0;
        }

        try {
            // 3. --- Insertar Cabecera (libro_diario) ---
            // ¡Usamos nuestro generador de correlativos consistente!
            $numeroAsiento = $this->generarNumeroAsiento($fechaHoy);
            
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $numeroAsiento,
                $fechaHoy,
                substr($glosa, 0, 500),
                (float) $totalDebe,
                (float) $totalHaber,
                1, 'ACTIVO', $userId,
                now(), now()
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) throw new \Exception("No se pudo obtener el ID del asiento de NC.");

            // 4. --- Insertar Detalles (libro_diario_detalles) ---
            $detallesAsiento = [
                // 4A. El DEBE (Reversión de Venta o Gasto por Descuento)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaCargo,
                    'debe' => (float) $subtotal, 'haber' => 0.0,
                    'concepto' => "Reversión/Desc. s/ Fact. {$facturaOriginal->Numero}"
                ],
                // 4B. El DEBE (Reversión de IGV)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaIgv,
                    'debe' => (float) $igv, 'haber' => 0.0,
                    'concepto' => "Reversión IGV s/ Fact. {$facturaOriginal->Numero}"
                ],
                // 4C. El HABER (Reducción de la Cuenta por Cobrar)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaPorCobrar,
                    'debe' => 0.0, 'haber' => (float) $total,
                    'concepto' => "NC {$numeroNC} aplicada a Fact. {$facturaOriginal->Numero}"
                ]
            ];
            
            // 5. --- Guardar Detalles ---
            $now = now();
            foreach ($detallesAsiento as &$detalle) {
                if ($detalle['debe'] > 0 || $detalle['haber'] > 0) {
                    $detalle['created_at'] = $now;
                    $detalle['updated_at'] = $now;
                    $detalle['documento_referencia'] = $numeroNC;
                } else {
                    $detalle = null; // No insertar líneas con 0
                }
            }

            DB::connection($this->connection)->table('libro_diario_detalles')->insert(array_filter($detallesAsiento));
            Log::info("Motor Contable: Asiento {$asientoId} creado para Nota de Crédito {$numeroNC}");
            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Nota de Crédito): " . $e->getMessage(), ['nc' => $numeroNC]);
            throw new \Exception("Error al registrar asiento contable de NC: " . $e->getMessage());
        }
    }

    public function registrarAsientoAnulacionCobranza(
        object $planilla,
        object $bancoIngreso,
        float $totalAnulado,
        string $motivo,
        int $userId
    ): int {
        
        $fechaAnulacion = Carbon::now();

        // 1. --- OBTENER CUENTAS ---
        $ctaCliente = Config::get('contabilidad.cuentas.anulaciones.cliente_por_cobrar');
        
        // Buscamos la cuenta contable del banco que se usó en el ingreso
        $cuentaBancoContable = DB::connection($this->connection)->table('Bancos')
                        ->where('Cuenta', $bancoIngreso->Cuenta)
                        ->value('cuenta_contable');
        
        if (!$cuentaBancoContable) {
            $cuentaBancoContable = Config::get('contabilidad.cuentas.cobranzas.banco_default');
        }

        // 2. --- Glosa y Totales ---
        $glosa = "ANULACIÓN de Cobranza. Planilla: {$planilla->Serie}-{$planilla->Numero}. Motivo: {$motivo}";
        $totalDebe  = round($totalAnulado, 2);
        $totalHaber = round($totalAnulado, 2);

        if ($totalDebe <= 0) return 0; // No hacer nada si el monto es 0

        try {
            // 3. --- Insertar Cabecera (libro_diario) ---
            $numeroAsiento = $this->generarNumeroAsiento($fechaAnulacion);
            
            $sqlCabecera = "
                INSERT INTO libro_diario 
                (numero, fecha, glosa, total_debe, total_haber, balanceado, estado, usuario_id, created_at, updated_at) 
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $paramsCabecera = [
                $numeroAsiento,
                $fechaAnulacion,
                substr($glosa, 0, 500),
                (float) $totalDebe,
                (float) $totalHaber,
                1, 'ACTIVO', $userId,
                now(), now()
            ];
            
            $resultado = DB::connection($this->connection)->select($sqlCabecera, $paramsCabecera);
            $asientoId = $resultado[0]->id;

            if (!$asientoId) throw new \Exception("No se pudo obtener el ID del asiento de anulación.");

            // 4. --- Insertar Detalles (El asiento inverso 12 / 10) ---
            $detallesAsiento = [
                // 4A. El DEBE (Restauramos la deuda al Cliente)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $ctaCliente,
                    'debe' => (float) $totalDebe, 'haber' => 0.0,
                    'concepto' => "Extorno s/ Planilla {$planilla->Serie}-{$planilla->Numero}"
                ],
                // 4B. El HABER (Revertimos el ingreso del banco)
                [
                    'asiento_id' => $asientoId, 'cuenta_contable' => $cuentaBancoContable,
                    'debe' => 0.0, 'haber' => (float) $totalHaber,
                    'concepto' => "Extorno de Banco s/ Planilla {$planilla->Serie}-{$planilla->Numero}"
                ]
            ];

            // 5. --- Guardar Detalles ---
            $now = now();
            foreach ($detallesAsiento as &$detalle) {
                $detalle['created_at'] = $now;
                $detalle['updated_at'] = $now;
                $detalle['documento_referencia'] = 'ANUL-' . $planilla->Serie . '-' . $planilla->Numero;
            }

            DB::connection($this->connection)->table('libro_diario_detalles')->insert($detallesAsiento);
            Log::info("Motor Contable: Asiento {$asientoId} creado para Anulación de Cobranza {$planilla->Serie}-{$planilla->Numero}");
            return $asientoId;

        } catch (\Exception $e) {
            Log::error("Error en Motor Contable (Anulación Cobranza): " . $e->getMessage());
            throw new \Exception("Error al registrar asiento contable de anulación: " . $e->getMessage());
        }
    }
}