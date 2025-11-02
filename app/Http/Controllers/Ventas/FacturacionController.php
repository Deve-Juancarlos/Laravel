<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\VentaCarritoService; // <-- ¡NUESTRO SERVICIO!
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberToWords; 
class FacturacionController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $carritoService;

    public function __construct(VentaCarritoService $carritoService)
    {
        $this->middleware('auth');
        $this->carritoService = $carritoService;
    }

    /**
     * Muestra la lista de facturas
     */
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->whereIn('dc.Tipo', [1, 2]); // Tipo 1=Factura, 2=Boleta

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
     * Muestra la vista de "Nueva Venta" (Seleccionar Cliente y Añadir Items)
     */
    public function create(Request $request)
    {
        $clienteId = $request->query('cliente_id');
        $carrito = $this->carritoService->get();

        // Si se pasa un cliente_id, iniciamos/reiniciamos el carrito
        if ($clienteId) {
            $cliente = DB::connection($this->connection)->table('Clientes')->where('Codclie', $clienteId)->first();
            if ($cliente) {
                $carrito = $this->carritoService->iniciar($cliente);
            }
        }
        
        // Cargamos los vendedores (Empleados) de tu BD
        $vendedores = DB::connection($this->connection)->table('Empleados')
                        //->where('Tipo', 1) // Asumo Tipo 1 = Vendedor, ajusta si es necesario
                        ->orderBy('Nombre')
                        ->get();
        
        // Cargamos Tipos de Documento (Factura/Boleta) de tu tabla Tablas
        $tiposDoc = DB::connection($this->connection)->table('Tablas')
                        ->where('n_codtabla', 3) // Asumo 3 = Tipos de Documento
                        ->whereIn('n_numero', [1, 2]) // 1=Factura, 2=Boleta
                        ->get();

        return view('ventas.crear', [
            'carrito' => $carrito,
            'vendedores' => $vendedores,
            'tiposDoc' => $tiposDoc
        ]);
    }

    /**
     * PASO 4: Guardar la Venta (El POST final)
     */
    public function store(Request $request)
    {
        $carrito = $this->carritoService->get();
        if (!$carrito || $carrito['items']->isEmpty()) {
            return redirect()->route('contador.facturas.create')->with('error', 'El carrito está vacío.');
        }

        DB::connection($this->connection)->beginTransaction();
        try {
            
            // 1. Generar el correlativo correctamente
            $tipoDoc = $carrito['pago']['tipo_doc'];
            $serie = $tipoDoc == 1 ? 'F001' : 'B001';

            // Obtenemos el último número para esta serie y tipo
            $ultimoNum = DB::connection($this->connection)->table('Doccab')
                ->where('Tipo', $tipoDoc)
                ->where('Numero', 'like', $serie.'-%')
                ->orderBy('Numero', 'desc')
                ->value('Numero');

            // Extraemos solo la parte numérica después del guion de la serie
            if ($ultimoNum) {
                $ultimoNumInt = (int)substr($ultimoNum, strpos($ultimoNum, '-') + 1);
            } else {
                $ultimoNumInt = 0;
            }

            // Nuevo correlativo
            $nuevoNumInt = $ultimoNumInt + 1;
            $nuevoNumStr = str_pad($nuevoNumInt, 8, '0', STR_PAD_LEFT);
            $numeroDoc = $serie . '-' . $nuevoNumStr;

            // 2. Crear la cabecera (Doccab)
            DB::connection($this->connection)->table('Doccab')->insert([
            'Numero' => $numeroDoc,
            'Tipo' => $tipoDoc,
            'CodClie' => $carrito['cliente']->Codclie,
            'Fecha' => now(),
            'Dias' => $carrito['pago']['condicion'] == 'credito' 
                ? max(0, (int) Carbon::parse($carrito['pago']['fecha_venc'])->diffInDays(now()))
                : 0,
            'FechaV' => $carrito['pago']['condicion'] == 'credito' ? $carrito['pago']['fecha_venc'] : now(),
            'Subtotal' => $carrito['totales']['subtotal'],
            'Igv' => $carrito['totales']['igv'],
            'Total' => $carrito['totales']['total'],
            'Moneda' => $carrito['pago']['moneda'],
            'Vendedor' => $carrito['pago']['vendedor_id'],
            'Eliminado' => 0,
            'Impreso' => 0,
            'Usuario' => Auth::user()->usuario,
        ]);


            // 3. Insertar los detalles (Docdet) y REBAJAR STOCK (Saldos)
            foreach ($carrito['items'] as $item) {
                
                DB::connection($this->connection)->table('Docdet')->insert([
                    'Numero' => $numeroDoc,
                    'Tipo' => $tipoDoc,
                    'Codpro' => $item['codpro'],
                    'Lote' => $item['lote'],
                    'Vencimiento' => $item['vencimiento'],
                    'Unimed' => $item['unimed'] ?? 1,
                    'Cantidad' => $item['cantidad'],
                    'Precio' => $item['precio'],
                    'Subtotal' => ($item['cantidad'] * $item['precio']),
                    'Costo' => $item['costo'],
                    'Nbonif' => 0 
                ]);
                
                // 3B. ¡REBAJAR EL STOCK!
                $afectado = DB::connection($this->connection)->table('Saldos')
                    ->where('codpro', $item['codpro'])
                    ->where('lote', $item['lote'])
                    ->where('saldo', '>=', $item['cantidad'])
                    ->decrement('saldo', $item['cantidad']);

                if ($afectado == 0) {
                    throw new \Exception("Stock agotado para {$item['codpro']} Lote {$item['lote']}. Venta cancelada.");
                }
            }

            // 4. Crear la Cuenta por Cobrar (CtaCliente)
            DB::connection($this->connection)->table('CtaCliente')->insert([
                'Documento' => $numeroDoc,
                'Tipo' => $tipoDoc,
                'CodClie' => $carrito['cliente']->Codclie,
                'FechaF' => now(),
                'FechaV' => $carrito['pago']['condicion'] == 'credito' ? $carrito['pago']['fecha_venc'] : now(),
                'Importe' => $carrito['totales']['total'],
                'Saldo' => $carrito['totales']['total'],
            ]);
            
            
            DB::connection($this->connection)->commit();
            $this->carritoService->olvidar(); // Limpiamos el carrito
            
            // Redirigir a la vista 'show'
            return redirect()->route('contador.facturas.show', ['numero' => $numeroDoc, 'tipo' => $tipoDoc])
                            ->with('success', "Venta {$numeroDoc} registrada exitosamente.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al guardar venta: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error crítico al guardar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista de la factura/boleta para imprimir (show)
     */
    public function show($numero, $tipo)
    {
        $factura = DB::connection($this->connection)->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp') // <-- AÑADIDO VENDEDOR
            ->leftJoin('Tablas as t_moneda', function($join) { // <-- AÑADIDO MONEDA
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')
                     ->where('t_moneda.n_codtabla', '=', 5); // Asumo 5 = Moneda
            })
            ->leftJoin('Tablas as t_doc', function($join) { // <-- AÑADIDO TIPO DOC
                $join->on('t_doc.n_numero', '=', 'dc.Tipo')
                     ->where('t_doc.n_codtabla', '=', 3); // Asumo 3 = Tipo Doc
            })
            ->where('dc.Numero', $numero)
            ->where('dc.Tipo', $tipo)
            ->select(
                'dc.*', 
                'c.Razon as ClienteNombre', 
                'c.Documento as ClienteRuc', 
                'c.Direccion as ClienteDireccion',
                'e.Nombre as VendedorNombre', // <-- Campo de Vendedor
                DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"), // <-- Campo de Moneda
                DB::raw("ISNULL(t_doc.c_describe, 'DOCUMENTO') as TipoDocNombre") // <-- Campo de TipoDoc
            )
            ->first();
            
        if(!$factura) abort(404, 'Documento no encontrado');

        $detalles = DB::connection($this->connection)->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numero)
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre', 'p.CodBar')
            ->get();
            
        // Datos de tu Empresa (SEDIMCORP SAC)
        $empresa = [
            'nombre' => 'SEDIMCORP SAC',
            'giro' => 'EMPRESA DE DISTRIBUIDORA DE FARMACOS',
            'ruc' => '20123456789', // <-- RUC de ejemplo, cámbialo
            'direccion' => 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - LIMA - LIMA',
            'telefono' => '(01) 555-1234',
            'email' => 'ventas@sedimcorp.com',
            'web' => 'www.sedimcorp.com'
        ];
        
        // Determinar Condición de Pago
        $fechaEmision = Carbon::parse($factura->Fecha);
        $fechaVenc = Carbon::parse($factura->FechaV);
        $condicionPago = $fechaEmision->diffInDays($fechaVenc, false) > 1 ? 'Crédito' : 'Contado';
        
        // Convertir total a letras
        $totalEnLetras = NumberToWords::convert($factura->Total, $factura->MonedaNombre);

        return view('ventas.show', compact('factura', 'detalles', 'empresa', 'condicionPago', 'totalEnLetras'));
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE BÚSQUEDA (AJAX) PARA EL CARRITO
    |--------------------------------------------------------------------------
    */

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
            ->select('Codclie', 'Razon', 'Documento', 'Direccion', 'Vendedor')
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
            ->select('CodPro', 'Nombre', 'PventaMa as Precio', 'Costo', 'Stock', 'Afecto') // Afecto=Si paga IGV
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
    
    /*
    |--------------------------------------------------------------------------
    | Carrito AJAX (Llama al Servicio)
    |--------------------------------------------------------------------------
    */
    
    public function carritoAgregar(Request $request)
    {
        try {
            $itemData = $request->only(['codpro', 'nombre', 'lote', 'vencimiento', 'cantidad', 'precio', 'costo', 'unimed']);
            $carrito = $this->carritoService->agregarItem($itemData);
            
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado',
                'carrito' => $carrito
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function carritoEliminar($itemId)
    {
        $carrito = $this->carritoService->eliminarItem($itemId);
        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado',
            'carrito' => $carrito
        ]);
    }
    
    public function carritoActualizarPago(Request $request)
    {
        $pagoData = $request->only(['tipo_doc', 'condicion', 'fecha_venc', 'vendedor_id', 'moneda']);
        $carrito = $this->carritoService->actualizarPago($pagoData);
        return response()->json([
            'success' => true,
            'message' => 'Datos de pago actualizados',
            'carrito' => $carrito
        ]);
    }

}