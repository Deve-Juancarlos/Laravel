<?php

namespace App\Services;

use App\Helpers\NumberToWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Common\EccLevel;



/**
 * Servicio completo para generar facturas y boletas con XML, PDF y QR
 * Uso: Genera comprobantes SUNAT (01=Factura, 03=Boleta)
 */
class FacturaService
{
    protected $connection = 'sqlsrv';
    protected $empresa;

    public function __construct()
    {
        $this->empresa = config('empresa');
    }

    /**
     * Obtiene datos completos de una factura/boleta
     */
    /**
 * Obtiene datos completos de una factura/boleta
 * 
 * @param string $numero Número del comprobante
 * @param int $tipo Tipo de comprobante (1=Factura, 3=Boleta)
 * @return object El comprobante encontrado
 * @throws \Exception Si el comprobante no existe
 */
    public function obtenerComprobante($numero, $tipo): object
    {
        $numero = trim($numero);

        $comprobante = DB::connection($this->connection)
            ->table('Doccab as dc')
            ->join('Clientes as c', 'dc.CodClie', '=', 'c.Codclie')
            ->leftJoin('Empleados as e', 'dc.Vendedor', '=', 'e.Codemp')
            ->leftJoin('Tablas as t_moneda', function($join) {
                $join->on('t_moneda.n_numero', '=', 'dc.Moneda')
                    ->where('t_moneda.n_codtabla', '=', 5);
            })
            ->leftJoin('Tablas as t_doc', function($join) {
                $join->on('t_doc.n_numero', '=', 'dc.Tipo')
                    ->where('t_doc.n_codtabla', '=', 3);
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

        if (!$comprobante) {
            throw new \Exception('Comprobante no encontrado: ' . $numero);
        }

        return $comprobante;
    }

    /**
     * Obtiene detalles de un comprobante
     */
    public function obtenerDetalles($numero, $tipo)
    {
        return DB::connection($this->connection)
            ->table('Docdet as dd')
            ->join('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
            ->where('dd.Numero', trim($numero))
            ->where('dd.Tipo', $tipo)
            ->select('dd.*', 'p.Nombre as ProductoNombre', 'p.CodBar')
            ->get();
    }

    /**
     * Prepara datos para PDF/Vista
     */
    public function prepararDatos($comprobante, $detalles)
    {
        $fechaEmision = Carbon::parse($comprobante->Fecha);
        $fechaVenc = Carbon::parse($comprobante->FechaV);
        $condicionPago = $fechaEmision->diffInDays($fechaVenc, false) > 1 ? 'Crédito' : 'Contado';
        $totalEnLetras = NumberToWords::convert($comprobante->Total, $comprobante->MonedaNombre);

        // Prepara una versión serializable de los detalles para uso en JS
        $detallesParaJs = $detalles->map(function($item) {
            return [
                'Codpro' => $item->Codpro ?? ($item->codigo ?? null),
                'Cantidad' => (float)($item->Cantidad ?? $item->cantidad ?? 0),
                'UnitCode' => 'NIU',
                'ProductoNombre' => $item->ProductoNombre ?? ($item->descripcion ?? ''),
                'PrecioUnitarioSinIgv' => (float)($item->Precio ?? $item->precio ?? 0),
                'Subtotal' => (float)($item->Subtotal ?? ($item->Precio ?? $item->precio ?? 0) * ($item->Cantidad ?? $item->cantidad ?? 1)),
            ];
        })->toArray();

        // Generar QR para la vista/PDF
        $codigoQr = $this->generarQr($comprobante);

        return [
            'factura' => $comprobante,
            'detalles' => $detalles,
            'detallesParaJs' => $detallesParaJs,
            'empresa' => $this->empresa,
            'condicionPago' => $condicionPago,
            'totalEnLetras' => $totalEnLetras,
            'tipoComprobante' => $comprobante->Tipo == 1 ? 'FACTURA' : 'BOLETA',
            'codigoQr' => $codigoQr,
        ];
    }

    /**
     * Genera QR para SUNAT
     * Formato: RUC|TipoDoc|Serie|Número|IGV|Total|FechaEmisión|RucCliente|RazónSocial|MontoTotal
     */
    /**
 * Genera QR para SUNAT
 * Formato: RUC|TipoDoc|Serie|Número|IGV|Total|FechaEmisión|RucCliente|RazónSocial|MontoTotal
 * 
 * @param mixed $comprobante
 * @return string|null
 */
    public function generarQr($comprobante): ?string
    {
        try {
            $seria = substr($comprobante->Numero, 0, 4);
            $numero = substr($comprobante->Numero, 5);

            $qrData = implode('|', [
                $this->empresa['ruc'],
                str_pad($comprobante->Tipo, 2, '0', STR_PAD_LEFT),
                $seria,
                $numero,
                number_format($comprobante->Igv, 2, '.', ''),
                number_format($comprobante->Total, 2, '.', ''),
                Carbon::parse($comprobante->Fecha)->format('Y-m-d'),
                $comprobante->ClienteRuc,
                $comprobante->ClienteNombre,
                number_format($comprobante->Total, 2, '.', ''),
            ]);

            $options = new QROptions([
                'version'      => 5,
                'outputBase64' => false,
                'eccLevel'     => EccLevel::L,
                'scale'        => 5,
                'imageTransparent' => false,
            ]);

            $qrCode = new QRCode($options);
            $qrCode->addByteSegment($qrData);
            
            $qrOutputInterface = new QRGdImagePNG($options, $qrCode->getQRMatrix());
            $imageData = $qrOutputInterface->dump();

            return base64_encode($imageData);

        } catch (\Exception $e) {
            Log::warning('Error al generar QR: ' . $e->getMessage());
            return $this->generarQrFallback($comprobante);
        }
    }


    /**
     * QR fallback (placeholder)
     */
    private function generarQrFallback($comprobante)
    {
        try {
            $contenidoQR = $comprobante->Numero ?? 'Sin número';

            $options = new QROptions([
                'version'      => 5,
                'outputBase64' => false,
                'eccLevel'     => EccLevel::L, // ✅ Actualizado
                'scale'        => 5,
            ]);

            $qrCode = new QRCode($options);
            $qrCode->addByteSegment($contenidoQR); // Método moderno

            $qrOutputInterface = new QRGdImagePNG($options, $qrCode->getQRMatrix());
            $imageData = $qrOutputInterface->dump();

            return base64_encode($imageData);

        } catch (\Exception $e) {
            Log::warning('Error QR fallback: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Genera PDF del comprobante
     */
    public function generarPdf($numero, $tipo)
    {
        try {
            $comprobante = $this->obtenerComprobante($numero, $tipo);
            $detalles = $this->obtenerDetalles($numero, $tipo);
            $datos = $this->prepararDatos($comprobante, $detalles);

            $pdf = Pdf::loadView('ventas.show', $datos)
                ->setPaper('a4')
                ->setOption('margin-top', 0)
                ->setOption('margin-right', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('dpi', 300);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Descarga PDF
     */
    public function descargarPdf($numero, $tipo)
    {
        $pdf = $this->generarPdf($numero, $tipo);
        $nombreArchivo = 'Comprobante_' . trim($numero) . '_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Obtiene PDF como string
     */
    public function obtenerPdfContenido($numero, $tipo)
    {
        $pdf = $this->generarPdf($numero, $tipo);
        return $pdf->output();
    }
}
