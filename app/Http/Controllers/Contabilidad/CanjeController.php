<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contabilidad\CanjeService;
use Illuminate\Support\Facades\Log;
use Exception;

class CanjeController extends Controller
{
    protected $canjeService;

    public function __construct(CanjeService $canjeService)
    {
        $this->middleware('auth');
        $this->canjeService = $canjeService;
    }

    /**
     * Listado de canjes realizados
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'cliente' => $request->input('cliente'),
                'fecha_desde' => $request->input('fecha_desde'),
                'fecha_hasta' => $request->input('fecha_hasta'),
            ];

            $data = $this->canjeService->getIndexData($filters);
            
            return view('contabilidad.canjes.index', $data);
            
        } catch (Exception $e) {
            Log::error("Error en CanjeController@index: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar canjes: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para crear nuevo canje
     */
    public function create()
    {
        try {
            $data = $this->canjeService->getCreateData();
            return view('contabilidad.canjes.create', $data);
            
        } catch (Exception $e) {
            Log::error("Error en CanjeController@create: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar formulario: ' . $e->getMessage());
        }
    }

    /**
     * API: Obtener facturas pendientes de un cliente
     */
    public function getFacturasCliente(Request $request)
    {
        try {
            $codCliente = (int) $request->input('cod_cliente');
            
            if (!$codCliente) {
                return response()->json(['error' => 'Cliente no especificado'], 400);
            }

            $data = $this->canjeService->getFacturasPendientes($codCliente);
            
            return response()->json($data);
            
        } catch (Exception $e) {
            Log::error("Error en getFacturasCliente: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesar creación del canje
     */
    public function store(Request $request)
    {
        $request->validate([
            'cod_cliente' => 'required|integer',
            'facturas' => 'required|array|min:1',
            'cantidad_letras' => 'required|integer|min:1',
            'fecha_primera_letra' => 'required|date',
            'dias_entre_cuotas' => 'nullable|integer|min:1'
        ], [
            'cod_cliente.required' => 'Debe seleccionar un cliente',
            'facturas.required' => 'Debe seleccionar al menos una factura',
            'cantidad_letras.required' => 'Debe especificar la cantidad de letras',
            'cantidad_letras.min' => 'Debe generar al menos 1 letra',
            'fecha_primera_letra.required' => 'Debe especificar la fecha de la primera letra',
        ]);

        try {
            // Llamamos al servicio
            $resultado = $this->canjeService->crearCanje($request->all());
            
            // SOLUCIÓN: Contar las letras antes de ponerlas en el mensaje de texto
            // $resultado['letras'] es un Array, no se puede imprimir directo.
            $totalLetras = count($resultado['letras']);
            $asiento = $resultado['asiento'];

            return redirect()
                ->route('contador.canjes.index')
                ->with('success', "Canje creado exitosamente. Se generaron {$totalLetras} letras. Asiento contable: {$asiento}");
            
        } catch (Exception $e) {
            Log::error("Error en CanjeController@store: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el canje: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalles de un canje específico
     */
    public function show($id)
    {
        try {
            // Podrías implementar aquí la vista del detalle del asiento o las letras generadas
            return redirect()->route('contador.canjes.index');
            
        } catch (Exception $e) {
            Log::error("Error en CanjeController@show: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mostrar canje: ' . $e->getMessage());
        }
    }
}