<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotaCreditoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberToWords;
use App\Services\ContabilidadService;

class NotaCreditoController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $carritoService;
    protected $contabilidadService;

    public function __construct(
        NotaCreditoService $carritoService,
        ContabilidadService $contabilidadService 
    ) {
        $this->middleware('auth');
        $this->carritoService = $carritoService;
        $this->contabilidadService = $contabilidadService;
    }

    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('notas_credito as nc')
            ->join('Clientes as c', 'nc.Codclie', '=', 'c.Codclie');

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('c.Razon', 'LIKE', '%' . $request->q . '%')
                  ->orWhere('nc.Numero', 'LIKE', '%' . $request->q . '%');
            });
        }

        $notas = $query->select('nc.*', 'c.Razon as ClienteNombre')
                       ->orderBy('nc.Fecha', 'desc')
                       ->paginate(25);
        
        return view('ventas.notas_credito.index', [
            'notas' => $notas,
            'filtros' => $request->only('q')
        ]);
    }

    public function create()
    {
        $this->carritoService->olvidar();
        return view('ventas.notas_credito.crear_paso1');
    }

    /**
     * ¡CORREGIDO!
     * PASO 1 (POST): Busca la factura y VALIDA EL SALDO en CtaCliente
     */
    public function buscarFactura(Request $request)
    {
        $request->validate(['numero_factura' => 'required|string']);
        $numero = trim($request->numero_factura);

        // 1. Buscamos en CtaCliente
        $facturaEnCta = DB::connection($this->connection)->table('CtaCliente')
            ->where('Documento', $numero)
            ->whereIn('Tipo', [1, 2]) // Factura o Boleta
            ->first();

        if (!$facturaEnCta) {
            return redirect()->back()->with('error', "Factura no encontrada en Cuentas por Cobrar (CtaCliente).")->withInput();
        }
        
        // 2. ¡VALIDACIÓN CLAVE!
        if ($facturaEnCta->Saldo <= 0.01) { // Damos margen de 1 centavo
             return redirect()->back()->with('error', "La factura {$numero} ya ha sido pagada o anulada (Saldo: S/ {$facturaEnCta->Saldo}). No se puede aplicar otra Nota de Crédito.")->withInput();
        }

        // 3. Si el saldo es válido, buscamos los datos de Doccab
        $factura = DB::connection($this->connection)->table('Doccab')
            ->where('Numero', $numero)
            ->where('Tipo', $facturaEnCta->Tipo)
            ->first();
        
        if (!$factura) {
             return redirect()->back()->with('error', "Error de Integridad: La factura {$numero} existe en CtaCliente pero no en Doccab.");
        }

        $cliente = DB::connection($this->connection)->table('Clientes')
            ->where('Codclie', $factura->CodClie)->first();

        // 4. ¡CORREGIDO! Iniciamos el carrito pasando el saldo máximo
        $this->carritoService->iniciar($factura, $cliente, $facturaEnCta->Saldo);

        return redirect()->route('contador.notas-credito.showPaso2');
    }

    /**
     * PASO 2 (GET): Muestra los items a devolver
     */
    public function showPaso2()
    {
        $carrito = $this->carritoService->get();
        if (!$carrito) {
            return redirect()->route('contador.notas-credito.create');
        }

        $detalles = DB::connection($this->connection)->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $carrito['factura_original']->Numero)
            ->where('dd.Tipo', $carrito['factura_original']->Tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre')
            ->get();
            
        return view('ventas.notas_credito.crear_paso2', [
            'carrito' => $carrito,
            'detalles' => $detalles,
            'saldo_maximo' => $carrito['saldo_maximo'] // Pasamos el saldo
        ]);
    }

    /**
     * ¡CORREGIDO!
     * PASO 3: Guarda la Nota de Crédito (Afectando Saldo de CtaCliente)
     */
    public function store(Request $request)
    {
        $carrito = $this->carritoService->get();
        if (!$carrito) {
            return redirect()->route('contador.notas-credito.create')->with('error', 'Sesión expirada.');
        }

        // ... (Tu validación está perfecta) ...
        $request->validate([
            'tipo_operacion' => 'required|in:devolucion,descuento',
            'motivo_glosa' => 'required|string|max:255',
            'monto_descuento' => 'nullable|numeric|min:0.01|required_if:tipo_operacion,descuento',
            'items' => 'nullable|array|required_if:tipo_operacion,devolucion',
        ]);
        
        $facturaOriginal = $carrito['factura_original'];

        DB::connection($this->connection)->beginTransaction();
        try {
            
            // 1. Validar y Calcular Totales (usando el servicio)
            $carritoActualizado = $this->carritoService->actualizarCarrito(
                $request->input('tipo_operacion'),
                $request->input('motivo_glosa'),
                $request->input('items', []),
                $request->input('monto_descuento', 0)
            );
            
            $totalNC = $carritoActualizado['totales']['total'];
            $subtotalNC = $carritoActualizado['totales']['subtotal'];
            $igvNC = $carritoActualizado['totales']['igv'];
            
            // 2. Generar el correlativo
            $tipoDocNC = 8; // Asumo Tipo 8 = Nota de Crédito
            $ultimoNum = DB::connection($this->connection)->table('notas_credito')->max('Numero');
            $nuevoNumInt = $ultimoNum ? (int)preg_replace('/[^0-9]/', '', $ultimoNum) + 1 : 1;
            $numeroNC = 'NC01-' . str_pad($nuevoNumInt, 8, '0', STR_PAD_LEFT);

            // 3. Guardar la Cabecera (notas_credito)
            // ... (Tu lógica de guardar en 'notas_credito' está perfecta) ...
            DB::connection($this->connection)->table('notas_credito')->insert([
                'Numero' => $numeroNC,
                'TipoNota' => $tipoDocNC,
                'Fecha' => now(),
                'Documento' => $facturaOriginal->Numero,
                'TipoDoc' => $facturaOriginal->Tipo,
                'Codclie' => $carrito['cliente']->Codclie,
                'Monto' => $subtotalNC,
                'Igv' => $igvNC,
                'Total' => $totalNC,
                'Observacion' => $carritoActualizado['motivo'],
                'Anulado' => 0
            ]);

            // 4. Si fue DEVOLUCIÓN, guardar detalles y devolver stock
            // ... (Tu lógica de 'notas_credito_deta' y 'Saldos' está perfecta) ...
            if ($carritoActualizado['tipo_operacion'] == 'devolucion') {
                foreach ($carritoActualizado['items_devueltos'] as $item) {
                    $item = (object)$item; 
                    DB::connection($this->connection)->table('notas_credito_deta')->insert([
                        'Numero' => $numeroNC, 'TipoNota' => $tipoDocNC,
                        'Codpro' => $item->codpro, 'Lote' => $item->lote,
                        'Vencimiento' => $item->vencimiento,
                        'Cantidad' => $item->cantidad, 'Precio' => $item->precio,
                        'Subtotal' => ($item->cantidad * $item->precio)
                    ]);
                    // Devolver Stock
                    DB::connection($this->connection)->table('Saldos')->where('codpro', $item->codpro)->where('lote', $item->lote)->increment('saldo', $item->cantidad);
                    DB::connection($this->connection)->table('Productos')->where('CodPro', $item->codpro)->increment('Stock', $item->cantidad);
                }
            }

            // 5. ¡¡¡LÓGICA CONTABLE CORRECTA!!!
            // ... (Tu lógica de 'CtaCliente' está perfecta) ...
            DB::connection($this->connection)->table('CtaCliente')
                ->where('Documento', $facturaOriginal->Numero)
                ->where('Tipo', $facturaOriginal->Tipo)
                ->decrement('Saldo', $totalNC);

            DB::connection($this->connection)->table('CtaCliente')->insert([
                'Documento' => $numeroNC,
                'Tipo' => $tipoDocNC,
                'CodClie' => $carrito['cliente']->Codclie,
                'FechaF' => now(), 'FechaV' => now(),
                'Importe' => -$totalNC, 'Saldo' => 0
            ]);
            
            // 6. Marcar Doccab como 'Eliminado' si el saldo llega a 0
            // ... (Tu lógica de 'Doccab' está perfecta) ...
            $nuevoSaldo = DB::connection($this->connection)->table('CtaCliente')
                ->where('Documento', $facturaOriginal->Numero)
                ->where('Tipo', $facturaOriginal->Tipo)
                ->value('Saldo');

            if ($nuevoSaldo <= 0.01) {
                 DB::connection($this->connection)->table('Doccab')
                    ->where('Numero', $facturaOriginal->Numero)
                    ->update(['Eliminado' => 1]);
            }
            
            // ================================================================
            // 7. Generar Asiento Contable (¡USANDO EL SERVICIO!)
            // ================================================================
            $this->contabilidadService->registrarAsientoNotaCredito(
                $numeroNC,
                $carritoActualizado,
                $facturaOriginal,
                Auth::id()
            );

            DB::connection($this->connection)->commit();
            $this->carritoService->olvidar();
            
            return redirect()->route('contador.notas-credito.show', $numeroNC)
                             ->with('success', "Nota de Crédito {$numeroNC} generada. El saldo de la factura {$facturaOriginal->Numero} ha sido reducido.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al guardar Nota de Crédito: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error crítico al guardar la NC: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra la vista de la Nota de Crédito para imprimir
     */
    public function show($numero)
    {
        $notaCredito = $this->getNotaCreditoCompleta($numero);
        if (!$notaCredito) abort(404, 'Nota de Crédito no encontrada');

        $facturaAfectadaCta = DB::connection($this->connection)->table('CtaCliente')
            ->where('Documento', $notaCredito->factura_afectada->Numero)
            ->where('Tipo', $notaCredito->factura_afectada->Tipo)
            ->first();

        $totalEnLetras = NumberToWords::convert($notaCredito->Total, 'SOLES');

        return view('ventas.notas_credito.show', [
            'notaCredito' => $notaCredito,
            'detalles' => $notaCredito->detalles,
            'facturaAfectada' => $notaCredito->factura_afectada,
            'cliente' => $notaCredito->cliente,
            'empresa' => $this->getEmpresaDatos(),
            'saldoFacturaOriginal' => $facturaAfectadaCta->Saldo, // <-- El saldo actualizado
            'totalEnLetras' => $totalEnLetras
        ]);
    }

    // --- MÉTODOS PRIVADOS DE AYUDA ---

    private function getNotaCreditoCompleta($numero)
    {
        $notaCredito = DB::connection($this->connection)->table('notas_credito as nc')
            ->join('Clientes as c', 'nc.Codclie', '=', 'c.Codclie')
            ->where('nc.Numero', $numero)
            ->select('nc.*', 'c.Razon as ClienteNombre', 'c.Documento as ClienteRuc', 'c.Direccion as ClienteDireccion')
            ->first();
            
        if(!$notaCredito) return null;

        $notaCredito->detalles = DB::connection($this->connection)->table('notas_credito_deta as ncd')
            ->join('Productos as p', 'ncd.Codpro', '=', 'p.CodPro')
            ->where('ncd.Numero', $notaCredito->Numero)
            ->where('ncd.TipoNota', $notaCredito->TipoNota)
            ->select('ncd.*', 'p.Nombre as ProductoNombre')
            ->get();
        
        $notaCredito->factura_afectada = DB::connection($this->connection)->table('Doccab')
            ->where('Numero', $notaCredito->Documento)
            ->where('Tipo', $notaCredito->TipoDoc)
            ->first();
            
        $notaCredito->cliente = (object)[
            'RazonSocial' => $notaCredito->ClienteNombre,
            'Ruc' => $notaCredito->ClienteRuc,
            'Direccion' => $notaCredito->ClienteDireccion
        ];
        
        return $notaCredito;
    }
    
    private function getEmpresaDatos()
    {
        return [
            'nombre' => 'SEDIMCORP SAC',
            'ruc' => '20123456789',
            'direccion' => 'AV. LOS HEROES 754 OTR. D SAN JUAN DE MIRAFLORES - LIMA - LIMA',
        ];
    }
    
    private function generarAsientoContable($numeroNC, $carrito, $facturaOriginal)
    {
        $usuario = Auth::user()->name ?? 'SYSTEM';
        $total = $carrito['totales']['total'];
        $subtotal = $carrito['totales']['subtotal'];
        $igv = $carrito['totales']['igv'];

        // 1. Generar N° Asiento
        $ultimo = DB::connection($this->connection)->table('libro_diario')
            ->whereYear('fecha', date('Y'))
            ->max('numero');
        $num = $ultimo ? (int)substr($ultimo, 5) + 1 : 1;
        $numeroAsiento = date('Y') . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);

        // 2. Insertar Cabecera
        $asientoId = DB::connection($this->connection)->table('libro_diario')->insertGetId([
            'numero' => $numeroAsiento,
            'fecha' => now(),
            'glosa' => "Por la NC {$numeroNC} (Afecta Fact. {$facturaOriginal->Numero}) - Motivo: {$carrito['motivo']}",
            'total_debe' => $total,
            'total_haber' => $total,
            'balanceado' => 1,
            'estado' => 'ACTIVO',
            'usuario_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $detalles = [];

        // 3. Insertar Detalles
        if ($carrito['tipo_operacion'] == 'devolucion') {
            // Reversión de la venta (Devolución)
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '701101', 'debe' => $subtotal, 'haber' => 0, 'concepto' => "Devolución s/ venta {$facturaOriginal->Numero}" ];
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '401111', 'debe' => $igv, 'haber' => 0, 'concepto' => "Devolución IGV s/ venta" ];
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '121201', 'debe' => 0, 'haber' => $total, 'concepto' => "NC {$numeroNC}" ];
        } else {
            // Descuento
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '675101', 'debe' => $subtotal, 'haber' => 0, 'concepto' => "Descuento s/ venta {$facturaOriginal->Numero}" ]; // Cuenta 67 Descuentos Concedidos
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '401111', 'debe' => $igv, 'haber' => 0, 'concepto' => "IGV del descuento" ];
            $detalles[] = [ 'asiento_id' => $asientoId, 'cuenta_contable' => '121201', 'debe' => 0, 'haber' => $total, 'concepto' => "NC {$numeroNC}" ];
        }
        
        DB::connection($this->connection)->table('libro_diario_detalles')->insert($detalles);
        
        // 4. Auditoría
        DB::connection($this->connection)->table('Auditoria_Sistema')->insert([
            'usuario' => $usuario,
            'accion' => 'CREAR_NOTA_CREDITO',
            'tabla' => 'notas_credito',
            'detalle' => "NC: {$numeroNC} - Factura: {$facturaOriginal->Numero} - Tipo: {$carrito['tipo_operacion']}",
            'fecha' => now()
        ]);
    }
}