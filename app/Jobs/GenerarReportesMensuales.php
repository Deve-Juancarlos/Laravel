<?php

namespace App\Jobs;

use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerarReportesMensuales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mes;
    protected $año;
    protected $cacheService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hora

    /**
     * Create a new job instance.
     *
     * @param int $año
     * @param int $mes
     */
    public function __construct(int $año = null, int $mes = null)
    {
        $fecha = $año && $mes ? Carbon::create($año, $mes) : Carbon::now()->subMonth();
        $this->año = $fecha->year;
        $this->mes = $fecha->month;
        $this->cacheService = new CacheService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Iniciando generación de reportes mensuales', [
            'año' => $this->año,
            'mes' => $this->mes,
            'periodo' => $this->año . '-' . str_pad($this->mes, 2, '0', STR_PAD_LEFT),
            'job_id' => $this->job->getJobId()
        ]);

        try {
            DB::beginTransaction();

            // 1. Generar reporte de ventas
            $reporteVentas = $this->generarReporteVentas();
            
            // 2. Generar reporte de compras
            $reporteCompras = $this->generarReporteCompras();
            
            // 3. Generar reporte de inventario
            $reporteInventario = $this->generarReporteInventario();
            
            // 4. Generar reporte financiero
            $reporteFinanciero = $this->generarReporteFinanciero();
            
            // 5. Generar reporte de medicamentos controlados
            $reporteMedicamentosControlados = $this->generarReporteMedicamentosControlados();
            
            // 6. Generar reporte de proveedores
            $reporteProveedores = $this->generarReporteProveedores();
            
            // 7. Generar reporte de clientes
            $reporteClientes = $this->generarReporteClientes();
            
            // 8. Crear archivo Excel consolidado
            $archivosGenerados = $this->crearArchivosExcel([
                'ventas' => $reporteVentas,
                'compras' => $reporteCompras,
                'inventario' => $reporteInventario,
                'financiero' => $reporteFinanciero,
                'medicamentos_controlados' => $reporteMedicamentosControlados,
                'proveedores' => $reporteProveedores,
                'clientes' => $reporteClientes
            ]);
            
            // 9. Enviar reportes por email
            $this->enviarReportesPorEmail($archivosGenerados);
            
            // 10. Actualizar estado del reporte mensual
            $this->actualizarEstadoReporteMensual($archivosGenerados);
            
            DB::commit();

            Log::info('Generación de reportes mensuales completada', [
                'año' => $this->año,
                'mes' => $this->mes,
                'archivos_generados' => count($archivosGenerados),
                'job_id' => $this->job->getJobId()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en generación de reportes mensuales', [
                'año' => $this->año,
                'mes' => $this->mes,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generar reporte de ventas
     */
    private function generarReporteVentas(): array
    {
        Log::info('Generando reporte de ventas');

        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $ventas = DB::table('facturas')
            ->leftJoin('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->leftJoin('accesoweb', 'facturas.usuario_id', '=', 'accesoweb.id')
            ->where('facturas.fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('facturas.fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->whereIn('facturas.estado', ['APROBADA', 'PENDIENTE'])
            ->select(
                'facturas.id',
                'facturas.numero_factura',
                'facturas.fecha_emision',
                'facturas.subtotal',
                'facturas.igv',
                'facturas.total',
                'clientes.nombre as cliente_nombre',
                'clientes.dni as cliente_dni',
                'accesoweb.nombre as vendedor_nombre'
            )
            ->orderBy('facturas.fecha_emision')
            ->get();

        $resumen = [
            'total_facturas' => $ventas->count(),
            'total_ventas' => $ventas->sum('total'),
            'total_subtotal' => $ventas->sum('subtotal'),
            'total_igv' => $ventas->sum('igv'),
            'promedio_por_factura' => $ventas->avg('total'),
            'ventas_por_vendedor' => $ventas->groupBy('vendedor_nombre')->map->sum('total'),
            'datos_detallados' => $ventas->toArray()
        ];

        return $resumen;
    }

    /**
     * Generar reporte de compras
     */
    private function generarReporteCompras(): array
    {
        Log::info('Generando reporte de compras');

        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $compras = DB::table('compras')
            ->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->leftJoin('accesoweb', 'compras.usuario_id', '=', 'accesoweb.id')
            ->where('compras.fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('compras.fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->where('compras.estado', 'APROBADA')
            ->select(
                'compras.*',
                'proveedores.nombre as proveedor_nombre',
                'proveedores.ruc as proveedor_ruc',
                'accesoweb.nombre as usuario_nombre'
            )
            ->orderBy('compras.fecha_emision')
            ->get();

        $resumen = [
            'total_compras' => $compras->count(),
            'total_importe' => $compras->sum('total'),
            'total_subtotal' => $compras->sum('subtotal'),
            'total_igv' => $compras->sum('igv'),
            'compras_por_proveedor' => $compras->groupBy('proveedor_nombre')->map->sum('total'),
            'datos_detallados' => $compras->toArray()
        ];

        return $resumen;
    }

    /**
     * Generar reporte de inventario
     */
    private function generarReporteInventario(): array
    {
        Log::info('Generando reporte de inventario');

        // Productos con stock
        $productos = DB::table('productos')
            ->leftJoin('lotes_productos', 'productos.id', '=', 'lotes_productos.producto_id')
            ->leftJoin('categorias_productos', 'productos.categoria_id', '=', 'categorias_productos.id')
            ->where('productos.activo', true)
            ->select(
                'productos.*',
                'lotes_productos.stock as stock_lote',
                'lotes_productos.fecha_vencimiento',
                'lotes_productos.lote',
                'categorias_productos.nombre as categoria_nombre'
            )
            ->get();

        $resumen = [
            'total_productos' => $productos->count(),
            'productos_por_categoria' => $productos->groupBy('categoria_nombre')->count(),
            'productos_vencidos' => $this->contarProductosVencidos(),
            'productos_proximos_vencer' => $this->contarProductosProximosVencer(),
            'valor_total_inventario' => $this->calcularValorTotalInventario(),
            'productos_sin_stock' => $this->contarProductosSinStock(),
            'productos_controlados' => $this->contarProductosControlados(),
            'datos_detallados' => $productos->toArray()
        ];

        return $resumen;
    }

    /**
     * Generar reporte financiero
     */
    private function generarReporteFinanciero(): array
    {
        Log::info('Generando reporte financiero');

        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        // Ingresos
        $ingresos = DB::table('facturas')
            ->where('fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->whereIn('estado', ['APROBADA', 'PENDIENTE'])
            ->sum('total');

        // Egresos
        $egresos = DB::table('compras')
            ->where('fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->where('estado', 'APROBADA')
            ->sum('total');

        // Gastos operativos
        $gastosOperativos = DB::table('gastos_operativos')
            ->where('fecha_gasto', '>=', $fechaInicio->format('Y-m-d'))
            ->where('fecha_gasto', '<=', $fechaFin->format('Y-m-d'))
            ->where('estado', 'APROBADO')
            ->sum('monto');

        $utilidadBruta = $ingresos - $egresos;
        $utilidadNeta = $utilidadBruta - $gastosOperativos;

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'gastos_operativos' => $gastosOperativos,
            'utilidad_bruta' => $utilidadBruta,
            'utilidad_neta' => $utilidadNeta,
            'margen_utilidad_bruta' => $ingresos > 0 ? ($utilidadBruta / $ingresos) * 100 : 0,
            'margen_utilidad_neta' => $ingresos > 0 ? ($utilidadNeta / $ingresos) * 100 : 0,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d')
        ];
    }

    /**
     * Generar reporte de medicamentos controlados
     */
    private function generarReporteMedicamentosControlados(): array
    {
        Log::info('Generando reporte de medicamentos controlados');

        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $medicamentosControlados = DB::table('factura_detalles')
            ->join('facturas', 'factura_detalles.factura_id', '=', 'facturas.id')
            ->join('productos', 'factura_detalles.producto_id', '=', 'productos.id')
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('productos.es_controlado', true)
            ->where('facturas.fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('facturas.fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->select(
                'productos.nombre as medicamento_nombre',
                'productos.codigo as codigo_medicamento',
                'factura_detalles.cantidad',
                'factura_detalles.precio_unitario',
                'facturas.numero_factura',
                'facturas.fecha_emision',
                'clientes.nombre as cliente_nombre',
                'clientes.dni as cliente_dni'
            )
            ->get();

        return [
            'total_medicamentos_vendidos' => $medicamentosControlados->sum('cantidad'),
            'total_ventas_controladas' => $medicamentosControlados->sum(function($item) {
                return $item->cantidad * $item->precio_unitario;
            }),
            'diferentes_medicamentos' => $medicamentosControlados->unique('codigo_medicamento')->count(),
            'clientes_atendidos' => $medicamentosControlados->unique('cliente_dni')->count(),
            'datos_detallados' => $medicamentosControlados->toArray()
        ];
    }

    /**
     * Generar reporte de proveedores
     */
    private function generarReporteProveedores(): array
    {
        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $proveedores = DB::table('compras')
            ->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('compras.fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->select(
                'proveedores.id',
                'proveedores.nombre',
                'proveedores.ruc',
                'proveedores.telefono',
                DB::raw('COUNT(compras.id) as total_compras'),
                DB::raw('SUM(compras.total) as total_comprado')
            )
            ->groupBy('proveedores.id', 'proveedores.nombre', 'proveedores.ruc', 'proveedores.telefono')
            ->orderBy('total_comprado', 'desc')
            ->get();

        return [
            'total_proveedores_activos' => $proveedores->count(),
            'proveedor_principal' => $proveedores->first() ?? null,
            'promedio_compra_por_proveedor' => $proveedores->avg('total_comprado'),
            'datos_detallados' => $proveedores->toArray()
        ];
    }

    /**
     * Generar reporte de clientes
     */
    private function generarReporteClientes(): array
    {
        $fechaInicio = Carbon::create($this->año, $this->mes)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $clientes = DB::table('facturas')
            ->join('clientes', 'facturas.cliente_id', '=', 'clientes.id')
            ->where('facturas.fecha_emision', '>=', $fechaInicio->format('Y-m-d'))
            ->where('facturas.fecha_emision', '<=', $fechaFin->format('Y-m-d'))
            ->select(
                'clientes.id',
                'clientes.nombre',
                'clientes.dni',
                'clientes.telefono',
                'clientes.direccion',
                DB::raw('COUNT(facturas.id) as total_compras'),
                DB::raw('SUM(facturas.total) as total_comprado')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.dni', 'clientes.telefono', 'clientes.direccion')
            ->orderBy('total_comprado', 'desc')
            ->get();

        return [
            'total_clientes_activos' => $clientes->count(),
            'cliente_premium' => $clientes->first() ?? null,
            'promedio_compra_por_cliente' => $clientes->avg('total_comprado'),
            'clientes_frecuentes' => $clientes->where('total_compras', '>', 1)->count(),
            'datos_detallados' => $clientes->toArray()
        ];
    }

    /**
     * Crear archivos Excel
     */
    private function crearArchivosExcel(array $reportes): array
    {
        Log::info('Creando archivos Excel');

        $archivosGenerados = [];
        $periodo = $this->año . '-' . str_pad($this->mes, 2, '0', STR_PAD_LEFT);
        $directorio = storage_path("app/reportes_mensuales/{$periodo}");
        
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        // Crear archivo Excel para cada reporte
        foreach ($reportes as $tipo => $reporte) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Agregar datos según el tipo de reporte
            $this->agregarDatosAExcel($sheet, $reporte, $tipo);
            
            // Guardar archivo
            $nombreArchivo = "reporte_{$tipo}_{$periodo}.xlsx";
            $rutaArchivo = $directorio . '/' . $nombreArchivo;
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($rutaArchivo);
            
            $archivosGenerados[] = $nombreArchivo;
            
            Log::info("Archivo Excel creado: {$nombreArchivo}");
        }

        // Crear archivo consolidado
        $archivoConsolidado = $this->crearArchivoConsolidado($reportes, $directorio, $periodo);
        $archivosGenerados[] = $archivoConsolidado;

        return $archivosGenerados;
    }

    /**
     * Agregar datos a hoja de Excel
     */
    private function agregarDatosAExcel($sheet, array $reporte, string $tipo)
    {
        $fila = 1;
        
        // Título del reporte
        $sheet->setCellValue('A' . $fila, 'REPORTE MENSUAL - ' . strtoupper($tipo));
        $sheet->getStyle('A' . $fila)->getFont()->setBold(true)->setSize(14);
        $fila += 2;
        
        // Resumen
        if (isset($reporte['resumen'])) {
            foreach ($reporte['resumen'] as $campo => $valor) {
                $sheet->setCellValue('A' . $fila, $campo);
                $sheet->setCellValue('B' . $fila, $valor);
                $fila++;
            }
            $fila += 2;
        }
        
        // Datos detallados
        if (isset($reporte['datos_detallados']) && count($reporte['datos_detallados']) > 0) {
            $datos = $reporte['datos_detallados'];
            
            // Headers
            $headers = array_keys($datos[0]);
            foreach ($headers as $col => $header) {
                $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue($columna . $fila, strtoupper(str_replace('_', ' ', $header)));
            }
            $sheet->getStyle('A' . $fila . ':' . $columna . $fila)->getFont()->setBold(true);
            $fila++;
            
            // Datos
            foreach ($datos as $dato) {
                foreach ($headers as $col => $header) {
                    $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->setCellValue($columna . $fila, $dato[$header]);
                }
                $fila++;
            }
        }
    }

    /**
     * Crear archivo consolidado
     */
    private function crearArchivoConsolidado(array $reportes, string $directorio, string $periodo): string
    {
        $spreadsheet = new Spreadsheet();
        
        $hojaIndex = 0;
        foreach ($reportes as $tipo => $reporte) {
            if ($hojaIndex > 0) {
                $spreadsheet->createSheet();
            }
            $spreadsheet->setActiveSheetIndex($hojaIndex);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(ucfirst($tipo));
            
            $this->agregarDatosAExcel($sheet, $reporte, $tipo);
            $hojaIndex++;
        }
        
        $nombreArchivo = "reporte_consolidado_{$periodo}.xlsx";
        $rutaArchivo = $directorio . '/' . $nombreArchivo;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($rutaArchivo);
        
        return $nombreArchivo;
    }

    /**
     * Enviar reportes por email
     */
    private function enviarReportesPorEmail(array $archivosGenerados)
    {
        Log::info('Enviando reportes por email');

        $emailsDestinatarios = config('sifano.emails_reportes', []);
        $periodo = $this->año . '-' . str_pad($this->mes, 2, '0', STR_PAD_LEFT);
        
        foreach ($emailsDestinatarios as $email) {
            Log::info("Enviando reporte a: {$email}");
            // En una implementación real, aquí se enviaría el email
        }
    }

    /**
     * Actualizar estado del reporte mensual
     */
    private function actualizarEstadoReporteMensual(array $archivosGenerados)
    {
        DB::table('reportes_mensuales')->updateOrInsert(
            ['año' => $this->año, 'mes' => $this->mes],
            [
                'estado' => 'GENERADO',
                'archivos_generados' => json_encode($archivosGenerados),
                'fecha_generacion' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Contar productos vencidos
     */
    private function contarProductosVencidos(): int
    {
        return DB::table('lotes_productos')
            ->where('fecha_vencimiento', '<', now()->format('Y-m-d'))
            ->where('stock', '>', 0)
            ->count();
    }

    /**
     * Contar productos próximos a vencer
     */
    private function contarProductosProximosVencer(): int
    {
        return DB::table('lotes_productos')
            ->whereBetween('fecha_vencimiento', [
                now()->format('Y-m-d'),
                now()->addDays(30)->format('Y-m-d')
            ])
            ->where('stock', '>', 0)
            ->count();
    }

    /**
     * Calcular valor total del inventario
     */
    private function calcularValorTotalInventario(): float
    {
        return DB::table('lotes_productos')
            ->join('productos', 'lotes_productos.producto_id', '=', 'productos.id')
            ->where('lotes_productos.stock', '>', 0)
            ->sum(DB::raw('lotes_productos.stock * productos.precio_venta'));
    }

    /**
     * Contar productos sin stock
     */
    private function contarProductosSinStock(): int
    {
        return DB::table('productos')
            ->leftJoin('lotes_productos', 'productos.id', '=', 'lotes_productos.producto_id')
            ->where('productos.activo', true)
            ->where('lotes_productos.stock', 0)
            ->count();
    }

    /**
     * Contar productos controlados
     */
    private function contarProductosControlados(): int
    {
        return DB::table('productos')
            ->where('es_controlado', true)
            ->where('activo', true)
            ->count();
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('Job GenerarReportesMensuales falló', [
            'año' => $this->año,
            'mes' => $this->mes,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}