<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\ContabilidadService;
use Carbon\Carbon;
use PDF;
use App\Models\CompraCab;
use App\Models\CompraDet;

class RegistroCompraController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $contabilidadService;

    public function __construct(ContabilidadService $contabilidadService)
    {
        $this->middleware('auth');
        $this->contabilidadService = $contabilidadService;
    }

    
    public function create(Request $request)
    {
        $ordenCompra = null;
        $detalles = collect();
        $proveedor = null;

        if ($request->has('orden_id')) {
            $ordenId = $request->input('orden_id');
            
            $ordenCompra = DB::connection($this->connection)->table('OrdenCompraCab as oc')
                ->join('Proveedores as p', 'oc.CodProv', '=', 'p.CodProv')
                ->where('oc.Id', $ordenId)
                ->where('oc.Estado', 'PENDIENTE') // Solo O/C pendientes
                ->select('oc.*', 'p.RazonSocial', 'p.Ruc')
                ->first();

            if ($ordenCompra) {
                $detalles = DB::connection($this->connection)->table('OrdenCompraDet as od')
                    ->join('Productos as p', 'od.CodPro', '=', 'p.CodPro')
                    ->where('od.OrdenId', $ordenId)
                    ->select('od.CodPro', 'p.Nombre', 'od.Cantidad', 'od.CostoUnitario')
                    ->get();
                
                $proveedor = (object)[
                    'CodProv' => $ordenCompra->CodProv,
                    'RazonSocial' => $ordenCompra->RazonSocial,
                    'Ruc' => $ordenCompra->Ruc,
                ];
            } else {
                return redirect()->route('contador.compras.index')
                    ->with('error', 'Orden de Compra no encontrada o ya fue procesada.');
            }
        } else {
             return redirect()->route('contador.compras.index')
                    ->with('error', 'Debe seleccionar una Orden de Compra para registrar.');
        }
        
        // Vista para el formulario de registro de compra
        return view('compras.registros.create', [
            'ordenCompra' => $ordenCompra,
            'detalles' => $detalles,
            'proveedor' => $proveedor
        ]);
    }

    /**
     * Guarda la Compra, afecta Inventario, Cuentas por Pagar y Contabilidad.
     */
   public function store(Request $request)
    {
        // 1. Validar datos del formulario
        $request->validate([
            'orden_id' => 'required|integer|exists:sqlsrv.OrdenCompraCab,Id',
            'proveedor_id' => 'required|integer|exists:sqlsrv.Proveedores,CodProv',
            'nro_factura' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
            'almacen_id' => 'required|integer', 
            
            'subtotal' => 'required|numeric|min:0',
            'igv' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0.01',
            
            'items' => 'required|array|min:1',
            'items.*.codpro' => 'required|string|exists:sqlsrv.Productos,CodPro',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.costo' => 'required|numeric|min:0',
            'items.*.lote' => 'required|string|max:15',
            'items.*.vencimiento' => 'required|date',
        ]);

        $ordenId = $request->input('orden_id');
        $proveedorId = $request->input('proveedor_id');
        $nroFactura = $request->input('nro_factura');
        $fechaEmision = Carbon::parse($request->input('fecha_emision'));
        $fechaVencimiento = Carbon::parse($request->input('fecha_vencimiento'));
        $almacenId = $request->input('almacen_id');

        DB::connection($this->connection)->beginTransaction();
        try {
            
            // 2. Verificar que la O/C esté PENDIENTE
            $orden = DB::connection($this->connection)->table('OrdenCompraCab')
                        ->where('Id', $ordenId)->lockForUpdate()->first();
                        
            if ($orden->Estado !== 'PENDIENTE') {
                throw new \Exception("La Orden de Compra {$orden->Serie}-{$orden->Numero} ya fue procesada.");
            }

            $compraId = DB::connection($this->connection)->table('CompraCab')->insertGetId([
                'Serie' => substr($nroFactura, 0, 4), // Ejemplo: "F001"
                'Numero' => substr($nroFactura, 5),    // Ejemplo: "000123"
                'TipoDoc' => '01', // 01=Factura, ajusta según tu lógica
                'CodProv' => $proveedorId,
                'FechaEmision' => $fechaEmision,
                'FechaVencimiento' => $fechaVencimiento,
                'Moneda' => 1, // 1=Soles, 2=Dólares
                'Cambio' => 1.00,
                'BaseAfecta' => $request->input('subtotal'),
                'BaseInafecta' => 0,
                'Igv' => $request->input('igv'),
                'Total' => $request->input('total'),
                'Estado' => 'REGISTRADA',
                'Glosa' => "Compra de O/C {$orden->Serie}-{$orden->Numero}",
                'OrdenCompraId' => $ordenId,
                'UsuarioId' => Auth::id(),
                'created_at' => now(),
            ]);

            foreach ($request->input('items') as $item) {
                DB::connection($this->connection)->table('CompraDet')->insert([
                    'CompraId' => $compraId,                    
                    'CodPro' => $item['codpro'],                
                    'Cantidad' => (float) $item['cantidad'],   
                    'CostoUnitario' => (float) $item['costo'], 
                    'Subtotal' => (float) $item['cantidad'] * (float) $item['costo'],
                    'Lote' => $item['lote'],                    
                    'Vencimiento' => Carbon::parse($item['vencimiento']), 
                ]);
            }

            // 3. Crear la Deuda (CtaProveedor)
            DB::connection($this->connection)->table('CtaProveedor')->insert([
                'Documento' => $nroFactura,
                'Tipo' => 1, // Asumimos 1 = Factura de Proveedor
                'CodProv' => $proveedorId,
                'FechaF' => $fechaEmision,
                'FechaV' => $fechaVencimiento,
                'Importe' => $request->input('total'),
                'Saldo' => $request->input('total'),
                'Usuario' => Auth::user()->usuario,
                'created_at' => now(),
            ]);

            // 4. Aumentar el Stock (Saldos) - (Esta es la versión que ya corregimos)
            foreach ($request->input('items') as $item) {
                $cantidad = (float) $item['cantidad'];
                $vencimiento = Carbon::parse($item['vencimiento']);
                $protocolo = $item['protocolo'] ?? 0;
                $conditions = [
                    'codpro' => $item['codpro'],
                    'almacen' => $almacenId,
                    'lote' => $item['lote']
                ];

                $existingSaldo = DB::connection($this->connection)
                    ->table('Saldos')
                    ->where($conditions)
                    ->first();

                if ($existingSaldo) {
                    // SI EXISTE: Hacemos UPDATE
                    DB::connection($this->connection)
                        ->table('Saldos')
                        ->where($conditions)
                        ->update([
                            'saldo' => DB::raw("saldo + {$cantidad}"), 
                            'vencimiento' => $vencimiento
                        ]);
                } else {
                    // NO EXISTE: Hacemos INSERT
                    DB::connection($this->connection)
                        ->table('Saldos')
                        ->insert([
                            'codpro' => $item['codpro'],
                            'almacen' => $almacenId,
                            'lote' => $item['lote'],
                            'vencimiento' => $vencimiento,
                            'saldo' => $cantidad, 
                            'protocolo' => $protocolo
                        ]);
                }
            }
            
            // =================================================================
            // 5. Actualizar la Orden de Compra a 'COMPLETADA' - ¡CORREGIDO!
            // =================================================================
            DB::connection($this->connection)->table('OrdenCompraCab')
                ->where('Id', $ordenId)
                ->update([
                    'Estado' => 'COMPLETADA'
                    // Se quitó la línea 'NroGuia' => $nroFactura que causaba el error
                ]);

            // 6. 👨‍💼 LLAMAR AL MOTOR CONTABLE (COMPRA) 👨‍💼
            $proveedor = DB::connection($this->connection)->table('Proveedores')
                            ->where('CodProv', $proveedorId)->first();
                            
            $this->contabilidadService->registrarAsientoCompra(
                $nroFactura,
                $proveedor,
                (float) $request->input('subtotal'),
                (float) $request->input('igv'),
                (float) $request->input('total'),
                $fechaEmision,
                Auth::id()
            );

            // 7. ¡Todo listo!
            DB::connection($this->connection)->commit();

            // Redirigimos a la lista de Cuentas por Pagar (Flujo 3)
            return redirect()->route('contador.cxp.index') 
                ->with('success', "Factura de Compra {$nroFactura} registrada. Stock actualizado y asiento contable generado.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al registrar Compra: " . $e->getMessage(), [
                'factura' => $nroFactura,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error crítico al registrar la compra: ' . $e->getMessage())->withInput();
        }
    }
}