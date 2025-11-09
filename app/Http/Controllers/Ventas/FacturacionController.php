<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\VentaCarritoService; 
use App\Services\ContabilidadService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberToWords; 
use Illuminate\Support\Facades\Mail; // <-- ¡AÑADIDO!
use App\Mail\EnviarDocumentoMail;    
use Barryvdh\DomPDF\Facade\Pdf;                         

class FacturacionController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $carritoService;
    protected $contabilidadService;

    public function __construct(
        VentaCarritoService $carritoService,
        ContabilidadService $contabilidadService 
    ) {
        $this->middleware('auth');
        $this->carritoService = $carritoService;
        $this->contabilidadService = $contabilidadService; 
    }

    /**
     * Muestra la lista de facturas
     */
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereIn('dc.Tipo', [1, 3]); // Tipo 1=Factura, 3=Boleta

        if ($request->filled('q')) {
            $query->where('c.Razon', 'like', '%' . $request->q . '%')
                  ->orWhere('dc.Numero', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('estado') && $request->estado == 'anuladas') {
            $query->where('dc.Eliminado', 1);
        } else {
            $query->where('dc.Eliminado', 0); // Por defecto solo activas
        }

        $facturas = $query->select(
                'dc.Numero', 'dc.Tipo', 'dc.Fecha', 'dc.FechaV', 'c.Razon as Cliente',
                'dc.Total', 'dc.Moneda', 'dc.Eliminado'
            )
            ->orderBy('dc.Fecha', 'desc')
            ->orderBy('dc.Numero', 'desc')
            ->paginate(25);

        return view('ventas.index', [
            'facturas' => $facturas,
            'filtros' => $request->only(['q', 'estado'])
        ]);
    }

    /**
     * Muestra la vista de "Nueva Venta"
     */
    public function create(Request $request)
    {
        $clienteId = $request->query('cliente_id');
        $carrito = $this->carritoService->get();

        if ($clienteId) {
            $cliente = DB::connection($this->connection)->table('Clientes')->where('Codclie', $clienteId)->first();
            if ($cliente) {
                $carrito = $this->carritoService->iniciar($cliente);
                
                $tipoDocAutomatico = 1; 
                if ($cliente->Documento) {
                    $longitudDoc = strlen(trim($cliente->Documento));
                    if ($longitudDoc === 8) {
                        $tipoDocAutomatico = 3; // Boleta
                    }
                }
                
                $carrito = $this->carritoService->actualizarPago([
                    'tipo_doc' => $tipoDocAutomatico,
                    'condicion' => 'contado',
                    'fecha_venc' => now()->addDays(30)->format('Y-m-d'),
                    'vendedor_id' => $cliente->Vendedor ?? null,
                    'moneda' => 1 
                ]);
            }
        }
        
        $vendedores = DB::connection($this->connection)->table('Empleados')->orderBy('Nombre')->get();
        $tiposDoc = DB::connection($this->connection)->table('Tablas')
                        ->where('n_codtabla', 3)->whereIn('n_numero', [1, 3])->get();

        return view('ventas.crear', [
            'carrito' => $carrito,
            'vendedores' => $vendedores,
            'tiposDoc' => $tiposDoc
        ]);
    }
    
    /**
     * Guarda la Venta
     */
    public function store(Request $request)
    {
        $carrito = $this->carritoService->get();
        if (!$carrito || $carrito['items']->isEmpty()) {
            return redirect()->route('contador.facturas.create')->with('error', 'El carrito está vacío.');
        }

        DB::connection($this->connection)->beginTransaction();
        try {
            
            $tipoDoc = $carrito['pago']['tipo_doc'];
            $serie = $tipoDoc == 1 ? 'F001' : 'B001'; 

            $ultimoNum = DB::connection($this->connection)->table('Doccab')
                ->where('Tipo', $tipoDoc)->where('Numero', 'like', $serie.'-%')
                ->orderBy('Numero', 'desc')->value('Numero');

            $ultimoNumInt = $ultimoNum ? (int)substr($ultimoNum, strpos($ultimoNum, '-') + 1) : 0;
            $nuevoNumInt = $ultimoNumInt + 1;
            $numeroDoc = $serie . '-' . str_pad($nuevoNumInt, 8, '0', STR_PAD_LEFT);

            DB::connection($this->connection)->table('Doccab')->insert([
                'Numero' => $numeroDoc,
                'Tipo' => $tipoDoc,
                'CodClie' => $carrito['cliente']->Codclie,
                'Fecha' => now(),
                'Dias' => $carrito['pago']['condicion'] == 'credito' ? max(0, (int) Carbon::parse($carrito['pago']['fecha_venc'])->diffInDays(now())) : 0,
                'FechaV' => $carrito['pago']['condicion'] == 'credito' ? $carrito['pago']['fecha_venc'] : now(),
                'Subtotal' => $carrito['totales']['subtotal'],
                'Igv' => $carrito['totales']['igv'],
                'Total' => $carrito['totales']['total'],
                'Moneda' => $carrito['pago']['moneda'],
                'Vendedor' => $carrito['pago']['vendedor_id'],
                'Eliminado' => 0, 'Impreso' => 0,
                'Usuario' => Auth::user()->usuario,
                'estado_sunat' => 'PENDIENTE'
            ]);

            $totalCostoVenta = 0; 
            foreach ($carrito['items'] as $item) {
                
                $precio_bruto_item = $item['cantidad'] * $item['precio'];
                $descuento_item = $precio_bruto_item * (($item['descuento'] ?? 0) / 100);
                $subtotal_item_neto = $precio_bruto_item - $descuento_item;
                
                $vencimiento = $item['vencimiento'];
                if (empty($vencimiento)) {
                    Log::warning("Producto {$item['codpro']} Lote {$item['lote']} no tiene fecha de vencimiento. Usando fecha de hoy.");
                    $vencimiento = now();
                }

                DB::connection($this->connection)->table('Docdet')->insert([
                    'Numero' => $numeroDoc, 'Tipo' => $tipoDoc,
                    'Codpro' => $item['codpro'], 'Lote' => $item['lote'],
                    'Vencimiento' => $vencimiento,
                    'Unimed' => $item['unimed'] ?? 1,
                    'Cantidad' => $item['cantidad'],
                    'Precio' => $item['precio'],
                    'Descuento1' => $item['descuento'] ?? 0, 
                    'Subtotal' => $subtotal_item_neto, 
                    'Costo' => $item['costo'],
                    'Nbonif' => 0 
                ]);
                
                $afectado = DB::connection($this->connection)->table('Saldos')
                    ->where('codpro', $item['codpro'])->where('lote', $item['lote'])
                    ->where('saldo', '>=', $item['cantidad'])
                    ->decrement('saldo', $item['cantidad']);

                if ($afectado == 0) {
                    throw new \Exception("Stock agotado para {$item['codpro']} Lote {$item['lote']}. Venta cancelada.");
                }
                
                $totalCostoVenta += ($item['costo'] * $item['cantidad']);
            }

            DB::connection($this->connection)->table('CtaCliente')->insert([
                'Documento' => $numeroDoc,
                'Tipo' => $tipoDoc,
                'CodClie' => $carrito['cliente']->Codclie,
                'FechaF' => now(),
                'FechaV' => $carrito['pago']['condicion'] == 'credito' ? $carrito['pago']['fecha_venc'] : now(),
                'Importe' => $carrito['totales']['total'],
                'Saldo' => $carrito['totales']['total'],
            ]);
            
            $this->contabilidadService->registrarAsientoVenta(
                $numeroDoc, $tipoDoc,
                $carrito['cliente'],
                $carrito['totales'], 
                $totalCostoVenta,
                Auth::id()
            );

            DB::connection($this->connection)->commit();
            $this->carritoService->olvidar();
            
            return redirect()->route('contador.facturas.show', ['numero' => $numeroDoc, 'tipo' => $tipoDoc])
                             ->with('success', "Venta {$numeroDoc} registrada (Asiento Creado).");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al guardar venta (store): " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error crítico al guardar la venta: ' . $e->getMessage());
        }
    }

    

    public function show($numero, $tipo)
    {
        // Limpia los espacios en blanco de la URL (¡MUY IMPORTANTE!)
        $numeroTrimmed = rtrim($numero); 

        // 1. Obtienes la factura (Tu consulta ya hace esto)
        $factura = DB::table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')
                    ->where('t_moneda.n_codtabla', '=', 5); // 5 = Moneda
            })
            ->select('dc.*', 'c.Razon as ClienteNombre', 'c.Documento as ClienteRuc', 
                    'c.Direccion as ClienteDireccion', 'c.Email as ClienteEmail',
                    'e.Nombre as VendedorNombre', 
                    DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"))
            ->where('dc.Numero', $numeroTrimmed) // <-- Usa la variable sin espacios
            ->where('dc.Tipo', $tipo)
            ->first();

        if (!$factura) {
            abort(404, 'Documento no encontrado');
        }

        // 2. Obtienes los detalles (Tu consulta ya hace esto)
        $detalles = DB::table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numeroTrimmed) // <-- Usa la variable sin espacios
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre')
            ->get();

        
        $detallesParaJs = $detalles->map(function($item) {
            return [
                'Codpro' => $item->Codpro,
                'Cantidad' => (float)$item->Cantidad,
                'UnitCode' => 'NIU', // Asumimos 'NIU' (Unidad)
                'ProductoNombre' => $item->ProductoNombre,
                'PrecioUnitarioSinIgv' => (float)$item->Precio,
                'Subtotal' => (float)$item->Subtotal,
            ];
        });

        
        $empresa = [
            'nombre' => 'SEDIMCORP SAC', 
            'ruc' => '20123456789',
            'giro' => 'EMPRESA DE DISTRIBUIDORA DE FARMACOS',
            'direccion' => 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - Trujillo  - La libertad',
            'telefono' => '(01) 555-1234', 
            'email' => 'ventas@sedimcorp.com',
            'web' => 'www.sedimcorp.com' 
        ];

        // 5. Define el TOTAL EN LETRAS (Placeholder)
        // ¡Necesitarás un helper para esto!
        $totalEnLetras = NumberToWords::convert($factura->Total, 'SOLES');

        // 6. Define la CONDICIÓN DE PAGO
        $condicionPago = ($factura->Dias > 0) ? 'CRÉDITO A ' . $factura->Dias . ' DÍAS' : 'CONTADO';


        // 7. Pasa TODAS las variables a la vista
        return view('ventas.show', compact(
            'factura', 
            'detalles', 
            'empresa',         // <-- Ahora SÍ existe
            'totalEnLetras',   // <-- Ahora SÍ existe
            'condicionPago',   // <-- Ahora SÍ existe
            'detallesParaJs'   // <-- La nueva variable
        ));
    }
    
    public function enviarEmail(Request $request, $numero, $tipo)
    {
        $request->validate(['email_destino' => 'required|email']);
        $emailDestino = $request->input('email_destino');

        try {
            $data = $this->getShowData(trim($numero), $tipo); // Limpiamos espacios
            $tipoDocNombre = $data['factura']->TipoDocNombre;
            $clienteNombre = $data['factura']->ClienteNombre;

            // ¡CORREGIDO! Se quita la barra '\'
            $pdf = PDF::loadView('ventas.show', $data);
            
            $emailData = [
                'asunto' => "Envío de {$tipoDocNombre}: {$data['factura']->Numero}",
                'titulo' => "Estimado(a) {$clienteNombre},",
                'cuerpo' => "Adjuntamos su {$tipoDocNombre} electrónica {$data['factura']->Numero} emitida por {$data['empresa']['nombre']}.",
                'pdf' => $pdf->output(),
                'nombreArchivo' => "{$data['factura']->Numero}.pdf"
            ];

            Mail::to($emailDestino)->send(new EnviarDocumentoMail($emailData));

            return redirect()->back()->with('success', "Documento {$data['factura']->Numero} enviado a {$emailDestino} exitosamente.");

        } catch (\Exception $e) {
            Log::error("Error al enviar email de factura: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al enviar el email: ' . $e->getMessage());
        }
    }

    /**
     * ¡CORREGIDO Y CENTRALIZADO!
     * Este helper ahora define $empresa con la variable 'web'
     */
    private function getShowData($numero, $tipo)
    {
        $factura = DB::connection($this->connection)->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')->where('t_moneda.n_codtabla', '=', 5);
            })
            ->leftJoin('Tablas as t_doc', function($join) {
                $join->on('t_doc.n_numero', '=', 'dc.Tipo')->where('t_doc.n_codtabla', '=', 3);
            })
            ->where('dc.Numero', $numero)->where('dc.Tipo', $tipo) // Usa el número limpio
            ->select(
                'dc.*', 'c.Razon as ClienteNombre', 'c.Documento as ClienteRuc', 
                'c.Direccion as ClienteDireccion', 'c.Email as ClienteEmail', 
                'e.Nombre as VendedorNombre',
                DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"),
                DB::raw("ISNULL(t_doc.c_describe, 'DOCUMENTO') as TipoDocNombre")
            )->first();
            
        if(!$factura) abort(404, 'Documento no encontrado');

        $detalles = DB::connection($this->connection)->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numero)->where('dd.Tipo', $tipo) // Usa el número limpio
            ->select('dd.*', 'p.Nombre as ProductoNombre', 'p.CodBar')
            ->get();
            
        // ¡¡¡AQUÍ ESTÁ LA CORRECCIÓN!!!
        $empresa = [
            'nombre' => 'SEDIMCORP SAC', 
            'ruc' => '20123456789',
            'giro' => 'EMPRESA DE DISTRIBUIDORA DE FARMACOS',
            'direccion' => 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - LIMA - LIMA',
            'telefono' => '(01) 555-1234', 
            'email' => 'ventas@sedimcorp.com',
            'web' => 'www.sedimcorp.com' // <-- ¡LA CLAVE QUE FALTABA!
        ];
        
        $fechaEmision = Carbon::parse($factura->Fecha);
        $fechaVenc = Carbon::parse($factura->FechaV);
        $condicionPago = $fechaEmision->diffInDays($fechaVenc, false) > 1 ? 'Crédito' : 'Contado';
        
        $totalEnLetras = NumberToWords::convert($factura->Total, $factura->MonedaNombre);

        return compact('factura', 'detalles', 'empresa', 'condicionPago', 'totalEnLetras');
    }

    
    // --- (Resto de tus métodos API) ---
    
    public function buscarClientes(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 3) return response()->json([]);
        $clientes = DB::connection($this->connection)->table('Clientes')
            ->where('Activo', 1)
            ->where(function($q) use ($query) {
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
        if (strlen($query) < 3) return response()->json([]);
        $productos = DB::connection($this->connection)->table('Productos')
            ->where('Eliminado', 0)
            ->where(function($q) use ($query) {
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
        $lotes = DB::connection($this->connection)->table('Saldos')
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
            $itemData = $request->only(['codpro', 'nombre', 'lote', 'vencimiento', 'cantidad', 'precio', 'costo', 'unimed', 'descuento']);
            $carrito = $this->carritoService->agregarItem($itemData);
            return response()->json(['success' => true, 'message' => 'Producto agregado', 'carrito' => $carrito]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function carritoEliminar($itemId)
    {
        $carrito = $this->carritoService->eliminarItem($itemId);
        return response()->json(['success' => true, 'message' => 'Producto eliminado', 'carrito' => $carrito]);
    }
    
    public function carritoActualizarPago(Request $request)
    {
        $pagoData = $request->only(['tipo_doc', 'condicion', 'fecha_venc', 'vendedor_id', 'moneda']);
        $carrito = $this->carritoService->actualizarPago($pagoData);
        return response()->json(['success' => true, 'message' => 'Datos de pago actualizados', 'carrito' => $carrito]);
    }
}