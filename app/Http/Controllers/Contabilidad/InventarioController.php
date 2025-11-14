<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InventarioService; // <-- Importamos el servicio
use Illuminate\Support\Facades\DB;   // <-- ¡CORRECCIÓN! Faltaba esto
use Illuminate\Support\Facades\Log;  // <-- ¡CORRECCIÓN! Faltaba esto

class InventarioController extends Controller
{
    protected $inventarioService;
    protected $connection = 'sqlsrv'; // <-- ¡CORRECCIÓN! Faltaba esta línea

    // Inyectamos el servicio
    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Muestra la lista principal de productos (para el link "Productos").
     */
    public function index(Request $request)
    {
        $productos = $this->inventarioService->getProductosPaginados($request);
        $filtros = $request->only(['q']);

        return view('inventario.index', compact('productos', 'filtros'));
    }

    /**
     * Muestra el detalle de un producto (lotes, stock).
     */
    public function show($codPro)
    {
        $producto = $this->inventarioService->getProductoPorCodigo($codPro);
        if (!$producto) {
            abort(404, 'Producto no encontrado');
        }
        
        $stockDetallado = $this->inventarioService->getStockPorProducto($codPro);

        return view('inventario.show', compact('producto', 'stockDetallado'));
    }
    
    /**
     * Muestra la lista de "Stock y Lotes" (tabla Saldos).
     */
    public function stockLotes(Request $request)
    {
        try {
            $lotes = $this->inventarioService->getStockLotesPaginado($request);
            $filtros = $request->only(['q']);
            return view('inventario.stock', compact('lotes', 'filtros'));
        } catch (\Exception $e) {
            Log::error('Error en stockLotes: ' . $e->getMessage()); // Usamos Log
            return back()->withErrors(['error' => 'Error al cargar el stock.']);
        }
    }

    /**
     * Muestra la lista de laboratorios.
     */
    public function laboratorios(Request $request)
    {
        $laboratorios = $this->inventarioService->getLaboratoriosPaginados($request);
        $filtros = $request->only(['q']);

        return view('inventario.laboratorios', compact('laboratorios', 'filtros'));
    }

    /**
     * (Recomendado) Reporte de Vencimientos
     */
    public function vencimientos(Request $request)
    {
        $productos = $this->inventarioService->getProductosPorVencer($request);
        $filtros = $request->only(['q', 'estado']);

        return view('inventario.vencimientos', compact('productos', 'filtros'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return view('inventario.create');
    }

    /**
     * ¡CORREGIDO!
     * Guarda el producto y su lote inicial en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validación de todos los campos
        $request->validate([
            // Campos de Productos
            'CodPro' => 'required|string|max:10|unique:sqlsrv.Productos,CodPro',
            'Nombre' => 'required|string|max:70',
            'CodProv' => 'nullable|string|max:4', // (Debería validar contra Proveedores si existe)
            'Costo' => 'required|numeric|min:0',
            'PventaMa' => 'required|numeric|min:0',
            'PventaMi' => 'required|numeric|min:0',
            'Clinea' => 'required|integer',
            'Clase' => 'required|integer',
            'Principio' => 'nullable|string|max:200',

            // Campos de Saldos (El Lote Inicial)
            'lote' => 'required|string|max:15',
            'vencimiento' => 'required|date',
            'stock_inicial' => 'required|numeric|min:0.01', // Stock debe ser mayor a 0
        ]);

        // 2. Iniciar Transacción
        // Usamos $this->connection (que definimos arriba)
        DB::connection($this->connection)->beginTransaction();

        try {
            // 3. Insertar en la tabla 'Productos'
            DB::connection($this->connection)->table('Productos')->insert([
                'CodPro' => $request->CodPro,
                'CodBar' => $request->CodBar,
                'Clinea' => $request->Clinea,
                'Clase' => $request->Clase,
                'Nombre' => $request->Nombre,
                'CodProv' => $request->CodProv,
                'Peso' => $request->Peso ?? 0,
                'Minimo' => $request->Minimo ?? 0,
                'Stock' => $request->stock_inicial, // ¡El stock total es el stock inicial!
                'Afecto' => $request->Afecto ?? 1,
                'Tipo' => $request->Tipo ?? 1,
                'Costo' => $request->Costo,
                'PventaMa' => $request->PventaMa,
                'PventaMi' => $request->PventaMi,
                'Eliminado' => 0,
                'AfecFle' => 0,
                'Principio' => $request->Principio,
            ]);

            // 4. Insertar en la tabla 'Saldos'
            DB::connection($this->connection)->table('Saldos')->insert([
                'codpro' => $request->CodPro,
                'almacen' => 1, // Asumimos Almacén 1 (principal)
                'lote' => $request->lote,
                'vencimiento' => $request->vencimiento,
                'saldo' => $request->stock_inicial,
                'protocolo' => 0
            ]);

            // 5. Confirmar Transacción
            DB::connection($this->connection)->commit();

            return redirect()->route('contador.inventario.index')
                             ->with('success', "Producto {$request->Nombre} creado exitosamente con su lote inicial.");

        } catch (\Exception $e) {
            // 6. Revertir en caso de error
            DB::connection($this->connection)->rollBack();
            Log::error("Error al crear producto: " . $e->getMessage()); // Usamos Log
            Log::error($e->getTraceAsString());
            
            return redirect()->back()
                             ->with('error', 'Error crítico al guardar el producto: ' . $e->getMessage())
                             ->withInput();
        }
    }
}