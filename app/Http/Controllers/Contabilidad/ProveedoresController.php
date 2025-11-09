<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProveedoresController extends Controller
{
    protected $connection = 'sqlsrv';

    public function __construct()
    {
        $this->middleware('auth');
    }

   
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('Proveedores');

        if ($request->filled('q')) {
            $query->where('RazonSocial', 'LIKE', '%' . $request->q . '%')
                  ->orWhere('Ruc', 'LIKE', '%' . $request->q . '%');
        }

        $proveedores = $query->where('Activo', 1)
                             ->orderBy('RazonSocial')
                             ->paginate(25);

        return view('compras.proveedores.index', [
            'proveedores' => $proveedores,
            'filtros' => $request->only('q')
        ]);
    }

   
    public function create()
    {
        return view('compras.proveedores.crear');
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'Ruc' => 'required|string|size:11|unique:sqlsrv.Proveedores,Ruc',
            'RazonSocial' => 'required|string|max:200',
            'Direccion' => 'nullable|string|max:255',
            'Contacto' => 'nullable|string|max:100',
            'Telefono' => 'nullable|string|max:50',
            'Email' => 'nullable|email|max:100',
        ]);

        try {
            DB::connection($this->connection)->table('Proveedores')->insert([
                'Ruc' => $request->Ruc,
                'RazonSocial' => $request->RazonSocial,
                'Direccion' => $request->Direccion,
                'Contacto' => $request->Contacto,
                'Telefono' => $request->Telefono,
                'Email' => $request->Email,
                'Activo' => 1
            ]);

            return redirect()->route('contador.proveedores.index')
                             ->with('success', 'Proveedor creado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar: ' . $e->getMessage())->withInput();
        }
    }
}