<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\VentaCarritoService;
use App\Services\ContabilidadService;
use App\Services\FacturaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberToWords;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnviarDocumentoMail;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturacionController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $carritoService;
    protected $contabilidadService;
    protected $facturaService;

    public function __construct(
        VentaCarritoService $carritoService,
        ContabilidadService $contabilidadService,
        FacturaService $facturaService
    ) {
        $this->middleware('auth');
        $this->carritoService = $carritoService;
        $this->contabilidadService = $contabilidadService;
        $this->facturaService = $facturaService;
    }

    /**
     * =========================================================
     * LISTADO DE FACTURAS
     * =========================================================
     */
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)
            ->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereIn('dc.Tipo', [1, 3]); // 1 = Factura, 3 = Boleta (interno)

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('c.Razon', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('dc.Numero', 'like', '%' . $request->input('q') . '%');
            });
        }

        if ($request->filled('estado') && $request->input('estado') == 'anuladas') {
            $query->where('dc.Eliminado', 1);
        } else {
            $query->where('dc.Eliminado', 0);
        }

        $facturas = $query->select(
            'dc.Numero',
            'dc.Tipo',
            'dc.Fecha',
            'dc.FechaV',
            'c.Razon as Cliente',
            'dc.Total',
            'dc.Moneda',
            'dc.Eliminado'
        )
            ->orderBy('dc.Fecha', 'desc')
            ->orderBy('dc.Numero', 'desc')
            ->paginate(25);

        $estadisticas = [
            'ventas_hoy' => 0,
            'total_mes' => 0,
            'ventas_anuladas' => 0,
        ];

        return view('ventas.index', [
            'facturas' => $facturas,
            'filtros' => $request->only(['q', 'estado']),
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * =========================================================
     * NUEVA VENTA - FORMULARIO
     * =========================================================
     */
    public function create(Request $request)
    {
        $clienteId = $request->query('cliente_id');
        $carrito = $this->carritoService->get();

        if ($clienteId) {
            $cliente = DB::connection($this->connection)
                ->table('Clientes')
                ->where('Codclie', $clienteId)
                ->first();

            if ($cliente) {
                $carrito = $this->carritoService->iniciar($cliente);

                // Determinar tipo de documento interno por defecto
                // 1 = Factura, 3 = Boleta
                $tipoDocAutomatico = 1; // Factura por defecto

                if ($cliente->tipoDoc == '1' || (isset($cliente->Documento) && strlen(trim($cliente->Documento)) === 8)) {
                    $tipoDocAutomatico = 3; // Boleta para DNI
                }

                $carrito = $this->carritoService->actualizarPago([
                    'tipo_doc'   => $tipoDocAutomatico,
                    'condicion'  => 'contado',
                    'fecha_venc' => now()->addDays(30)->format('Y-m-d'),
                    'vendedor_id'=> $cliente->Vendedor ?? null,
                    'moneda'     => 1,
                ]);
            }
        }

        $vendedores = DB::connection($this->connection)
            ->table('Empleados')
            ->orderBy('Nombre')
            ->get();

        // Tipos de documento desde TiposDocumentoSUNAT (01,03,07,08)
        $tiposDoc = DB::connection($this->connection)
            ->table('TiposDocumentoSUNAT')
            ->whereIn('Codigo', ['01', '03', '07', '08'])
            ->select('Codigo', 'Descripcion')
            ->get();

        return view('ventas.crear', [
            'carrito'    => $carrito,
            'vendedores' => $vendedores,
            'tiposDoc'   => $tiposDoc,
        ]);
    }

    /**
     * =========================================================
     * GUARDAR VENTA
     * =========================================================
     */
    public function store(Request $request)
    {
        $carrito = $this->carritoService->get();

        if (!$carrito || $carrito['items']->isEmpty()) {
            return redirect()
                ->route('contador.facturas.create')
                ->with('error', 'El carrito está vacío.');
        }

        DB::connection($this->connection)->beginTransaction();

        try {
            // Tipo interno (1=Factura, 3=Boleta, 7=NC, 8=ND)
            $tipoDoc = $carrito['pago']['tipo_doc'];

            // Serie por tipo interno
            $serie = $tipoDoc == 1 ? 'F001' : 'B001';

            // Código SUNAT
            $tipoDocumentoSunat = $this->mapearTipoDocumentoSunat($tipoDoc);

            // Obtener último número
            $ultimoNum = DB::connection($this->connection)
                ->table('Doccab')
                ->where('Tipo', $tipoDoc)
                ->where('Numero', 'like', $serie . '-%')
                ->orderBy('Numero', 'desc')
                ->value('Numero');

            $ultimoNumInt = $ultimoNum ? (int) substr($ultimoNum, strpos($ultimoNum, '-') + 1) : 0;
            $nuevoNumInt  = $ultimoNumInt + 1;
            $numeroDoc    = $serie . '-' . str_pad($nuevoNumInt, 8, '0', STR_PAD_LEFT);

            // Insertar cabecera
            DB::connection($this->connection)
                ->table('Doccab')
                ->insert([
                    'Numero'             => $numeroDoc,
                    'Tipo'               => $tipoDoc,
                    'tipo_documento_sunat' => $tipoDocumentoSunat,
                    'serie_sunat'        => $serie,
                    'correlativo_sunat'  => $nuevoNumInt,
                    'CodClie'            => $carrito['cliente']->Codclie,
                    'Fecha'              => now(),
                    'Dias'               => $carrito['pago']['condicion'] == 'credito'
                        ? max(0, (int) Carbon::parse($carrito['pago']['fecha_venc'])->diffInDays(now()))
                        : 0,
                    'FechaV'             => $carrito['pago']['condicion'] == 'credito'
                        ? $carrito['pago']['fecha_venc']
                        : now(),
                    'Subtotal'           => $carrito['totales']['subtotal'],
                    'Igv'                => $carrito['totales']['igv'],
                    'Total'              => $carrito['totales']['total'],
                    'Moneda'             => $carrito['pago']['moneda'],
                    'Vendedor'           => $carrito['pago']['vendedor_id'],
                    'Eliminado'          => 0,
                    'Impreso'            => 0,
                    'Usuario'            => Auth::user()->usuario,
                    'estado_sunat'       => 'PENDIENTE',
                ]);

            // Detalle y stock
            $totalCostoVenta = 0;

            foreach ($carrito['items'] as $item) {
                $precio_bruto_item  = $item['cantidad'] * $item['precio'];
                $descuento_item     = $precio_bruto_item * (($item['descuento'] ?? 0) / 100);
                $subtotal_item_neto = $precio_bruto_item - $descuento_item;

                $vencimiento = $item['vencimiento'];
                if (empty($vencimiento)) {
                    Log::warning("Producto {$item['codpro']} Lote {$item['lote']} sin vencimiento, usando fecha actual.");
                    $vencimiento = now();
                }

                DB::connection($this->connection)
                    ->table('Docdet')
                    ->insert([
                        'Numero'     => $numeroDoc,
                        'Tipo'       => $tipoDoc,
                        'Codpro'     => $item['codpro'],
                        'Lote'       => $item['lote'],
                        'Vencimiento'=> $vencimiento,
                        'Unimed'     => $item['unimed'] ?? 1,
                        'Cantidad'   => $item['cantidad'],
                        'Precio'     => $item['precio'],
                        'Descuento1' => $item['descuento'] ?? 0,
                        'Subtotal'   => $subtotal_item_neto,
                        'Costo'      => $item['costo'],
                        'Nbonif'     => 0,
                    ]);

                // Actualizar stock
                $afectado = DB::connection($this->connection)
                    ->table('Saldos')
                    ->where('codpro', $item['codpro'])
                    ->where('lote', $item['lote'])
                    ->where('saldo', '>=', $item['cantidad'])
                    ->decrement('saldo', $item['cantidad']);

                if ($afectado == 0) {
                    throw new \Exception("Stock agotado para {$item['codpro']} Lote {$item['lote']}. Venta cancelada.");
                }

                $totalCostoVenta += ($item['costo'] * $item['cantidad']);
            }

            // Cuenta por cobrar
            DB::connection($this->connection)
                ->table('CtaCliente')
                ->insert([
                    'Documento' => $numeroDoc,
                    'Tipo'      => $tipoDoc,
                    'CodClie'   => $carrito['cliente']->Codclie,
                    'FechaF'    => now(),
                    'FechaV'    => $carrito['pago']['condicion'] == 'credito'
                        ? $carrito['pago']['fecha_venc']
                        : now(),
                    'Importe'   => $carrito['totales']['total'],
                    'Saldo'     => $carrito['totales']['total'],
                ]);

            // Asiento contable
            $this->contabilidadService->registrarAsientoVenta(
                $numeroDoc,
                $tipoDoc,
                $carrito['cliente'],
                $carrito['totales'],
                $totalCostoVenta,
                Auth::id()
            );

            DB::connection($this->connection)->commit();
            $this->carritoService->olvidar();

            return redirect()
                ->route('contador.facturas.show', ['numero' => $numeroDoc, 'tipo' => $tipoDoc])
                ->with('success', "Venta {$numeroDoc} registrada (Asiento creado).");
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error('Error al guardar venta (store): ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()
                ->back()
                ->with('error', 'Error crítico al guardar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Mapea el tipo interno (1,3,7,8) al código SUNAT (01,03,07,08)
     */
    private function mapearTipoDocumentoSunat($tipoInterno)
    {
        $mapeo = [
            1 => '01', // Factura
            3 => '03', // Boleta
            7 => '07', // Nota de Crédito
            8 => '08', // Nota de Débito
        ];

        return $mapeo[$tipoInterno] ?? '01';
    }

    /**
     * =========================================================
     * MOSTRAR DOCUMENTO
     * =========================================================
     */
    public function show($numero, $tipo)
    {
        $numeroTrimmed = rtrim($numero);

        $factura = DB::connection($this->connection)
            ->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function ($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')
                    ->where('t_moneda.n_codtabla', '=', 5);
            })
            ->leftJoin('TiposDocumentoSUNAT as tds', 'dc.tipo_documento_sunat', '=', 'tds.Codigo')
            ->select(
                'dc.*',
                'c.Razon as ClienteNombre',
                'c.Documento as ClienteRuc',
                'c.Direccion as ClienteDireccion',
                'c.Email as ClienteEmail',
                'e.Nombre as VendedorNombre',
                DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"),
                'tds.Descripcion as TipoDocumentoSunatNombre'
            )
            ->where('dc.Numero', $numeroTrimmed)
            ->where('dc.Tipo', $tipo)
            ->first();

        if (!$factura) {
            abort(404, 'Documento no encontrado');
        }

        $detalles = DB::connection($this->connection)
            ->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numeroTrimmed)
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre')
            ->get();

        $detallesParaJs = $detalles->map(function ($item) {
            return [
                'Codpro'             => $item->Codpro,
                'Cantidad'           => (float) $item->Cantidad,
                'UnitCode'           => 'NIU',
                'ProductoNombre'     => $item->ProductoNombre,
                'PrecioUnitarioSinIgv'=> (float) $item->Precio,
                'Subtotal'           => (float) $item->Subtotal,
            ];
        });

        $empresa = [
            'nombre'    => 'SEDIMCORP SAC',
            'ruc'       => '20123456789',
            'giro'      => 'EMPRESA DISTRIBUIDORA DE FARMACOS',
            'direccion' => 'AV. LOS HEROES 754 - Trujillo - La Libertad',
            'telefono'  => '(044) 555-1234',
            'email'     => 'ventas@sedimcorp.com',
            'web'       => 'www.sedimcorp.com',
        ];

        $totalEnLetras = NumberToWords::convert($factura->Total, 'SOLES');
        $condicionPago = ($factura->Dias > 0)
            ? 'CRÉDITO A ' . $factura->Dias . ' DÍAS'
            : 'CONTADO';

        $codigoQr = $this->facturaService->generarQr($factura);

        return view('ventas.show', compact(
            'factura',
            'detalles',
            'empresa',
            'totalEnLetras',
            'condicionPago',
            'codigoQr',
            'detallesParaJs'
        ));
    }

    /**
     * =========================================================
     * ENVIAR DOCUMENTO POR EMAIL
     * =========================================================
     */
    public function enviarEmail(Request $request, $numero, $tipo)
    {
        $request->validate(['email_destino' => 'required|email']);
        $emailDestino = $request->input('email_destino');

        try {
            $data = $this->getShowData(trim($numero), $tipo);
            $tipoDocNombre = $data['factura']->TipoDocNombre;
            $clienteNombre = $data['factura']->ClienteNombre;

            $pdf = Pdf::loadView('ventas.show', $data);

            $emailData = [
                'asunto'        => "Envío de {$tipoDocNombre}: {$data['factura']->Numero}",
                'titulo'        => "Estimado(a) {$clienteNombre},",
                'cuerpo'        => "Adjuntamos su {$tipoDocNombre} electrónica {$data['factura']->Numero} emitida por {$data['empresa']['nombre']}.",
                'pdf'           => $pdf->output(),
                'nombreArchivo' => "{$data['factura']->Numero}.pdf",
            ];

            Mail::to($emailDestino)->send(new EnviarDocumentoMail($emailData));

            return redirect()
                ->back()
                ->with('success', "Documento {$data['factura']->Numero} enviado a {$emailDestino} exitosamente.");
        } catch (\Exception $e) {
            Log::error('Error al enviar email de factura: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()
                ->back()
                ->with('error', 'Error al enviar el email: ' . $e->getMessage());
        }
    }

    /**
     * Helper centralizado para obtener datos de la factura
     */
    private function getShowData($numero, $tipo)
    {
        $factura = DB::connection($this->connection)
            ->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function ($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')
                    ->where('t_moneda.n_codtabla', '=', 5);
            })
            ->leftJoin('Tablas as t_doc', function ($join) {
                $join->on('t_doc.n_numero', '=', 'dc.Tipo')
                    ->where('t_doc.n_codtabla', '=', 3);
            })
            ->leftJoin('TiposDocumentoSUNAT as tds', 'dc.tipo_documento_sunat', '=', 'tds.Codigo')
            ->where('dc.Numero', $numero)
            ->where('dc.Tipo', $tipo)
            ->select(
                'dc.*',
                'c.Razon as ClienteNombre',
                'c.Documento as ClienteRuc',
                'c.Direccion as ClienteDireccion',
                'c.Email as ClienteEmail',
                'e.Nombre as VendedorNombre',
                DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"),
                DB::raw("ISNULL(t_doc.c_describe, 'DOCUMENTO') as TipoDocNombre"),
                'tds.Descripcion as TipoDocumentoSunatNombre'
            )
            ->first();

        if (!$factura) {
            abort(404, 'Documento no encontrado');
        }

        $detalles = DB::connection($this->connection)
            ->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numero)
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre', 'p.CodBar')
            ->get();

        $empresa = [
            'nombre'    => 'SEDIMCORP SAC',
            'ruc'       => '20123456789',
            'giro'      => 'EMPRESA DISTRIBUIDORA DE FARMACOS',
            'direccion' => 'AV. LOS HEROES 754 - Trujillo - La Libertad',
            'telefono'  => '(044) 555-1234',
            'email'     => 'ventas@sedimcorp.com',
            'web'       => 'www.sedimcorp.com',
        ];

        $fechaEmision  = Carbon::parse($factura->Fecha);
        $fechaVenc     = Carbon::parse($factura->FechaV);
        $condicionPago = $fechaEmision->diffInDays($fechaVenc, false) > 1 ? 'Crédito' : 'Contado';

        $totalEnLetras = NumberToWords::convert($factura->Total, $factura->MonedaNombre);

        return compact('factura', 'detalles', 'empresa', 'condicionPago', 'totalEnLetras');
    }

    /**
     * =========================================================
     * BÚSQUEDAS Y API AUXILIAR
     * =========================================================
     */
    public function buscarClientes(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $clientes = DB::connection($this->connection)
            ->table('Clientes')
            ->where('Activo', 1)
            ->where(function ($q) use ($query) {
                $q->where('Razon', 'LIKE', "%{$query}%")
                    ->orWhere('Documento', 'LIKE', "%{$query}%");
            })
            ->select('Codclie', 'Razon', 'Documento', 'Direccion', 'Vendedor', 'Email')
            ->limit(10)
            ->get();

        return response()->json($clientes);
    }

    public function buscarProductos(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $productos = DB::connection($this->connection)
            ->table('Productos')
            ->where('Eliminado', 0)
            ->where(function ($q) use ($query) {
                $q->where('Nombre', 'LIKE', "%{$query}%")
                    ->orWhere('CodPro', 'LIKE', "%{$query}%");
            })
            ->select('CodPro', 'Nombre', 'PventaMa as Precio', 'Costo', 'Stock', 'Afecto')
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    public function buscarLotes($codPro)
    {
        $lotes = DB::connection($this->connection)
            ->table('Saldos')
            ->where('codpro', $codPro)
            ->where('saldo', '>', 0)
            ->select('lote', 'vencimiento', 'saldo')
            ->orderBy('vencimiento', 'asc')
            ->get();

        return response()->json($lotes);
    }

    public function carritoAgregar(Request $request)
    {
        try {
            $itemData = $request->only([
                'codpro',
                'nombre',
                'lote',
                'vencimiento',
                'cantidad',
                'precio',
                'costo',
                'unimed',
                'descuento',
            ]);

            $carrito = $this->carritoService->agregarItem($itemData);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado',
                'carrito' => $carrito,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function carritoEliminar($itemId)
    {
        $carrito = $this->carritoService->eliminarItem($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado',
            'carrito' => $carrito,
        ]);
    }

    public function carritoActualizarPago(Request $request)
    {
        $pagoData = $request->only(['tipo_doc', 'condicion', 'fecha_venc', 'vendedor_id', 'moneda']);
        $carrito  = $this->carritoService->actualizarPago($pagoData);

        return response()->json([
            'success' => true,
            'message' => 'Datos de pago actualizados',
            'carrito' => $carrito,
        ]);
    }

    /**
     * Iniciar carrito vía AJAX
     */
    public function carritoIniciar(Request $request)
    {
        try {
            $clienteId = $request->input('cliente_id');

            if (!$clienteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cliente es requerido',
                ], 400);
            }

            $cliente = DB::connection($this->connection)
                ->table('Clientes')
                ->where('Codclie', $clienteId)
                ->first();

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $carrito = $this->carritoService->iniciar($cliente);

            $tipoDocAutomatico = 1;
            if ($cliente->tipoDoc == '1' || (isset($cliente->Documento) && strlen(trim($cliente->Documento)) === 8)) {
                $tipoDocAutomatico = 3; // Boleta
            }

            $carrito = $this->carritoService->actualizarPago([
                'tipo_doc'   => $tipoDocAutomatico,
                'condicion'  => 'contado',
                'fecha_venc' => now()->addDays(30)->format('Y-m-d'),
                'vendedor_id'=> $cliente->Vendedor ?? null,
                'moneda'     => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Carrito iniciado correctamente',
                'carrito' => $carrito,
                'cliente' => $cliente,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al iniciar carrito: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar carrito: ' . $e->getMessage(),
            ], 500);
        }
    }
}
