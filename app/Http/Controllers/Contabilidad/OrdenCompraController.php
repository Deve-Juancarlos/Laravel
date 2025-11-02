<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompraCarritoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrdenCompraController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $carritoService;

    public function __construct(CompraCarritoService $carritoService)
    {
        $this->middleware('auth');
        $this->carritoService = $carritoService;
    }

    /**
     * Muestra la lista de Órdenes de Compra
     */
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('OrdenCompraCab as oc')
            ->join('Proveedores as p', 'oc.CodProv', '=', 'p.CodProv');

        if ($request->filled('q')) {
            $query->where('p.RazonSocial', 'LIKE', '%' . $request->q . '%')
                  ->orWhere('oc.Numero', 'LIKE', '%' . $request->q . '%');
        }
        
        // Filtro por Estado (¡NUEVO!)
        if ($request->filled('estado')) {
            $query->where('oc.Estado', $request->estado);
        }

        $ordenes = $query->select('oc.*', 'p.RazonSocial as ProveedorNombre')
                         ->orderBy('oc.FechaEmision', 'desc')
                         ->paginate(25);

        return view('compras.ordenes.index', [
            'ordenes' => $ordenes,
            'filtros' => $request->only(['q', 'estado']) // Añadido filtro
        ]);
    }

    /**
     * Muestra el formulario de creación (flujo de carrito)
     */
    public function create(Request $request)
    {
        $proveedorId = $request->query('proveedor_id');
        $carrito = $this->carritoService->get();

        if ($proveedorId) {
            $proveedor = DB::connection($this->connection)->table('Proveedores')->where('CodProv', $proveedorId)->first();
            if ($proveedor) {
                $carrito = $this->carritoService->iniciar($proveedor);
            }
        }
        
        return view('compras.ordenes.crear', [
            'carrito' => $carrito
        ]);
    }

    /**
     * Guarda la Orden de Compra (POST final)
     */
    public function store(Request $request)
    {
        $carrito = $this->carritoService->get();
        if (!$carrito || $carrito['items']->isEmpty()) {
            return redirect()->route('contador.compras.create')->with('error', 'El carrito está vacío.');
        }

        DB::connection($this->connection)->beginTransaction();
        try {
            
            $serie = 'OC01';
            $ultimoNum = DB::connection($this->connection)->table('OrdenCompraCab')
                            ->where('Serie', $serie)->max('Numero');
            $nuevoNum = $ultimoNum ? (int)$ultimoNum + 1 : 1;
            $numeroDoc = str_pad($nuevoNum, 8, '0', STR_PAD_LEFT);

            $ordenId = DB::connection($this->connection)->table('OrdenCompraCab')->insertGetId([
                'Serie' => $serie,
                'Numero' => $numeroDoc,
                'CodProv' => $carrito['proveedor']->CodProv,
                'FechaEmision' => now(),
                'FechaEntrega' => $carrito['pago']['fecha_entrega'],
                'Moneda' => $carrito['pago']['moneda'],
                'Subtotal' => $carrito['totales']['subtotal'],
                'Igv' => $carrito['totales']['igv'],
                'Total' => $carrito['totales']['total'],
                'Estado' => 'PENDIENTE', 
                'UsuarioId' => Auth::id(),
                'created_at' => now(),
            ]);

            foreach ($carrito['items'] as $item) {
                DB::connection($this->connection)->table('OrdenCompraDet')->insert([
                    'OrdenId' => $ordenId,
                    'CodPro' => $item['codpro'],
                    'Cantidad' => $item['cantidad'],
                    'CostoUnitario' => $item['costo'],
                    'Subtotal' => ($item['cantidad'] * $item['costo'])
                ]);
            }
            
           

            DB::connection($this->connection)->commit();
            $this->carritoService->olvidar();
            
            // Redirigimos a la vista 'show'
            return redirect()->route('contador.compras.show', $ordenId)
                             ->with('success', "Orden de Compra {$serie}-{$numeroDoc} registrada. Pendiente de recepción.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al guardar Orden de Compra: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error crítico al guardar la O/C: ' . $e->getMessage());
        }
    }
    
    
    public function show($id)
    {
        $orden = $this->getOrdenCompraCompleta($id);
        if (!$orden) abort(404);

        return view('compras.ordenes.show', [
            'orden' => $orden,
            'detalles' => $orden->detalles,
            'proveedor' => $orden->proveedor,
            'empresa' => $this->getEmpresaDatos()
        ]);
    }
    
  
    private function getOrdenCompraCompleta($id)
    {
        $orden = DB::connection($this->connection)->table('OrdenCompraCab as oc')
            ->join('Proveedores as p', 'oc.CodProv', '=', 'p.CodProv')
            ->where('oc.Id', $id)
            ->select('oc.*', 'p.RazonSocial as ProveedorNombre', 'p.Ruc as ProveedorRuc', 'p.Direccion as ProveedorDireccion')
            ->first();
            
        if(!$orden) return null;

        $detalles = DB::connection($this->connection)->table('OrdenCompraDet as od')
            ->join('Productos as p', 'od.CodPro', '=', 'p.CodPro')
            // ¡AQUÍ USAMOS TU TABLA LABORATORIOS!
            ->leftJoin('Laboratorios as l', DB::raw('RTRIM(l.CodLab)'), '=', DB::raw('LEFT(p.CodPro, 2)'))
            ->where('od.OrdenId', $id)
            ->select('od.*', 'p.Nombre as ProductoNombre', 'l.Descripcion as LaboratorioNombre')
            ->get();
            
        $orden->detalles = $detalles;
        $orden->proveedor = (object)[
            'RazonSocial' => $orden->ProveedorNombre,
            'Ruc' => $orden->ProveedorRuc,
            'Direccion' => $orden->ProveedorDireccion
        ];
        
        return $orden;
    }
    
    private function getEmpresaDatos()
    {
        return [
            'nombre' => 'SEDIMCORP SAC',
            'giro' => 'EMPRESA DE DISTRIBUIDORA DE FARMACOS',
            'ruc' => '20123456789', 
            'direccion' => 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - TRUJILLO - LA LIBERTAD',
            'telefono' => '968468303',
            'email' => 'compras@sedimcorp.com',
        ];
    }
    
   
    
    public function buscarProveedores(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 3) return response()->json([]);
        $proveedores = DB::connection($this->connection)->table('Proveedores')
            ->where('Activo', 1)
            ->where(function($q) use ($query) {
                $q->where('RazonSocial', 'LIKE', "%{$query}%")
                  ->orWhere('Ruc', 'LIKE', "%{$query}%");
            })
            ->select('CodProv', 'RazonSocial', 'Ruc', 'Direccion')
            ->limit(10)
            ->get();
        return response()->json($proveedores);
    }
    
    public function carritoAgregar(Request $request)
    {
        try {
            $itemData = $request->only(['codpro', 'nombre', 'costo', 'cantidad', 'laboratorio']);
            $carrito = $this->carritoService->agregarItem($itemData);
            return response()->json(['success' => true, 'carrito' => $carrito]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function carritoEliminar($itemId)
    {
        $carrito = $this->carritoService->eliminarItem($itemId);
        return response()->json(['success' => true, 'carrito' => $carrito]);
    }
    
    public function carritoActualizarPago(Request $request)
    {
        $pagoData = $request->only(['fecha_entrega', 'moneda']);
        $carrito = $this->carritoService->actualizarPago($pagoData);
        return response()->json(['success' => true, 'carrito' => $carrito]);
    }
}