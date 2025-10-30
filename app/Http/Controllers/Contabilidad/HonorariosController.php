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

            return view('contabilidad.honorarios.index', compact(
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
                $serviciosCollection = collect($servicios);
                $totalesServicios[$categoria] = [
                    'cantidad' => $serviciosCollection->count(),
                    'total' => $serviciosCollection->sum('Total'),
                    'prestadores' => $serviciosCollection->unique('CodClie')->count()
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
                        WHEN dc.Total <= 1500 THEN dc.Total * 0.08
                        ELSE 1500 * 0.08 + (dc.Total - 1500) * 0.10
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
            $promedioMensual = array_sum($gastosHistoricos) / count(array_filter($gastosHistoricos, function($val) {
                return $val > 0;
            }) ?: 1);
            $proyeccionAnual = $promedioMensual * 12;

            // Clasificar proyecciones por categoría
            $proyeccionesCategoria = $this->proyectarPorCategoria($gastosHistoricos);

            // Alertas de presupuesto
            $alertasPresupuesto = $this->generarAlertasPresupuesto($gastosHistoricos, $anio);

            return view('contabilidad.auxiliares.honorarios-proyeccion', compact(
                'gastosHistoricos', 
                'promedioMensual', 
                'proyeccionAnual', 
                'proyeccionesCategoria', 
                'alertasPresupuesto', 
                'anio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en proyección: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS PRIVADOS ====================

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
                'dc.Igv'
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

    /**
     * Classify documents by service categories
     */
    private function clasificarCategoriasDocumentos($documentos)
    {
        return $documentos->groupBy(function($doc) {
            $nombre = strtoupper($doc->producto_nombre ?? '');
            $razon = strtoupper($doc->Razon ?? '');
            
            // Clasificación por nombre de producto o razón social
            if (strpos($nombre, 'CONSULTOR') !== false || strpos($razon, 'CONSULTOR') !== false) {
                return 'CONSULTORIA';
            } elseif (strpos($nombre, 'CAPACIT') !== false || strpos($razon, 'CAPACIT') !== false) {
                return 'CAPACITACION';
            } elseif (strpos($nombre, 'ASESORIA') !== false || strpos($razon, 'ASESOR') !== false) {
                return 'ASESORIA';
            } elseif (strpos($nombre, 'REGENTE') !== false || strpos($razon, 'REGENTE') !== false) {
                return 'REGENCIA';
            } elseif (strpos($razon, 'Q.F.') !== false || strpos($razon, 'QUIMICO') !== false) {
                return 'SERVICIOS_FARMACEUTICOS';
            } else {
                return 'OTROS_SERVICIOS';
            }
        });
    }

    /**
     * Analyze category growth trends
     */
    private function analizarCrecimientoCategorias($categorias, $fechaInicio, $fechaFin)
    {
        $analisis = [];
        
        foreach ($categorias as $categoria => $documentos) {
            // Dividir período en mitades para comparar
            $fechaInicioDt = Carbon::parse($fechaInicio);
            $fechaFinDt = Carbon::parse($fechaFin);
            $fechaMitad = $fechaInicioDt->copy()->addDays($fechaInicioDt->diffInDays($fechaFinDt) / 2);
            
            $primeraMetad = $documentos->filter(function($doc) use ($fechaInicioDt, $fechaMitad) {
                $fecha = Carbon::parse($doc->Fecha);
                return $fecha->between($fechaInicioDt, $fechaMitad);
            });
            
            $segundaMetad = $documentos->filter(function($doc) use ($fechaMitad, $fechaFinDt) {
                $fecha = Carbon::parse($doc->Fecha);
                return $fecha->between($fechaMitad, $fechaFinDt);
            });
            
            $totalPrimera = $primeraMetad->sum('Total');
            $totalSegunda = $segundaMetad->sum('Total');
            
            $crecimiento = $totalPrimera > 0 
                ? (($totalSegunda - $totalPrimera) / $totalPrimera) * 100 
                : 0;
            
            $analisis[$categoria] = [
                'primera_mitad' => $totalPrimera,
                'segunda_mitad' => $totalSegunda,
                'crecimiento_porcentual' => round($crecimiento, 2),
                'tendencia' => $crecimiento > 10 ? 'CRECIENTE' : ($crecimiento < -10 ? 'DECRECIENTE' : 'ESTABLE')
            ];
        }
        
        return $analisis;
    }

    /**
     * Calculate honorarium trends
     */
    private function calcularTendenciasHonorarios($resumenMensual, $anio)
    {
        $totalesMensuales = array_values(array_map(function($mes) {
            return $mes['total'];
        }, $resumenMensual));
        
        // Filtrar meses con datos
        $mesesConDatos = array_filter($totalesMensuales, function($val) {
            return $val > 0;
        });
        
        if (empty($mesesConDatos)) {
            return [
                'tendencia_general' => 'SIN_DATOS',
                'mes_mayor_actividad' => null,
                'mes_menor_actividad' => null,
                'variacion_promedio' => 0,
                'total_anual' => 0,
                'promedio_mensual' => 0
            ];
        }
        
        // Encontrar mes con mayor y menor actividad
        $maxTotal = max($totalesMensuales);
        $minTotal = min(array_filter($totalesMensuales, function($val) {
            return $val > 0;
        }));
        
        $mesMayor = array_search($maxTotal, $totalesMensuales);
        $mesMenor = array_search($minTotal, $totalesMensuales);
        
        // Calcular tendencia (comparar primera mitad vs segunda mitad)
        $primeraMetad = array_sum(array_slice($totalesMensuales, 0, 6));
        $segundaMetad = array_sum(array_slice($totalesMensuales, 6, 6));
        
        $variacion = $primeraMetad > 0 
            ? (($segundaMetad - $primeraMetad) / $primeraMetad) * 100 
            : 0;
        
        $tendenciaGeneral = $variacion > 10 ? 'CRECIENTE' : ($variacion < -10 ? 'DECRECIENTE' : 'ESTABLE');
        
        return [
            'tendencia_general' => $tendenciaGeneral,
            'mes_mayor_actividad' => $mesMayor !== false ? Carbon::create($anio, $mesMayor + 1, 1)->format('F') : null,
            'mes_menor_actividad' => $mesMenor !== false ? Carbon::create($anio, $mesMenor + 1, 1)->format('F') : null,
            'variacion_promedio' => round($variacion, 2),
            'total_anual' => array_sum($totalesMensuales),
            'promedio_mensual' => count($mesesConDatos) > 0 ? array_sum($totalesMensuales) / count($mesesConDatos) : 0
        ];
    }

    /**
     * Project expenses by category
     */
    private function proyectarPorCategoria($gastosHistoricos)
    {
        // Obtener categorías de los últimos 3 meses
        $fechaInicio = Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m-d');
        $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $documentos = DB::table('Doccab as dc')
            ->leftJoin('Clientes as cl', 'dc.CodClie', '=', 'cl.Codclie')
            ->whereBetween('dc.Fecha', [$fechaInicio, $fechaFin])
            ->where(function($query) {
                $query->where('dc.Numero', 'like', 'HON%')
                      ->orWhere('dc.Numero', 'like', 'CBO%')
                      ->orWhere('dc.Numero', 'like', 'REC%');
            })
            ->select([
                'dc.Total',
                'cl.Razon'
            ])
            ->get();
        
        $categorias = $this->clasificarCategoriasDocumentos($documentos);
        
        $proyecciones = [];
        foreach ($categorias as $categoria => $docs) {
            $promedioMensual = $docs->sum('Total') / 3; // Promedio de 3 meses
            $proyecciones[$categoria] = [
                'promedio_mensual' => round($promedioMensual, 2),
                'proyeccion_anual' => round($promedioMensual * 12, 2),
                'documentos_mes' => round($docs->count() / 3, 0)
            ];
        }
        
        return $proyecciones;
    }

    /**
     * Generate budget alerts
     */
    private function generarAlertasPresupuesto($gastosHistoricos, $anio)
    {
        $alertas = [];
        
        // Calcular promedio mensual
        $mesesConGastos = array_filter($gastosHistoricos, function($val) {
            return $val > 0;
        });
        
        if (empty($mesesConGastos)) {
            return $alertas;
        }
        
        $promedioMensual = array_sum($mesesConGastos) / count($mesesConGastos);
        $desviacionEstandar = $this->calcularDesviacionEstandar($gastosHistoricos, $promedioMensual);
        
        // Analizar cada mes
        foreach ($gastosHistoricos as $mes => $gasto) {
            if ($gasto == 0) continue;
            
            // Alerta si el gasto supera el promedio + 1.5 desviaciones estándar
            if ($gasto > ($promedioMensual + (1.5 * $desviacionEstandar))) {
                $alertas[] = [
                    'tipo' => 'ALTO',
                    'mes' => Carbon::create($anio, $mes, 1)->format('F'),
                    'mensaje' => 'Gasto significativamente superior al promedio',
                    'gasto' => $gasto,
                    'promedio' => $promedioMensual,
                    'diferencia' => $gasto - $promedioMensual,
                    'color' => 'danger'
                ];
            }
            
            // Alerta si el gasto es menor al 50% del promedio
            if ($gasto < ($promedioMensual * 0.5) && $gasto > 0) {
                $alertas[] = [
                    'tipo' => 'BAJO',
                    'mes' => Carbon::create($anio, $mes, 1)->format('F'),
                    'mensaje' => 'Gasto significativamente inferior al promedio',
                    'gasto' => $gasto,
                    'promedio' => $promedioMensual,
                    'diferencia' => $promedioMensual - $gasto,
                    'color' => 'warning'
                ];
            }
        }
        
        // Alerta de tendencia creciente
        $ultimosTres = array_slice($gastosHistoricos, -3, 3, true);
        $ultimosTresValidos = array_filter($ultimosTres);
        
        if (count($ultimosTresValidos) >= 3) {
            $valores = array_values($ultimosTresValidos);
            if ($valores[2] > $valores[1] && $valores[1] > $valores[0]) {
                $alertas[] = [
                    'tipo' => 'TENDENCIA',
                    'mes' => 'Últimos 3 meses',
                    'mensaje' => 'Tendencia creciente en gastos de honorarios',
                    'gasto' => null,
                    'promedio' => null,
                    'diferencia' => null,
                    'color' => 'info'
                ];
            }
        }
        
        return $alertas;
    }

    /**
     * Calculate standard deviation
     */
    private function calcularDesviacionEstandar($valores, $promedio)
    {
        $valoresValidos = array_filter($valores, function($val) {
            return $val > 0;
        });
        
        if (count($valoresValidos) <= 1) {
            return 0;
        }
        
        $sumaCuadrados = array_reduce($valoresValidos, function($carry, $valor) use ($promedio) {
            return $carry + pow($valor - $promedio, 2);
        }, 0);
        
        return sqrt($sumaCuadrados / count($valoresValidos));
    }
}