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

            // Obtener honorarios (basado en la tabla Clientes para prestadores de servicios)
            $query = DB::table('Clientes as c')
                ->where('c.TipoClie', 3) // Asumimos que TipoClie = 3 son prestadores de servicios/honorarios
                ->leftJoin('t_Clientes_ubigeo as ub', 'c.Codclie', '=', 'ub.CODIGO')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona');

            if ($proveedor) {
                $query->where(function($q) use ($proveedor) {
                    $q->where('c.Razon', 'like', "%$proveedor%")
                      ->orWhere('c.Documento', 'like', "%$proveedor%");
                });
            }

            $honorarios = $query->select([
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

            // Obtener gastos de honorarios del período
            $gastosHonorarios = $this->obtenerGastosHonorarios($fechaInicio, $fechaFin);

            // Obtener saldos pendientes
            $saldosHonorarios = [];
            foreach ($honorarios as $honorario) {
                $saldosHonorarios[$honorario->Codclie] = $this->obtenerSaldosHonorarios($honorario->Codclie);
            }

            // Resumen general
            $resumenGeneral = [
                'total_prestadores' => DB::table('Clientes')->where('TipoClie', 3)->count(),
                'prestadores_activos' => DB::table('Clientes')->where('TipoClie', 3)->where('Activo', 1)->count(),
                'total_honorarios_periodo' => $gastosHonorarios->sum('monto'),
                'prestadores_mas_gastados' => $gastosHonorarios->groupBy('CodClie')
                    ->map->sum('monto')
                    ->sortDesc()
                    ->take(5)
            ];

            return view('contabilidad.auxiliares.honorarios', compact(
                'honorarios', 'gastosHonorarios', 'saldosHonorarios', 'resumenGeneral', 
                'fechaInicio', 'fechaFin', 'proveedor'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el libro de honorarios: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed statement for honorarium provider
     */
    public function estadoCuenta($honorarioId, Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Información del prestador
            $prestador = DB::table('Clientes as c')
                ->leftJoin('Zonas as z', 'c.Zona', '=', 'z.Codzona')
                ->where('c.Codclie', $honorarioId)
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

            // Gastos de honorarios del prestador
            $honorariosPagados = DB::table('Caja as c')
                ->where('c.Razon', $honorarioId)
                ->where('c.Tipo', 2) // Egresos = Pagos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('c.Documento', 'like', 'HON%')
                          ->orWhere('c.Documento', 'like', 'CBO%')
                          ->orWhere('c.Documento', 'like', 'REC%');
                })
                ->select([
                    'c.Numero',
                    'c.Fecha',
                    'c.Documento',
                    'c.Monto',
                    'c.Moneda',
                    'c.Cambio',
                    DB::raw('CASE WHEN c.Moneda = 1 THEN c.Monto ELSE c.Monto * c.Cambio END as total_soles')
                ])
                ->orderBy('c.Fecha', 'desc')
                ->get();

            // Clasificar por tipo de servicio
            $clasificacionServicios = $this->clasificarServicios($honorariosPagados);

            // Resumen mensual
            $resumenMensual = $honorariosPagados->groupBy(function($item) {
                return Carbon::parse($item->Fecha)->format('Y-m');
            })->map(function($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('total_soles'),
                    'promedio' => $grupo->avg('total_soles')
                ];
            });

            // Totales
            $totales = [
                'total_honorarios' => $honorariosPagados->sum('total_soles'),
                'total_documentos' => $honorariosPagados->count(),
                'promedio_honorario' => $honorariosPagados->count() > 0 ? 
                    $honorariosPagados->sum('total_soles') / $honorariosPagados->count() : 0
            ];

            return view('contabilidad.auxiliares.honorarios-estado-cuenta', compact(
                'prestador', 'honorariosPagados', 'clasificacionServicios', 'resumenMensual', 
                'totales', 'fechaInicio', 'fechaFin'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Get honorarium expenses analysis by category
     */
    public function analisisCategorias(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfYear()->format('Y-m-d'));

            // Obtener todos los gastos de honorarios
            $honorariosGenerales = DB::table('Caja as c')
                ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
                ->where('c.Tipo', 2) // Egresos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('c.Documento', 'like', 'HON%')
                          ->orWhere('c.Documento', 'like', 'CBO%')
                          ->orWhere('c.Documento', 'like', 'REC%');
                })
                ->select([
                    'c.Razon as CodClie',
                    'cl.Razon',
                    'c.Documento',
                    'c.Fecha',
                    'c.Monto',
                    'c.Moneda'
                ])
                ->get();

            // Clasificar por categorías de servicios
            $categorias = $this->clasificarCategoriasHonorarios($honorariosGenerales);

            // Calcular totales por categoría
            $totalesCategorias = [];
            foreach ($categorias as $categoria => $servicios) {
                $totalesCategorias[$categoria] = [
                    'cantidad_servicios' => $servicios->count(),
                    'prestadores_diferentes' => $servicios->unique('CodClie')->count(),
                    'total_gastado' => $servicios->sum('Monto'),
                    'promedio_servicio' => $servicios->avg('Monto'),
                    'mayor_gasto' => $servicios->max('Monto')
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

                $honorariosMes = DB::table('Caja as c')
                    ->where('c.Tipo', 2) // Egresos
                    ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                    ->where(function($query) {
                        $query->where('c.Documento', 'like', 'HON%')
                              ->orWhere('c.Documento', 'like', 'CBO%')
                              ->orWhere('c.Documento', 'like', 'REC%');
                    })
                    ->select([
                        DB::raw('COUNT(*) as cantidad'),
                        DB::raw('SUM(c.Monto) as total'),
                        DB::raw('AVG(c.Monto) as promedio')
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
            $topPrestadores = DB::table('Caja as c')
                ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
                ->where('c.Tipo', 2) // Egresos
                ->whereYear('c.Fecha', $anio)
                ->where(function($query) {
                    $query->where('c.Documento', 'like', 'HON%')
                          ->orWhere('c.Documento', 'like', 'CBO%')
                          ->orWhere('c.Documento', 'like', 'REC%');
                })
                ->select([
                    'c.Razon as CodClie',
                    'cl.Razon',
                    DB::raw('SUM(c.Monto) as total'),
                    DB::raw('COUNT(*) as cantidad')
                ])
                ->groupBy('c.Razon', 'cl.Razon')
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
            $serviciosFarmaceuticos = DB::table('Caja as c')
                ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
                ->where('c.Tipo', 2) // Egresos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('cl.Razon', 'like', '%Q.F.%')
                          ->orWhere('cl.Razon', 'like', '%QUIMICO%')
                          ->orWhere('cl.Razon', 'like', '%FARMACEUT%')
                          ->orWhere('cl.Razon', 'like', '%REGENTE%')
                          ->orWhere('cl.Razon', 'like', '%ASESOR%');
                })
                ->select([
                    'c.Razon as CodClie',
                    'cl.Razon',
                    'c.Documento',
                    'c.Fecha',
                    'c.Monto'
                ])
                ->orderBy('c.Monto', 'desc')
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
                    'total' => $servicios->sum('Monto'),
                    'prestadores' => $servicios->unique('CodClie')->count()
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

            // Análisis de retenciones de honorarios
            $honorariosConRetencion = DB::table('Caja as c')
                ->where('c.Tipo', 2) // Egresos
                ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                ->where(function($query) {
                    $query->where('c.Documento', 'like', 'HON%')
                          ->orWhere('c.Documento', 'like', 'CBO%');
                })
                ->select([
                    'c.Documento',
                    'c.Fecha',
                    'c.Monto',
                    DB::raw('CASE 
                        WHEN c.Monto <= 1500 THEN c.Monto * 0.08  -- 8% hasta S/. 1,500
                        ELSE 1500 * 0.08 + (c.Monto - 1500) * 0.10  -- 10% sobre el exceso
                    END as retencion_estimada')
                ])
                ->get();

            // Calcular totales
            $totalHonorarios = $honorariosConRetencion->sum('Monto');
            $totalRetenciones = $honorariosConRetencion->sum('retencion_estimada');
            $netoPagar = $totalHonorarios - $totalRetenciones;

            // Resumen por rangos
            $resumenRangos = [
                'hasta_1500' => $honorariosConRetencion->where('Monto', '<=', 1500)->sum('Monto'),
                'mayor_1500' => $honorariosConRetencion->where('Monto', '>', 1500)->sum('Monto')
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

                $gastoMes = DB::table('Caja as c')
                    ->where('c.Tipo', 2) // Egresos
                    ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
                    ->where(function($query) {
                        $query->where('c.Documento', 'like', 'HON%')
                              ->orWhere('c.Documento', 'like', 'CBO%')
                              ->orWhere('c.Documento', 'like', 'REC%');
                    })
                    ->sum('c.Monto') ?? 0;

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
                'proyeccionesCategoria', 'alertasPresupuesto', 'anio'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en proyección: ' . $e->getMessage());
        }
    }

    /**
     * Get honorarium expenses by provider
     */
    private function obtenerGastosHonorarios($fechaInicio, $fechaFin)
    {
        return DB::table('Caja as c')
            ->leftJoin('Clientes as cl', 'c.Razon', '=', 'cl.Codclie')
            ->where('c.Tipo', 2) // Egresos
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            ->where(function($query) {
                $query->where('c.Documento', 'like', 'HON%')
                      ->orWhere('c.Documento', 'like', 'CBO%')
                      ->orWhere('c.Documento', 'like', 'REC%');
            })
            ->select([
                'c.Razon as CodClie',
                'cl.Razon',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(c.Monto) as monto'),
                DB::raw('AVG(c.Monto) as promedio')
            ])
            ->groupBy('c.Razon', 'cl.Razon')
            ->orderBy('monto', 'desc')
            ->get();
    }

    /**
     * Get balances for honorarium provider
     */
    private function obtenerSaldosHonorarios($honorarioId)
    {
        // Para honorarios, no typically hay saldos pendientes como cuentas por cobrar
        // Pero podríamos rastrear si hay servicios prestados no facturados
        return (object)[
            'total_facturado' => 0,
            'saldo_pendiente' => 0,
            'total_pagado' => 0
        ];
    }

    /**
     * Classify services by type
     */
    private function clasificarServicios($honorarios)
    {
        $clasificacion = [
            'CONSULTORIA' => [],
            'CAPACITACION' => [],
            'ASESORIA' => [],
            'SERVICIOS_PROFESIONALES' => [],
            'OTROS' => []
        ];

        foreach ($honorarios as $honorario) {
            $documento = strtoupper($honorario->Documento);
            
            if (strpos($documento, 'HON') !== false) {
                $clasificacion['SERVICIOS_PROFESIONALES'][] = $honorario;
            } elseif (strpos($documento, 'CBO') !== false) {
                $clasificacion['CONSULTORIA'][] = $honorario;
            } elseif (strpos($documento, 'REC') !== false) {
                $clasificacion['ASESORIA'][] = $honorario;
            } else {
                $clasificacion['OTROS'][] = $honorario;
            }
        }

        return $clasificacion;
    }

    /**
     * Classify honorarium categories
     */
    private function clasificarCategoriasHonorarios($honorarios)
    {
        $categorias = [
            'SERVICIOS_MEDICOS' => [],
            'CONSULTORIA_EMPRESARIAL' => [],
            'CAPACITACION' => [],
            'SERVICIOS_LEGALES' => [],
            'SERVICIOS_CONTABLES' => [],
            'OTROS' => []
        ];

        foreach ($honorarios->groupBy('CodClie') as $proveedorId => $servicios) {
            $proveedor = $servicios->first();
            $nombre = strtoupper($proveedor->Razon);
            
            if (strpos($nombre, 'DR.') !== false || strpos($nombre, 'DRA.') !== false || 
                strpos($nombre, 'MEDICO') !== false) {
                $categorias['SERVICIOS_MEDICOS'][$proveedorId] = $servicios;
            } elseif (strpos($nombre, 'CONSULTOR') !== false || strpos($nombre, 'CONSULTORA') !== false) {
                $categorias['CONSULTORIA_EMPRESARIAL'][$proveedorId] = $servicios;
            } elseif (strpos($nombre, 'CAPACIT') !== false || strpos($nombre, 'TRAINING') !== false) {
                $categorias['CAPACITACION'][$proveedorId] = $servicios;
            } elseif (strpos($nombre, 'ABOGADO') !== false || strpos($nombre, 'ESTUDIO') !== false) {
                $categorias['SERVICIOS_LEGALES'][$proveedorId] = $servicios;
            } elseif (strpos($nombre, 'CONTADOR') !== false || strpos($nombre, 'CONTABLE') !== false) {
                $categorias['SERVICIOS_CONTABLES'][$proveedorId] = $servicios;
            } else {
                $categorias['OTROS'][$proveedorId] = $servicios;
            }
        }

        return $categorias;
    }

    /**
     * Analyze category growth
     */
    private function analizarCrecimientoCategorias($categorias, $fechaInicio, $fechaFin)
    {
        $crecimiento = [];
        
        foreach ($categorias as $categoria => $servicios) {
            $totalActual = $servicios->sum('Monto');
            
            // Comparar con período anterior (simulado)
            $totalAnterior = $totalActual * 0.85; // Asumir 15% menos en período anterior
            
            $crecimiento[$categoria] = [
                'actual' => $totalActual,
                'anterior' => $totalAnterior,
                'variacion' => $totalAnterior > 0 ? (($totalActual - $totalAnterior) / $totalAnterior) * 100 : 0
            ];
        }
        
        return $crecimiento;
    }

    /**
     * Calculate honorarium trends
     */
    private function calcularTendenciasHonorarios($resumenMensual, $anio)
    {
        $totales = array_column($resumenMensual, 'total');
        
        return [
            'promedio_mensual' => array_sum($totales) / count($totales),
            'mes_mayor_gasto' => array_keys($totales, max($totales))[0] ?? null,
            'mes_menor_gasto' => array_keys($totales, min($totales))[0] ?? null,
            'tendencia_anual' => $this->calcularTendencia($totales)
        ];
    }

    /**
     * Calculate trend
     */
    private function calcularTendencia($valores)
    {
        if (count($valores) < 2) return 0;
        
        $primero = $valores[0];
        $ultimo = $valores[count($valores) - 1];
        
        return $primero > 0 ? (($ultimo - $primero) / $primero) * 100 : 0;
    }

    /**
     * Project by category
     */
    private function proyectarPorCategoria($gastosHistoricos)
    {
        $promedio = array_sum($gastosHistoricos) / count($gastosHistoricos);
        
        return [
            'SERVICIOS_MEDICOS' => $promedio * 0.4,
            'CONSULTORIA_EMPRESARIAL' => $promedio * 0.25,
            'CAPACITACION' => $promedio * 0.15,
            'SERVICIOS_LEGALES' => $promedio * 0.10,
            'SERVICIOS_CONTABLES' => $promedio * 0.05,
            'OTROS' => $promedio * 0.05
        ];
    }

    /**
     * Generate budget alerts
     */
    private function generarAlertasPresupuesto($gastosHistoricos, $anio)
    {
        $alertas = [];
        $promedio = array_sum($gastosHistoricos) / count($gastosHistoricos);
        
        foreach ($gastosHistoricos as $mes => $gasto) {
            if ($gasto > $promedio * 1.5) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'mes' => $mes,
                    'mensaje' => "Gasto de honorarios elevada en mes $mes"
                ];
            }
        }
        
        return $alertas;
    }
}