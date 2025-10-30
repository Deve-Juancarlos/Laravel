<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HonorariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));
            $proveedor = $request->input('proveedor');

            // Obtener prestadores de servicios (TipoClie = 3)
            $query = DB::table('Clientes as c')
                ->where('c.TipoClie', 3)
                ->leftJoin('t_Clientes_ubigeo as ub', 'c.Codclie', '=', 'ub.CODIGO')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona');

            if ($proveedor) {
                $query->where(function($q) use ($proveedor) {
                    $q->where('c.Razon', 'like', "%$proveedor%")
                      ->orWhere('c.Documento', 'like', "%$proveedor%");
                });
            }

            $prestadores = $query->select([
                'c.Codclie',
                'c.Razon',
                'c.Documento',
                'c.Direccion',
                'c.Telefono1',
                'c.Celular',
                'c.Email',
                'c.Zona',
                'z.Descripcion as zona_nombre',
                'c.TipoClie',
                'c.Limite',
                'c.Activo',
                'c.Fecha as fecha_registro'
            ])
            ->orderBy('c.Razon')
            ->paginate(50);

            // Obtener documentos de honorarios del período
            $documentosHonorarios = $this->obtenerDocumentosHonorarios($fechaInicio, $fechaFin);

            // Obtener resumen por prestador
            $resumenPrestadores = [];
            foreach ($prestadores as $prestador) {
                $resumenPrestadores[$prestador->Codclie] = $this->obtenerResumenPrestador($prestador->Codclie, $fechaInicio, $fechaFin);
            }

            // Resumen general
            $resumenGeneral = [
                'total_prestadores' => DB::table('Clientes')->where('TipoClie', 3)->count(),
                'prestadores_activos' => DB::table('Clientes')->where('TipoClie', 3)->where('Activo', 1)->count(),
                'total_honorarios_periodo' => $documentosHonorarios->sum('Total'),
                'documentos_emitidos' => $documentosHonorarios->count(),
                'prestadores_con_actividad' => $documentosHonorarios->unique('CodClie')->count()
            ];

            return view('contabilidad.auxiliares.honorarios', compact(
                'prestadores', 'documentosHonorarios', 'resumenPrestadores', 'resumenGeneral', 
                'fechaInicio', 'fechaFin', 'proveedor'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de honorarios: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed statement for honorarium provider
     */
    public function estadoCuenta($prestadorId, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información del prestador
            $prestador = DB::table('Clientes as c')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->where('c.Codclie', $prestadorId)
                ->where('c.TipoClie', 3)
                ->select([
                    'c.Codclie',
                    'c.Razon',
                    'c.Documento',
                    'c.Direccion',
                    'c.Telefono1',
                    'c.Email',
                    'c.Zona',
                    'z.Descripcion as zona_nombre'
                ])
                ->first();

            if (!$prestador) {
                return redirect()->route('libro-honorarios')->with('error', 'Prestador de servicios no encontrado');
            }

            // Documentos de honorarios del prestador
            $documentosHonorarios = DB::table('Doccab as dc')
                ->leftJoin('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->where('dc.CodClie', $prestadorId)
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('dc.Numero', 'like', 'HON%')
                          ->orWhere('dc.Numero', 'like', 'CBO%')
                          ->orWhere('dc.Numero', 'like', 'REC%');
                })
                ->select([
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.Total',
                    'dc.Moneda',
                    'dc.Bruto',
                    'dc.Descuento',
                    'dc.Igv',
                    'dd.Cantidad',
                    'dd.Precio',
                    'dd.Subtotal',
                    'p.Nombre as producto_nombre',
                    'l.Descripcion as laboratorio'
                ])
                ->orderBy('dc.Fecha', 'desc')
                ->get();

            // Clasificar por tipo de documento
            $clasificacionDocumentos = $this->clasificarDocumentos($documentosHonorarios);

            // Resumen mensual
            $resumenMensual = $documentosHonorarios->groupBy(function($item) {
                return Carbon::parse($item->Fecha)->format('Y-m');
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('Total'),
                    'promedio' => $grupo->avg('Total')
                ];
            });

            // Totales
            $totales = [
                'total_honorarios' => $documentosHonorarios->sum('Total'),
                'total_documentos' => $documentosHonorarios->count(),
                'promedio_honorario' => $documentosHonorarios->count() > 0 ? 
                    $documentosHonorarios->sum('Total') / $documentosHonorarios->count() : 0
            ];

            return view('contabilidad.auxiliares.honorarios-estado-cuenta', compact(
                'prestador', 'documentosHonorarios', 'clasificacionDocumentos', 'resumenMensual', 
                'totales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get honorarium documents analysis by category
     */
    public function analisisCategorias(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener todos los documentos de honorarios
            $documentosGenerales = DB::table('Doccab as dc')
                ->leftJoin('Clientes as cl', 'dc.CodClie', '=', 'cl.Codclie')
                ->leftJoin('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('dc.Numero', 'like', 'HON%')
                          ->orWhere('dc.Numero', 'like', 'CBO%')
                          ->orWhere('dc.Numero', 'like', 'REC%');
                })
                ->select([
                    'dc.CodClie',
                    'cl.Razon',
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.Total',
                    'dc.Moneda',
                    'dd.Codpro',
                    'p.Nombre as producto_nombre',
                    'p.Clinea'
                ])
                ->get();

            // Clasificar por categorías de servicios
            $categorias = $this->clasificarCategoriasDocumentos($documentosGenerales);

            // Calcular totales por categoría
            $totalesCategorias = [];
            foreach ($categorias as $categoria => $documentos) {
                $totalesCategorias[$categoria] = [
                    'cantidad_documentos' => $documentos->count(),
                    'prestadores_diferentes' => $documentos->unique('CodClie')->count(),
                    'total_facturado' => $documentos->sum('Total'),
                    'promedio_documento' => $documentos->avg('Total'),
                    'mayor_factura' => $documentos->max('Total')
                ];
            }

            // Análisis de crecimiento por categoría
            $crecimientoCategorias = $this->analizarCrecimientoCategorias($categorias, $fechaInicio, $fechaFin);

            return view('contabilidad.auxiliares.honorarios-categorias', compact(
                'categorias', 'totalesCategorias', 'crecimientoCategorias', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de categorías: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly honorarium report
     */
    public function resumenMensual(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);

            // Resumen mensual de honorarios
            $resumenMensual = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $honorariosMes = DB::table('Doccab as dc')
                    ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                    ->where(function($query) {
                        $query->where('dc.Numero', 'like', 'HON%')
                              ->orWhere('dc.Numero', 'like', 'CBO%')
                              ->orWhere('dc.Numero', 'like', 'REC%');
                    })
                    ->select([
                        DB::raw('COUNT(*) as cantidad'),
                        DB::raw('SUM(dc.Total) as total'),
                        DB::raw('AVG(dc.Total) as promedio')
                    ])
                    ->first();

                $resumenMensual[$mes] = [
                    'mes' => Carbon::create($anio, $mes, 1)->format('F'),
                    'cantidad' => $honorariosMes->cantidad ?? 0,
                    'total' => $honorariosMes->total ?? 0,
                    'promedio' => $honorariosMes->promedio ?? 0
                ];
            }

            // Top prestadores del año
            $topPrestadores = DB::table('Doccab as dc')
                ->leftJoin('Clientes as cl', 'dc.CodClie', '=', 'cl.Codclie')
                ->whereYear('dc.Fecha', $anio)
                ->where(function($query) {
                    $query->where('dc.Numero', 'like', 'HON%')
                          ->orWhere('dc.Numero', 'like', 'CBO%')
                          ->orWhere('dc.Numero', 'like', 'REC%');
                })
                ->select([
                    'dc.CodClie',
                    'cl.Razon',
                    DB::raw('SUM(dc.Total) as total'),
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->groupBy('dc.CodClie', 'cl.Razon')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // Análisis de tendencias
            $tendencias = $this->calcularTendenciasHonorarios($resumenMensual, $anio);

            return view('contabilidad.auxiliares.honorarios-mensual', compact(
                'resumenMensual', 'topPrestadores', 'tendencias', 'anio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en resumen mensual: ' . $e->getMessage());
        }
    }

    /**
     * Get professional services analysis for pharmacy
     */
    public function serviciosFarmaceuticos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Servicios profesionales específicos para farmacia
            $serviciosFarmaceuticos = DB::table('Doccab as dc')
                ->leftJoin('Clientes as cl', 'dc.CodClie', '=', 'cl.Codclie')
                ->leftJoin('Docdet as dd', function($join) {
                    $join->on('dc.Numero', '=', 'dd.Numero')
                         ->on('dc.Tipo', '=', 'dd.Tipo');
                })
                ->leftJoin('Productos as p', 'dd.Codpro', '=', 'p.CodPro')
                ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where(function($q) {
                        $q->where('cl.Razon', 'like', '%Q.F.%')
                          ->orWhere('cl.Razon', 'like', '%QUIMICO%')
                          ->orWhere('cl.Razon', 'like', '%FARMACEUT%')
                          ->orWhere('cl.Razon', 'like', '%REGENTE%')
                          ->orWhere('cl.Razon', 'like', '%ASESOR%');
                    });
                })
                ->select([
                    'dc.CodClie',
                    'cl.Razon',
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.Total',
                    'p.Nombre as producto_nombre',
                    'l.Descripcion as laboratorio'
                ])
                ->orderBy('dc.Total', 'desc')
                ->get();

            // Clasificar tipos de servicios farmacéuticos
            $clasificacionServicios = [
                'ASESOR_QUIMICO_FARMACEUTICO' => [],
                'SERVICIOS_REGENCIA' => [],
                'CAPACITACION_ENTRENAMIENTO' => [],
                'ASESORIA_REGULATORIA' => [],
                'OTROS_SERVICIOS' => []
            ];

            foreach ($serviciosFarmaceuticos as $servicio) {
                $nombre = strtoupper($servicio->Razon);
                
                if (strpos($nombre, 'Q.F.') !== false || strpos($nombre, 'QUIMICO') !== false) {
                    $clasificacionServicios['ASESOR_QUIMICO_FARMACEUTICO'][] = $servicio;
                } elseif (strpos($nombre, 'REGENTE') !== false) {
                    $clasificacionServicios['SERVICIOS_REGENCIA'][] = $servicio;
                } elseif (strpos($nombre, 'CAPACIT') !== false || strpos($nombre, 'ENTREN') !== false) {
                    $clasificacionServicios['CAPACITACION_ENTRENAMIENTO'][] = $servicio;
                } elseif (strpos($nombre, 'ASESOR') !== false || strpos($nombre, 'REGULA') !== false) {
                    $clasificacionServicios['ASESORIA_REGULATORIA'][] = $servicio;
                } else {
                    $clasificacionServicios['OTROS_SERVICIOS'][] = $servicio;
                }
            }

            // Totales por categoría
            $totalesServicios = [];
            foreach ($clasificacionServicios as $categoria => $servicios) {
                $totalesServicios[$categoria] = [
                    'cantidad' => $servicios->count(),
                    'total' => collect($servicios)->sum('Total'),
                    'prestadores' => collect($servicios)->unique('CodClie')->count()
                ];
            }

            return view('contabilidad.auxiliares.honorarios-farmaceuticos', compact(
                'clasificacionServicios', 'totalesServicios', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en servicios farmacéuticos: ' . $e->getMessage());
        }
    }

    /**
     * Get tax analysis for honorariums
     */
    public function analisisImpuestos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Análisis de honorarios con retenciones (asumiendo que son servicios profesionales)
            $honorariosConRetencion = DB::table('Doccab as dc')
                ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('dc.Numero', 'like', 'HON%')
                          ->orWhere('dc.Numero', 'like', 'CBO%');
                })
                ->select([
                    'dc.Numero',
                    'dc.Fecha',
                    'dc.Total',
                    'dc.Igv',
                    DB::raw('CASE 
                        WHEN dc.Total <= 1500 THEN dc.Total * 0.08  -- 8% hasta S/. 1,500
                        ELSE 1500 * 0.08 + (dc.Total - 1500) * 0.10  -- 10% sobre el exceso
                    END as retencion_estimada')
                ])
                ->get();

            // Calcular totales
            $totalHonorarios = $honorariosConRetencion->sum('Total');
            $totalRetenciones = $honorariosConRetencion->sum('retencion_estimada');
            $netoPagar = $totalHonorarios - $totalRetenciones;

            // Resumen por rangos
            $resumenRangos = [
                'hasta_1500' => $honorariosConRetencion->where('Total', '<=', 1500)->sum('Total'),
                'mayor_1500' => $honorariosConRetencion->where('Total', '>', 1500)->sum('Total')
            ];

            return view('contabilidad.auxiliares.honorarios-impuestos', compact(
                'honorariosConRetencion', 'totalHonorarios', 'totalRetenciones', 'netoPagar', 
                'resumenRangos', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en análisis de impuestos: ' . $e->getMessage());
        }
    }

    /**
     * Get honorarium expense forecast
     */
    public function proyeccionGastos(Request $request)
    {
        try {
            $anio = $request->input('anio', Carbon::now()->year);

            // Obtener gastos históricos por mes
            $gastosHistoricos = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->format('Y-m-d');
                $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

                $gastoMes = DB::table('Doccab as dc')
                    ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
                    ->where(function($query) {
                        $query->where('dc.Numero', 'like', 'HON%')
                              ->orWhere('dc.Numero', 'like', 'CBO%')
                              ->orWhere('dc.Numero', 'like', 'REC%');
                    })
                    ->sum('dc.Total') ?? 0;

                $gastosHistoricos[$mes] = $gastoMes;
            }

            // Proyectar gastos futuros basado en promedio histórico
            $promedioMensual = array_sum($gastosHistoricos) / count(array_filter($gastosHistoricos));
            $proyeccionAnual = $promedioMensual * 12;

            // Clasificar proyecciones por categoría
            $proyeccionesCategoria = $this->proyectarPorCategoria($gastosHistoricos);

            // Alertas de presupuesto
            $alertasPresupuesto = $this->generarAlertasPresupuesto($gastosHistoricos, $anio);

            return view('contabilidad.auxiliares.honorarios-proyeccion', compact(
                'gastosHistoricos', 'promedioMensual', 'proyeccionAnual', 
                'proyeccionesCategoria', 'alertasPresupuesto', 'anio
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en proyección: ' . $e->getMessage());
        }
            }}

    /**
     * Get honorarium documents by date range
     */
    private function obtenerDocumentosHonorarios($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->leftJoin('Clientes as cl', 'dc.CodClie', '=', 'cl.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where(function($query) {
                $query->where('dc.Numero', 'like', 'HON%')
                      ->orWhere('dc.Numero', 'like', 'CBO%')
                      ->orWhere('dc.Numero', 'like', 'REC%');
            })
            ->select([
                'dc.CodClie',
                'cl.Razon',
                'dc.Numero',
                'dc.Fecha',
                'dc.Total',
                'dc.Moneda',
                'dc.Igv',
                DB::raw('COUNT(*) OVER() as total_documentos')
            ])
            ->orderBy('dc.Fecha', 'desc')
            ->get();
    }

    /**
     * Get summary for a specific provider
     */
    private function obtenerResumenPrestador($prestadorId, $fechaInicio, $fechaFin)
    {
        $documentos = $this->obtenerDocumentosHonorariosPorPrestador($prestadorId, $fechaInicio, $fechaFin);
        
        return (object)[
            'total_facturado' => $documentos->sum('Total'),
            'total_documentos' => $documentos->count(),
            'promedio_documento' => $documentos->count() > 0 ? $documentos->sum('Total') / $documentos->count() : 0,
            'saldo_pendiente' => $this->obtenerSaldoPendientePrestador($prestadorId)
        ];
    }

    /**
     * Get honorarium documents for specific provider
     */
    private function obtenerDocumentosHonorariosPorPrestador($prestadorId, $fechaInicio, $fechaFin)
    {
        return DB::table('Doccab as dc')
            ->where('dc.CodClie', $prestadorId)
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where(function($query) {
                $query->where('dc.Numero', 'like', 'HON%')
                      ->orWhere('dc.Numero', 'like', 'CBO%')
                      ->orWhere('dc.Numero', 'like', 'REC%');
            })
            ->select([
                'dc.Numero',
                'dc.Fecha',
                'dc.Total',
                'dc.Moneda'
            ])
            ->orderBy('dc.Fecha', 'desc')
            ->get();
    }

    /**
     * Get pending balance for provider
     */
    private function obtenerSaldoPendientePrestador($prestadorId)
    {
        // Sumar saldos pendientes en cuentas por cobrar del prestador
        return DB::table('CtaCliente as cc')
            ->where('cc.CodClie', $prestadorId)
            ->sum('cc.Saldo') ?? 0;
    }

    /**
     * Classify documents by type
     */
    private function clasificarDocumentos($documentos)
    {
        $clasificacion = [
            'HONORARIOS' => [],
            'CONSULTORIA' => [],
            'RECIBOS' => [],
            'OTROS' => []
        ];

        foreach ($documentos as $documento) {
            $numero = strtoupper($documento->Numero);
            
            if (strpos($numero, 'HON') !== false) {
                $clasificacion['HONORARIOS'][] = $documento;
            } elseif (strpos($numero, 'CBO') !== false) {
                $clasificacion['CONSULTORIA'][] = $documento;
            } elseif (strpos($numero, 'REC') !== false) {
                $clasificacion['RECIBOS'][] = $documento;
            } else {
                $clasificacion['OTROS'][] = $documento;
            }
        }

        return $clasificacion;
    }