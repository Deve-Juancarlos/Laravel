<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CanjeService
{
    protected $connection = 'sqlsrv';
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    /**
     * Obtiene datos para la vista de creación de canje
     */
    public function getCreateData(): array
    {
        // Obtener clientes con facturas pendientes
        $clientes = DB::connection($this->connection)
            ->table('CtaCliente as cc')
            ->join('Clientes as c', 'cc.CodClie', '=', 'c.Codclie')
            ->where('cc.Saldo', '>', 0)
            ->where('cc.Tipo', '!=', 9) // Excluir letras
            ->select(
                'c.Codclie',
                'c.Razon',
                'c.Documento',
                DB::raw('SUM(cc.Saldo) as deuda_total'),
                DB::raw('COUNT(cc.Documento) as facturas_pendientes')
            )
            ->groupBy('c.Codclie', 'c.Razon', 'c.Documento')
            ->having(DB::raw('SUM(cc.Saldo)'), '>', 0)
            ->orderBy('c.Razon')
            ->get();

        return compact('clientes');
    }

    /**
     * Obtiene facturas pendientes de un cliente
     */
    public function getFacturasPendientes(int $codCliente): array
    {
        $facturas = DB::connection($this->connection)
            ->table('CtaCliente as cc')
            ->leftJoin('Doccab as dc', function($join) {
                $join->on('cc.Documento', '=', 'dc.Numero')
                     ->on('cc.Tipo', '=', 'dc.Tipo');
            })
            ->where('cc.CodClie', $codCliente)
            ->where('cc.Saldo', '>', 0)
            ->where('cc.Tipo', '!=', 9) // Excluir letras
            ->select(
                'cc.Documento',
                'cc.Tipo',
                'cc.FechaF',
                'cc.FechaV',
                'cc.Importe',
                'cc.Saldo',
                DB::raw('DATEDIFF(DAY, cc.FechaV, GETDATE()) as dias_vencidos')
            )
            ->orderBy('cc.FechaF')
            ->get();

        $cliente = DB::connection($this->connection)
            ->table('Clientes')
            ->where('Codclie', $codCliente)
            ->first();

        return [
            'facturas' => $facturas,
            'cliente' => $cliente,
            'total_deuda' => $facturas->sum('Saldo')
        ];
    }

    /**
     * Crea el canje: genera letras, salda facturas y registra todo
     */
    public function crearCanje(array $data)
    {
        return DB::connection($this->connection)->transaction(function () use ($data) {
            
            $codCliente = (int) $data['cod_cliente'];
            $facturasSeleccionadas = $data['facturas']; // ['DOC001' => 1500.50, 'DOC002' => 2400.00]
            $cantidadLetras = (int) $data['cantidad_letras'];
            $fechaPrimeraLetra = Carbon::parse($data['fecha_primera_letra']);
            $diasEntreCuotas = (int) ($data['dias_entre_cuotas'] ?? 30);
            $usuarioId = Auth::id();

            // Validaciones
            if (empty($facturasSeleccionadas)) {
                throw new Exception('Debe seleccionar al menos una factura.');
            }

            if ($cantidadLetras < 1) {
                throw new Exception('Debe generar al menos 1 letra.');
            }

            // Calcular monto total a canjear
            $montoTotal = array_sum($facturasSeleccionadas);
            $montoPorLetra = round($montoTotal / $cantidadLetras, 2);
            $resto = $montoTotal - ($montoPorLetra * $cantidadLetras);

            // Obtener datos del cliente
            $cliente = DB::connection($this->connection)
                ->table('Clientes')
                ->where('Codclie', $codCliente)
                ->first();

            if (!$cliente) {
                throw new Exception('Cliente no encontrado.');
            }

            // Obtener cuenta contable del banco (asumimos Letras por Cobrar)
            $cuentaBanco = DB::connection($this->connection)
                ->table('Bancos')
                ->where('Cuenta', 'LIKE', '%LETRA%')
                ->orWhere('Cuenta', 'LIKE', '%DESCUENTO%')
                ->first();

            $cuentaBancoId = $cuentaBanco ? $cuentaBanco->Cuenta : 'LETRAS_CARTERA';

            // Generar letras
            $letrasGeneradas = [];
            
            for ($i = 1; $i <= $cantidadLetras; $i++) {
                $montoLetra = $montoPorLetra;
                
                // Ajustar última letra para cubrir el resto
                if ($i == $cantidadLetras) {
                    $montoLetra += $resto;
                }

                $fechaVencimiento = $fechaPrimeraLetra->copy()->addDays(($i - 1) * $diasEntreCuotas);
                
                // Generar número de letra
                $numeroLetra = $this->generarNumeroLetra($codCliente, $i, $cantidadLetras);

                // Insertar en CtaCliente (como letra - Tipo 9)
                DB::connection($this->connection)
                    ->table('CtaCliente')
                    ->insert([
                        'Documento' => $numeroLetra,
                        'Tipo' => 9, // Tipo 9 = Letra
                        'CodClie' => $codCliente,
                        'FechaF' => now(),
                        'FechaV' => $fechaVencimiento,
                        'Importe' => $montoLetra,
                        'Saldo' => $montoLetra,
                    ]);

                // Insertar en DocLetra
                DB::connection($this->connection)
                    ->table('DocLetra')
                    ->insert([
                        'Numero' => $numeroLetra,
                        'CodBanco' => $cuentaBancoId,
                        'Codclie' => $codCliente,
                        'Vendedor' => $cliente->Vendedor ?? 0,
                        'Plazo' => $diasEntreCuotas,
                        'FecIni' => now(),
                        'FecVen' => $fechaVencimiento,
                        'Monto' => $montoLetra,
                        'Estado' => 1, // 1 = Activa
                        'Anulado' => 0,
                    ]);

                $letrasGeneradas[] = [
                    'numero' => $numeroLetra,
                    'monto' => $montoLetra,
                    'vencimiento' => $fechaVencimiento->format('Y-m-d')
                ];

                // Registrar en Canjes_Detalle (por cada factura)
                foreach ($facturasSeleccionadas as $factura => $monto) {
                    DB::connection($this->connection)
                        ->table('Canjes_Detalle')
                        ->insert([
                            'cod_cliente' => $codCliente,
                            'factura_origen' => $factura,
                            'letra_destino' => $numeroLetra,
                            'fecha_canje' => now(),
                            'usuario_id' => $usuarioId,
                        ]);
                }
            }

            // Saldar facturas originales
            foreach ($facturasSeleccionadas as $factura => $monto) {
                DB::connection($this->connection)
                    ->table('CtaCliente')
                    ->where('Documento', $factura)
                    ->where('CodClie', $codCliente)
                    ->update(['Saldo' => 0]);
            }

            // Crear asiento contable
            $numeroAsiento = $this->cajaService->obtenerSiguienteNumeroAsiento(now()->format('Y-m-d'));
            
            $asientoId = DB::connection($this->connection)
                ->table('libro_diario')
                ->insertGetId([
                    'numero' => $numeroAsiento,
                    'fecha' => now(),
                    'glosa' => "Canje de facturas por {$cantidadLetras} letra(s) - Cliente: {$cliente->Razon}",
                    'total_debe' => $montoTotal,
                    'total_haber' => $montoTotal,
                    'balanceado' => 1,
                    'estado' => 'ACTIVO',
                    'usuario_id' => $usuarioId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // Detalle: CARGO - Letras por Cobrar (123201)
            DB::connection($this->connection)
                ->table('libro_diario_detalles')
                ->insert([
                    'asiento_id' => $asientoId,
                    'cuenta_contable' => '123201', // Letras por Cobrar
                    'debe' => $montoTotal,
                    'haber' => 0,
                    'concepto' => "Canje de facturas por letras - {$cantidadLetras} letra(s)",
                    'documento_referencia' => implode(', ', array_keys($facturasSeleccionadas)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // Detalle: ABONO - Cuentas por Cobrar Comerciales (121201)
            DB::connection($this->connection)
                ->table('libro_diario_detalles')
                ->insert([
                    'asiento_id' => $asientoId,
                    'cuenta_contable' => '121201', // Facturas por Cobrar
                    'debe' => 0,
                    'haber' => $montoTotal,
                    'concepto' => "Salida de facturas por canje",
                    'documento_referencia' => implode(', ', array_keys($facturasSeleccionadas)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            Log::info("Canje creado exitosamente", [
                'cliente' => $codCliente,
                'facturas' => count($facturasSeleccionadas),
                'letras' => $cantidadLetras,
                'monto' => $montoTotal,
                'asiento' => $numeroAsiento
            ]);

            return [
                'success' => true,
                'letras' => $letrasGeneradas,
                'asiento' => $numeroAsiento,
                'monto_total' => $montoTotal
            ];
        });
    }

    /**
     * Genera número de letra único
     */
    protected function generarNumeroLetra(int $codCliente, int $cuota, int $totalCuotas): string
    {
        $anio = date('Y');
        $mes = date('m');
        
        // Buscar último número de letra del mes
        $ultimaLetra = DB::connection($this->connection)
            ->table('CtaCliente')
            ->where('Tipo', 9)
            ->where('Documento', 'LIKE', "LET-{$anio}{$mes}%")
            ->orderBy('Documento', 'desc')
            ->first();

        $correlativo = 1;
        if ($ultimaLetra) {
            $partes = explode('-', $ultimaLetra->Documento);
            if (isset($partes[1])) {
                $correlativo = (int)substr($partes[1], 6) + 1;
            }
        }

        $numeroBase = str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        
        return "LET-{$anio}{$mes}{$numeroBase}-{$cuota}/{$totalCuotas}";
    }

    /**
     * Lista de canjes realizados
     */
    public function getIndexData(array $filters): array
    {
        $query = DB::connection($this->connection)
            ->table('Canjes_Detalle as cd')
            ->join('Clientes as c', 'cd.cod_cliente', '=', 'c.Codclie')
            ->leftJoin('accesoweb as u', 'cd.usuario_id', '=', 'u.id')
            ->select(
                'cd.id',
                'cd.cod_cliente',
                'c.Razon as cliente_nombre',
                'c.Documento as cliente_doc',
                'cd.factura_origen',
                'cd.letra_destino',
                'cd.fecha_canje',
                'u.usuario as usuario_nombre'
            );

        if (!empty($filters['cliente'])) {
            $query->where('cd.cod_cliente', $filters['cliente']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('cd.fecha_canje', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('cd.fecha_canje', '<=', $filters['fecha_hasta']);
        }

        $canjes = $query->orderBy('cd.fecha_canje', 'desc')
            ->paginate(50);

        return compact('canjes');
    }
}