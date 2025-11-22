<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Services\XmlSunatService;
use App\Services\FacturaService;
use App\Helpers\NumberToWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class XmlDescargaController extends Controller
{
    protected $xmlService;
    protected $facturaService;
    protected $connection = 'sqlsrv';

    public function __construct(XmlSunatService $xmlService, FacturaService $facturaService)
    {
        $this->middleware('auth');
        $this->xmlService = $xmlService;
        $this->facturaService = $facturaService;
    }

    /**
     * Descarga XML de una factura
     * Ruta: /factura/{numero}/xml/download?tipo={tipo}
     */
    public function descargarXml(Request $request, $numero)
    {
        try {
            $tipo = $request->query('tipo', 1); // 1=Factura, 3=Boleta
            
            // Obtener datos de la factura
            $factura = $this->obtenerFactura($numero, $tipo);
            
            if (!$factura) {
                return response()->json(['error' => 'Factura no encontrada'], 404);
            }

            // Obtener detalles
            $detalles = $this->obtenerDetalles($numero, $tipo);

            // Obtener datos de empresa
            $empresa = $this->obtenerEmpresa();

            // Preparar datos
            $datosFactura = [
                'numero' => $factura->Numero,
                'tipo' => $factura->Tipo,
                'fecha' => $factura->Fecha,
                'moneda' => $factura->Moneda == 1 ? 'SOLES' : 'DOLARES',
                'cliente_ruc' => $factura->ClienteRuc,
                'cliente_nombre' => $factura->ClienteNombre,
                'subtotal' => $factura->Subtotal,
                'igv' => $factura->Igv,
                'descuento' => $factura->Descuento ?? 0,
                'total' => $factura->Total,
            ];

            $datosDetalles = $detalles->map(function($item) {
                return [
                    'codigo' => $item->Codpro,
                    'descripcion' => $item->ProductoNombre,
                    'cantidad' => $item->Cantidad,
                    'precio_unitario' => $item->Precio,
                ];
            })->toArray();

            // Generar XML
            $xml = $this->xmlService->generarXmlFactura(
                $empresa,
                $datosFactura,
                $datosDetalles
            );

            // Validar XML
            if (!$this->xmlService->validarXml($xml)) {
                Log::error('XML inválido generado para factura ' . $numero);
                return response()->json(['error' => 'XML generado inválido'], 500);
            }

            // Descargar XML
            $nombreArchivo = 'Factura_' . $numero . '.xml';
            
            // Registrar auditoría
            $this->registrarAuditoria('DESCARGA_XML', 'Factura descargada en XML', [
                'numero' => $numero,
                'tipo' => $tipo,
                'cliente' => $factura->ClienteNombre,
                'total' => $factura->Total,
                'archivo' => $nombreArchivo,
            ]);
            
            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Error al descargar XML: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descarga factura en PDF usando la vista existente
     * Ruta: /factura/{numero}/pdf/download?tipo={tipo}
     */
    public function descargarPdf(Request $request, $numero)
{
    try {
        $tipo = $request->query('tipo', 1);
        $numero = trim($numero);

        // 1️⃣ PRIMERO: Obtener datos de la factura
        $factura = DB::connection($this->connection)->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')->where('t_moneda.n_codtabla', '=', 5);
            })
            ->leftJoin('Tablas as t_doc', function($join) {
                $join->on('t_doc.n_numero', '=', 'dc.Tipo')->where('t_doc.n_codtabla', '=', 3);
            })
            ->where('dc.Numero', $numero)
            ->where('dc.Tipo', $tipo)
            ->where('dc.Eliminado', 0)
            ->select(
                'dc.*', 
                'c.Razon as ClienteNombre', 
                'c.Documento as ClienteRuc', 
                'c.Direccion as ClienteDireccion', 
                'c.Email as ClienteEmail', 
                'e.Nombre as VendedorNombre',
                DB::raw("ISNULL(t_moneda.c_describe, 'SOLES') as MonedaNombre"),
                DB::raw("ISNULL(t_doc.c_describe, 'DOCUMENTO') as TipoDocNombre")
            )
            ->first();
        
        // 2️⃣ Validar que existe
        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        // 3️⃣ Obtener detalles
        $detalles = DB::connection($this->connection)->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', $numero)
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre', 'p.CodBar')
            ->get();

        // 4️⃣ Obtener empresa
        $empresa = config('empresa') ?? [
            'nombre' => 'SEDIMCORP SAC',
            'ruc' => config('empresa.ruc', '20123456789'),
            'giro' => config('empresa.giro', 'EMPRESA DE DISTRIBUIDORA DE FARMACOS'),
            'direccion' => config('empresa.direccion', 'AV. LOS HEROES 754'),
            'telefono' => config('empresa.telefono', '(01) 555-1234'),
            'email' => config('empresa.email', 'ventas@sedimcorp.com'),
            'web' => config('empresa.web', 'www.sedimcorp.com'),
        ];

        // 5️⃣ Calcular datos adicionales
        $fechaEmision = Carbon::parse($factura->Fecha);
        $fechaVenc = Carbon::parse($factura->FechaV);
        $condicionPago = $fechaEmision->diffInDays($fechaVenc, false) > 1 ? 'Crédito' : 'Contado';
        $totalEnLetras = NumberToWords::convert($factura->Total, $factura->MonedaNombre);

        // 6️⃣ AHORA SÍ: Generar QR (después de que $factura esté definida)
        $codigoQr = $this->facturaService->generarQr($factura);

        // 7️⃣ Preparar datos para la vista usando FacturaService (incluye detallesParaJs y codigoQr)
        $data = $this->facturaService->prepararDatos($factura, $detalles);

        // 8️⃣ Generar PDF
        $pdf = Pdf::loadView('ventas.show', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('dpi', 300);

        $nombreArchivo = 'Factura_' . $numero . '_' . now()->format('Y-m-d_His') . '.pdf';

        // 9️⃣ Registrar auditoría
        $this->registrarAuditoria('DESCARGA_PDF', 'Factura descargada en PDF', [
            'numero' => $numero,
            'tipo' => $tipo,
            'cliente' => $factura->ClienteNombre,
            'total' => $factura->Total,
            'archivo' => $nombreArchivo,
        ]);

        return $pdf->download($nombreArchivo);

    } catch (\Exception $e) {
        Log::error('Error al generar PDF: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    /**
     * Ver XML en navegador (sin descargar)
     * Ruta: /factura/{numero}/xml/view?tipo={tipo}
     */
    public function verXml(Request $request, $numero)
    {
        try {
            $tipo = $request->query('tipo', 1);

            $factura = $this->obtenerFactura($numero, $tipo);
            if (!$factura) {
                return redirect()->back()->with('error', 'Factura no encontrada');
            }

            $detalles = $this->obtenerDetalles($numero, $tipo);
            $empresa = $this->obtenerEmpresa();

            $datosFactura = [
                'numero' => $factura->Numero,
                'tipo' => $factura->Tipo,
                'fecha' => $factura->Fecha,
                'moneda' => $factura->Moneda == 1 ? 'SOLES' : 'DOLARES',
                'cliente_ruc' => $factura->ClienteRuc,
                'cliente_nombre' => $factura->ClienteNombre,
                'subtotal' => $factura->Subtotal,
                'igv' => $factura->Igv,
                'descuento' => $factura->Descuento ?? 0,
                'total' => $factura->Total,
            ];

            $datosDetalles = $detalles->map(function($item) {
                return [
                    'codigo' => $item->Codpro,
                    'descripcion' => $item->ProductoNombre,
                    'cantidad' => $item->Cantidad,
                    'precio_unitario' => $item->Precio,
                ];
            })->toArray();

            $xml = $this->xmlService->generarXmlFactura(
                $empresa,
                $datosFactura,
                $datosDetalles
            );

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');

        } catch (\Exception $e) {
            Log::error('Error al ver XML: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Obtener datos de factura
     */
    private function obtenerFactura($numero, $tipo)
    {
        return DB::connection($this->connection)
            ->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->select(
                'dc.Numero',
                'dc.Tipo',
                'dc.Fecha',
                'dc.FechaV',
                'dc.Moneda',
                'dc.Subtotal',
                'dc.Igv',
                'dc.Descuento',
                'dc.Total',
                'c.Razon as ClienteNombre',
                'c.Documento as ClienteRuc'
            )
            ->where('dc.Numero', $numero)
            ->where('dc.Tipo', $tipo)
            ->where('dc.Eliminado', 0)
            ->first();
    }

    /**
     * Obtener detalles de factura
     */
    private function obtenerDetalles($numero, $tipo)
    {
        return DB::connection($this->connection)
            ->table('Docdet as dd')
            ->join('Productos as p', 'dd.CodPro', '=', 'p.Codpro')
            ->select(
                'dd.Codpro',
                'p.Nombre as ProductoNombre',
                'dd.Cantidad',
                'dd.Precio'
            )
            ->where('dd.Numero', $numero)
            ->where('dd.Tipo', $tipo)
            ->get();
    }

    /**
     * Obtener datos de empresa
     */
    private function obtenerEmpresa()
    {
        // Estos datos deben venir de tu configuración
        return [
            'ruc' => config('empresa.ruc') ?? '20000000001',
            'nombre' => config('empresa.nombre') ?? 'Mi Empresa S.A.C.',
            'web' => config('empresa.web') ?? 'https://empresa.com',
            'direccion' => config('empresa.direccion') ?? 'Jr. Principal 123',
            'telefono' => config('empresa.telefono') ?? '(01) 1234567',
            'email' => config('empresa.email') ?? 'info@empresa.com',
        ];
    }

    /**
     * Convierte número a palabras para factura
     */
    private function numberToWords($number)
    {
        try {
            return NumberToWords::convert($number, 'SOLES');
        } catch (\Exception $e) {
            Log::warning('Error al convertir número a letras: ' . $e->getMessage());
            return 'Error al convertir';
        }
    }

    /**
     * Registra auditoría del sistema
     */
    private function registrarAuditoria($accion, $descripcion, $datos = [])
    {
        try {
            $usuario = \Auth::user();
            $usuario_nombre = $usuario->name ?? 'Sistema';
            
            // Construir detalle con toda la información
            $detalle = $descripcion . ' | IP: ' . request()->ip() . ' | UA: ' . substr(request()->header('User-Agent', ''), 0, 100);
            
            DB::connection($this->connection)->table('auditoria_sistema')->insert([
                'usuario' => $usuario_nombre,
                'accion' => $accion,
                'tabla' => 'Doccab', // Tabla de comprobantes
                'detalle' => $detalle,
                'fecha' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Error al registrar auditoría: ' . $e->getMessage());
            // No detener proceso por error de auditoría
        }
    }

    
}

