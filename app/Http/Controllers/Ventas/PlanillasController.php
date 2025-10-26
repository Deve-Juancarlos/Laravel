<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class // tabla no existe - PlanillasController extends Controller
{
    /**
     * Constructor con middleware de autenticación y autorización
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('rol:contador|administrador');
    }

    /**
     * Dashboard principal de planillas
     */
    public function index(Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);
        
        $planillas = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('Periodo', $año)
            ->whereMonth('Periodo', $mes)
            ->select([
                '// tabla no existe - Planillas.*',
                'accesoweb.Nombre as Empleado',
                'accesoweb.Dni',
                'accesoweb.Cargo'
            ])
            ->orderBy('accesoweb.Nombre')
            ->paginate(20);

        // Resumen del período
        $resumen = $this->calcularResumenPeriodo($año, $mes);
        
        // Estadísticas anuales
        $estadisticasAnuales = $this->calcularEstadisticasAnuales($año);
        
        // Empleados activos
        $empleadosActivos = $this->obtenerEmpleadosActivos();

        return response()->json([
            'planillas' => $planillas,
            'resumen' => $resumen,
            'estadisticas' => $estadisticasAnuales,
            'empleados_activos' => $empleadosActivos,
            'periodo_actual' => [
                'año' => $año,
                'mes' => $mes,
                'nombre_mes' => Carbon::create($año, $mes)->format('F')
            ]
        ]);
    }

    /**
     * Crear nueva planilla mensual
     */
    public function crearPlanilla(Request $request)
    {
        $request->validate([
            'año' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'mes' => 'required|integer|min:1|max:12',
            'fecha_corte' => 'required|date',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $periodo = Carbon::create($request->año, $request->mes)->endOfMonth();
            
            // Verificar si ya existe planilla para el período
            $existePlanilla = DB::table('// tabla no existe - Planillas')
                ->whereYear('Periodo', $request->año)
                ->whereMonth('Periodo', $request->mes)
                ->exists();

            if ($existePlanilla) {
                return response()->json([
                    'error' => 'Ya existe una planilla para el período seleccionado'
                ], 400);
            }

            // Obtener empleados activos
            $empleados = DB::table('accesoweb')
                ->where('Estado', 'ACTIVO')
                ->where('Rol', '!=', 'ADMIN')
                ->get();

            $totalRegistros = 0;
            $totalBruto = 0;

            foreach ($empleados as $empleado) {
                // Calcular planilla del empleado
                $planillaEmpleado = $this->calcularPlanillaEmpleado($empleado, $periodo, $request->fecha_corte);
                
                if ($planillaEmpleado['total_ingresos'] > 0) {
                    DB::table('// tabla no existe - Planillas')->insert([
                        'CodEmp' => $empleado->Usuario,
                        'Periodo' => $periodo,
                        'Sueldo_Basico' => $planillaEmpleado['sueldo_basico'],
                        'Horas_Extra' => $planillaEmpleado['horas_extra'],
                        'Bonificaciones' => $planillaEmpleado['bonificaciones'],
                        'Otros_Ingresos' => $planillaEmpleado['otros_ingresos'],
                        'Total_Ingresos' => $planillaEmpleado['total_ingresos'],
                        'AFP' => $planillaEmpleado['afp'],
                        'ONP' => $planillaEmpleado['onp'],
                        'Seguro_Salud' => $planillaEmpleado['seguro_salud'],
                        'Impuesto_Renta' => $planillaEmpleado['impuesto_renta'],
                        'Otros_Descuentos' => $planillaEmpleado['otros_descuentos'],
                        'Total_Descuentos' => $planillaEmpleado['total_descuentos'],
                        'Neto_Pagar' => $planillaEmpleado['neto_pagar'],
                        'Fecha_Corte' => $request->fecha_corte,
                        'Observaciones' => $request->observaciones,
                        'Estado' => 'CALCULADO',
                        'Usuario' => auth()->user()->usuario ?? 'admin',
                        'created_at' => now()
                    ]);

                    $totalRegistros++;
                    $totalBruto += $planillaEmpleado['neto_pagar'];
                }
            }

            // Crear resumen de planilla
            $resumenId = DB::table('// tabla no existe - // tabla no existe - Planillas_Resumen')->insertGetId([
                'Periodo' => $periodo,
                'Año' => $request->año,
                'Mes' => $request->mes,
                'Total_Empleados' => $totalRegistros,
                'Total_Bruto' => $totalBruto,
                'Total_Descuentos' => 0, // Se calculará
                'Neto_Pagar' => $totalBruto,
                'Estado' => 'CERRADO',
                'created_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Planilla creada exitosamente',
                'resumen_id' => $resumenId,
                'total_empleados' => $totalRegistros,
                'total_bruto' => $totalBruto,
                'periodo' => $periodo->format('Y-m')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear la planilla'], 500);
        }
    }

    /**
     * Mostrar detalles de un empleado en planilla
     */
    public function show($codEmp, Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);

        $planilla = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->where('// tabla no existe - Planillas.CodEmp', $codEmp)
            ->whereYear('// tabla no existe - Planillas.Periodo', $año)
            ->whereMonth('// tabla no existe - Planillas.Periodo', $mes)
            ->select([
                '// tabla no existe - Planillas.*',
                'accesoweb.Nombre',
                'accesoweb.Dni',
                'accesoweb.Cargo',
                'accesoweb.Email',
                'accesoweb.Telefono'
            ])
            ->first();

        if (!$planilla) {
            return response()->json(['error' => 'Planilla no encontrada'], 404);
        }

        // Historial de planillas del empleado
        $historial = DB::table('// tabla no existe - Planillas')
            ->where('CodEmp', $codEmp)
            ->select([
                'Periodo',
                'Total_Ingresos',
                'Total_Descuentos',
                'Neto_Pagar',
                'Estado'
            ])
            ->orderBy('Periodo', 'desc')
            ->limit(12)
            ->get();

        // Detalles adicionales del empleado
        $empleado = DB::table('accesoweb')->where('Usuario', $codEmp)->first();
        $afiliacion = $this->obtenerAfiliaciones($codEmp);

        return response()->json([
            'planilla' => $planilla,
            'empleado' => $empleado,
            'afiliaciones' => $afiliacion,
            'historial' => $historial
        ]);
    }

    /**
     * Actualizar planilla de empleado
     */
    public function update(Request $request, $codEmp)
    {
        $request->validate([
            'año' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
            'horas_extra' => 'nullable|numeric|min:0',
            'bonificaciones' => 'nullable|numeric|min:0',
            'otros_ingresos' => 'nullable|numeric|min:0',
            'otros_descuentos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $periodo = Carbon::create($request->año, $request->mes)->endOfMonth();
            
            $planilla = DB::table('// tabla no existe - Planillas')
                ->where('CodEmp', $codEmp)
                ->where('Periodo', $periodo)
                ->first();

            if (!$planilla) {
                return response()->json(['error' => 'Planilla no encontrada'], 404);
            }

            // Recalcular totales
            $nuevosTotales = $this->recalcularTotales($request, $planilla);

            // Actualizar planilla
            DB::table('// tabla no existe - Planillas')
                ->where('CodEmp', $codEmp)
                ->where('Periodo', $periodo)
                ->update($nuevosTotales + [
                    'updated_at' => now()
                ]);

            // Actualizar resumen del período
            $this->actualizarResumenPeriodo($request->año, $request->mes);

            DB::commit();

            return response()->json([
                'mensaje' => 'Planilla actualizada exitosamente',
                'nuevos_totales' => $nuevosTotales
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la planilla'], 500);
        }
    }

    /**
     * Aprobar planilla del período
     */
    public function aprobarPlanilla(Request $request)
    {
        $request->validate([
            'año' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12'
        ]);

        try {
            $periodo = Carbon::create($request->año, $request->mes)->endOfMonth();

            // Cambiar estado de planillas individuales
            DB::table('// tabla no existe - Planillas')
                ->whereYear('Periodo', $request->año)
                ->whereMonth('Periodo', $request->mes)
                ->update([
                    'Estado' => 'APROBADO',
                    'Fecha_Aprobacion' => now(),
                    'Usuario_Aprobacion' => auth()->user()->usuario ?? 'admin',
                ]);

            // Cambiar estado del resumen
            DB::table('// tabla no existe - // tabla no existe - Planillas_Resumen')
                ->where('Año', $request->año)
                ->where('Mes', $request->mes)
                ->update([
                    'Estado' => 'APROBADO',
                    'Fecha_Aprobacion' => now()
                ]);

            // Crear asiento contable
            $this->crearAsientoPlanilla($request->año, $request->mes);

            return response()->json([
                'mensaje' => 'Planilla aprobada exitosamente',
                'periodo' => $periodo->format('Y-m')
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al aprobar la planilla'], 500);
        }
    }

    /**
     * Generar boleta de pago
     */
    public function generarBoleta($codEmp, Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);

        $planilla = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->where('// tabla no existe - Planillas.CodEmp', $codEmp)
            ->whereYear('// tabla no existe - Planillas.Periodo', $año)
            ->whereMonth('// tabla no existe - Planillas.Periodo', $mes)
            ->select([
                '// tabla no existe - Planillas.*',
                'accesoweb.Nombre as Empleado',
                'accesoweb.Dni',
                'accesoweb.Cargo',
                'accesoweb.Direccion'
            ])
            ->first();

        if (!$planilla) {
            return response()->json(['error' => 'Planilla no encontrada'], 404);
        }

        // Calcular datos adicionales para boleta
        $datosBoleta = [
            'periodo' => $planilla->Periodo,
            'dias_trabajados' => $this->calcularDiasTrabajados($codEmp, $año, $mes),
            'horas_trabajadas' => $this->calcularHorasTrabajadas($codEmp, $año, $mes),
            'vacaciones' => $this->obtener// tabla no existe - Vacaciones($codEmp, $año, $mes),
            'licencias' => $this->obtener// tabla no existe - Licencias($codEmp, $año, $mes)
        ];

        return response()->json([
            'boleta' => $planilla,
            'datos_adicionales' => $datosBoleta,
            'empresa' => $this->obtenerDatosEmpresa()
        ]);
    }

    /**
     * Reporte de planillas por período
     */
    public function reportePeriodo(Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);

        $planillas = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('// tabla no existe - Planillas.Periodo', $año)
            ->whereMonth('// tabla no existe - Planillas.Periodo', $mes)
            ->select([
                '// tabla no existe - Planillas.*',
                'accesoweb.Nombre',
                'accesoweb.Dni',
                'accesoweb.Cargo',
                'accesoweb.Centro_Costo'
            ])
            ->orderBy('accesoweb.Nombre')
            ->get();

        // Calcular totales
        $totales = [
            'total_empleados' => $planillas->count(),
            'total_ingresos' => $planillas->sum('Total_Ingresos'),
            'total_descuentos' => $planillas->sum('Total_Descuentos'),
            'total_neto' => $planillas->sum('Neto_Pagar'),
            'total_afp' => $planillas->sum('AFP'),
            'total_seguro' => $planillas->sum('Seguro_Salud'),
            'total_impuestos' => $planillas->sum('Impuesto_Renta')
        ];

        return response()->json([
            'periodo' => [
                'año' => $año,
                'mes' => $mes,
                'nombre_mes' => Carbon::create($año, $mes)->format('F')
            ],
            'planillas' => $planillas,
            'totales' => $totales
        ]);
    }

    /**
     * Análisis de costos laborales
     */
    public function analisisCostos(Request $request)
    {
        $año = $request->get('año', now()->year);
        $tipo = $request->get('tipo', 'mensual'); // mensual, trimestral, anual

        $fechaInicio = match($tipo) {
            'mensual' => Carbon::create($año, now()->month)->startOfMonth(),
            'trimestral' => Carbon::create($año, (int)((now()->month - 1) / 3) * 3 + 1)->startOfQuarter(),
            'anual' => Carbon::create($año, 1)->startOfYear(),
            default => Carbon::create($año, now()->month)->startOfMonth()
        };

        // Costos por centro de costo
        $costosCentro = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->where('// tabla no existe - Planillas.Periodo', '>=', $fechaInicio)
            ->select([
                'accesoweb.Centro_Costo',
                DB::raw('COUNT(DISTINCT // tabla no existe - Planillas.CodEmp) as total_empleados'),
                DB::raw('SUM(// tabla no existe - Planillas.Total_Ingresos) as total_ingresos'),
                DB::raw('SUM(// tabla no existe - Planillas.Total_Descuentos) as total_descuentos'),
                DB::raw('SUM(// tabla no existe - Planillas.Neto_Pagar) as total_neto')
            ])
            ->groupBy('accesoweb.Centro_Costo')
            ->get();

        // Evolución mensual de costos
        $evolucionMensual = DB::table('// tabla no existe - Planillas')
            ->whereYear('Periodo', $año)
            ->select([
                DB::raw('MONTH(Periodo) as mes'),
                DB::raw('SUM(Total_Ingresos) as total_ingresos'),
                DB::raw('SUM(Total_Descuentos) as total_descuentos'),
                DB::raw('SUM(Neto_Pagar) as total_neto'),
                DB::raw('COUNT(DISTINCT CodEmp) as empleados_activos')
            ])
            ->groupBy(DB::raw('MONTH(Periodo)'))
            ->orderBy('mes')
            ->get();

        // Top empleados por costo
        $topEmpleados = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('// tabla no existe - Planillas.Periodo', $año)
            ->select([
                'accesoweb.Nombre',
                'accesoweb.Cargo',
                'accesoweb.Centro_Costo',
                DB::raw('SUM(// tabla no existe - Planillas.Neto_Pagar) as costo_total_año')
            ])
            ->groupBy('accesoweb.Usuario', 'accesoweb.Nombre', 'accesoweb.Cargo', 'accesoweb.Centro_Costo')
            ->orderBy('costo_total_año', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'costos_centro' => $costosCentro,
            'evolucion_mensual' => $evolucionMensual,
            'top_empleados' => $topEmpleados,
            'periodo' => [
                'tipo' => $tipo,
                'año' => $año,
                'inicio' => $fechaInicio->toDateString(),
                'fin' => now()->toDateString()
            ]
        ]);
    }

    /**
     * // tabla no existe - Vacaciones y licencias
     */
    public function vacaciones// tabla no existe - Licencias(Request $request)
    {
        $codEmp = $request->get('empleado');
        $año = $request->get('año', now()->year);

        if ($codEmp) {
            // // tabla no existe - Vacaciones y licencias de un empleado
            $vacaciones = DB::table('// tabla no existe - Vacaciones')
                ->where('CodEmp', $codEmp)
                ->whereYear('Periodo', $año)
                ->orderBy('Periodo', 'desc')
                ->get();

            $licencias = DB::table('// tabla no existe - Licencias')
                ->where('CodEmp', $codEmp)
                ->whereYear('Periodo', $año)
                ->orderBy('Periodo', 'desc')
                ->get();

            return response()->json([
                'vacaciones' => $vacaciones,
                'licencias' => $licencias
            ]);
        }

        // Resumen general de vacaciones y licencias
        $resumen// tabla no existe - Vacaciones = DB::table('// tabla no existe - Vacaciones')
            ->leftJoin('accesoweb', '// tabla no existe - Vacaciones.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('// tabla no existe - Vacaciones.Periodo', $año)
            ->select([
                'accesoweb.Nombre',
                '// tabla no existe - Vacaciones.*',
                DB::raw('DATEDIFF(Fin, Inicio) + 1 as dias_solicitados')
            ])
            ->get();

        $resumen// tabla no existe - Licencias = DB::table('// tabla no existe - Licencias')
            ->leftJoin('accesoweb', '// tabla no existe - Licencias.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('// tabla no existe - Licencias.Periodo', $año)
            ->select([
                'accesoweb.Nombre',
                '// tabla no existe - Licencias.*',
                DB::raw('DATEDIFF(Fin, Inicio) + 1 as dias_solicitados')
            ])
            ->get();

        return response()->json([
            'vacaciones' => $resumen// tabla no existe - Vacaciones,
            'licencias' => $resumen// tabla no existe - Licencias,
            'año' => $año
        ]);
    }

    // ===== MÉTODOS PRIVADOS DE SOPORTE =====

    /**
     * Calcular planilla de un empleado
     */
    private function calcularPlanillaEmpleado($empleado, $periodo, $fechaCorte)
    {
        // Sueldo básico (calculado proporcionalmente)
        $sueldoBasico = $this->calcularSueldoBasico($empleado, $periodo);
        
        // Horas extras del mes
        $horasExtra = $this->calcularHorasExtras($empleado->Usuario, $periodo);
        
        // Bonificaciones
        $bonificaciones = $this->calcularBonificaciones($empleado, $periodo);
        
        // Otros ingresos
        $otrosIngresos = $this->calcularOtrosIngresos($empleado, $periodo);
        
        // Total ingresos
        $totalIngresos = $sueldoBasico + $horasExtra + $bonificaciones + $otrosIngresos;
        
        // Descuentos
        $afp = $totalIngresos * 0.11; // 11% AFP
        $onp = $totalIngresos * 0.13; // 13% ONP (ejemplo)
        $seguroSalud = $totalIngresos * 0.09; // 9% seguro
        $impuestoRenta = $this->calcularImpuestoRenta($totalIngresos);
        $otrosDescuentos = 0; // Calculados por separado
        
        $totalDescuentos = $afp + $onp + $seguroSalud + $impuestoRenta + $otrosDescuentos;
        $netoPagar = $totalIngresos - $totalDescuentos;

        return [
            'sueldo_basico' => $sueldoBasico,
            'horas_extra' => $horasExtra,
            'bonificaciones' => $bonificaciones,
            'otros_ingresos' => $otrosIngresos,
            'total_ingresos' => $totalIngresos,
            'afp' => $afp,
            'onp' => $onp,
            'seguro_salud' => $seguroSalud,
            'impuesto_renta' => $impuestoRenta,
            'otros_descuentos' => $otrosDescuentos,
            'total_descuentos' => $totalDescuentos,
            'neto_pagar' => $netoPagar
        ];
    }

    /**
     * Calcular sueldo básico proporcional
     */
    private function calcularSueldoBasico($empleado, $periodo)
    {
        // Lógica para calcular sueldo básico proporcional según días trabajados
        $sueldoMensual = $empleado->Sueldo ?? 1500; // Sueldo por defecto
        
        $diasMes = $periodo->daysInMonth;
        $diasTrabajados = $this->calcularDiasTrabajados($empleado->Usuario, $periodo->year, $periodo->month);
        
        return ($sueldoMensual / $diasMes) * $diasTrabajados;
    }

    /**
     * Calcular horas extras
     */
    private function calcularHorasExtras($codEmp, $periodo)
    {
        $horasExtras = DB::table('Horas_Extras')
            ->where('CodEmp', $codEmp)
            ->whereYear('Fecha', $periodo->year)
            ->whereMonth('Fecha', $periodo->month)
            ->sum('Total_Horas');

        $valorHora = 15; // Valor por hora extra
        return $horasExtras * $valorHora;
    }

    /**
     * Calcular bonificaciones
     */
    private function calcularBonificaciones($empleado, $periodo)
    {
        $bonificaciones = 0;
        
        // Bonificación por productividad
        $productividad = $this->calcularProductividad($empleado->Usuario, $periodo);
        if ($productividad > 100) {
            $bonificaciones += ($productividad - 100) * 10; // 10 soles por punto sobre 100%
        }
        
        return $bonificaciones;
    }

    /**
     * Calcular otros ingresos
     */
    private function calcularOtrosIngresos($empleado, $periodo)
    {
        // Implementación básica - en producción sería más compleja
        return 0;
    }

    /**
     * Calcular impuesto a la renta (escala progresiva)
     */
    private function calcularImpuestoRenta($ingresos)
    {
        if ($ingresos <= 1025) return 0;
        if ($ingresos <= 1825) return ($ingresos - 1025) * 0.15;
        if ($ingresos <= 2825) return ($ingresos - 1825) * 0.15 + 120;
        if ($ingresos <= 4150) return ($ingresos - 2825) * 0.20 + 270;
        return ($ingresos - 4150) * 0.30 + 535;
    }

    /**
     * Recalcular totales después de actualización
     */
    private function recalcularTotales($request, $planilla)
    {
        $horasExtra = $request->horas_extra ?? 0;
        $bonificaciones = $request->bonificaciones ?? 0;
        $otrosIngresos = $request->otros_ingresos ?? 0;
        $otrosDescuentos = $request->otros_descuentos ?? 0;

        $nuevoTotalIngresos = $planilla->Sueldo_Basico + $horasExtra + $bonificaciones + $otrosIngresos;
        
        $afp = $nuevoTotalIngresos * 0.11;
        $seguroSalud = $nuevoTotalIngresos * 0.09;
        $impuestoRenta = $this->calcularImpuestoRenta($nuevoTotalIngresos);
        
        $nuevoTotalDescuentos = $afp + $seguroSalud + $impuestoRenta + $otrosDescuentos;
        $nuevoNetoPagar = $nuevoTotalIngresos - $nuevoTotalDescuentos;

        return [
            'Horas_Extra' => $horasExtra,
            'Bonificaciones' => $bonificaciones,
            'Otros_Ingresos' => $otrosIngresos,
            'Total_Ingresos' => $nuevoTotalIngresos,
            'Otros_Descuentos' => $otrosDescuentos,
            'Total_Descuentos' => $nuevoTotalDescuentos,
            'Neto_Pagar' => $nuevoNetoPagar,
            'Observaciones' => $request->observaciones
        ];
    }

    /**
     * Calcular resumen del período
     */
    private function calcularResumenPeriodo($año, $mes)
    {
        $periodo = Carbon::create($año, $mes)->endOfMonth();
        
        $planillas = DB::table('// tabla no existe - Planillas')
            ->whereYear('Periodo', $año)
            ->whereMonth('Periodo', $mes)
            ->select([
                DB::raw('COUNT(*) as total_empleados'),
                DB::raw('SUM(Sueldo_Basico) as total_sueldos'),
                DB::raw('SUM(Horas_Extra) as total_horas_extra'),
                DB::raw('SUM(Bonificaciones) as total_bonificaciones'),
                DB::raw('SUM(Total_Ingresos) as total_ingresos'),
                DB::raw('SUM(Total_Descuentos) as total_descuentos'),
                DB::raw('SUM(Neto_Pagar) as total_neto')
            ])
            ->first();

        return [
            'total_empleados' => $planillas->total_empleados,
            'total_sueldos' => number_format($planillas->total_sueldos, 2),
            'total_horas_extra' => number_format($planillas->total_horas_extra, 2),
            'total_bonificaciones' => number_format($planillas->total_bonificaciones, 2),
            'total_ingresos' => number_format($planillas->total_ingresos, 2),
            'total_descuentos' => number_format($planillas->total_descuentos, 2),
            'total_neto' => number_format($planillas->total_neto, 2)
        ];
    }

    /**
     * Calcular estadísticas anuales
     */
    private function calcularEstadisticasAnuales($año)
    {
        $mensual = DB::table('// tabla no existe - Planillas')
            ->whereYear('Periodo', $año)
            ->select([
                DB::raw('MONTH(Periodo) as mes'),
                DB::raw('SUM(Neto_Pagar) as total_mensual'),
                DB::raw('COUNT(DISTINCT CodEmp) as empleados_promedio')
            ])
            ->groupBy(DB::raw('MONTH(Periodo)'))
            ->orderBy('mes')
            ->get();

        return $mensual;
    }

    /**
     * Obtener empleados activos
     */
    private function obtenerEmpleadosActivos()
    {
        return DB::table('accesoweb')
            ->where('Estado', 'ACTIVO')
            ->where('Rol', '!=', 'ADMIN')
            ->select(['Usuario', 'Nombre', 'Cargo', 'Sueldo'])
            ->orderBy('Nombre')
            ->get();
    }

    /**
     * Obtener afiliaciones del empleado
     */
    private function obtenerAfiliaciones($codEmp)
    {
        return [
            'afp' => DB::table('AFP_Afiliacion')->where('CodEmp', $codEmp)->first(),
            'seguro' => DB::table('Seguro_Afiliacion')->where('CodEmp', $codEmp)->first()
        ];
    }

    /**
     * Actualizar resumen del período
     */
    private function actualizarResumenPeriodo($año, $mes)
    {
        $totales = $this->calcularResumenPeriodo($año, $mes);
        
        DB::table('// tabla no existe - // tabla no existe - Planillas_Resumen')
            ->where('Año', $año)
            ->where('Mes', $mes)
            ->update([
                'Total_Empleados' => $totales['total_empleados'],
                'Total_Bruto' => $totales['total_ingresos'],
                'Total_Descuentos' => $totales['total_descuentos'],
                'Neto_Pagar' => $totales['total_neto'],
                'updated_at' => now()
            ]);
    }

    /**
     * Crear asiento contable de planilla
     */
    private function crearAsientoPlanilla($año, $mes)
    {
        $periodo = Carbon::create($año, $mes)->endOfMonth();
        $totales = $this->calcularResumenPeriodo($año, $mes);

        DB::table('asientos_diario')->insert([
            [
                'fecha' => $periodo,
                'glosa' => "PLANILLA {$año}-{$mes}",
                'cuenta_debe' => '6211', // Gastos de personal
                'cuenta_haber' => '4111', // Remuneraciones por pagar
                'monto' => $totales['total_ingresos'],
                'fecha_creacion' => now()
            ]
        ]);
    }

    /**
     * Obtener datos de la empresa
     */
    private function obtenerDatosEmpresa()
    {
        return [
            'razon_social' => 'Mi Empresa SAC',
            'ruc' => '20123456789',
            'direccion' => 'Av. Principal 123, Lima',
            'telefono' => '01-1234567'
        ];
    }

    /**
     * Calcular días trabajados
     */
    private function calcularDiasTrabajados($codEmp, $año, $mes)
    {
        // Implementación básica
        return Carbon::create($año, $mes)->daysInMonth;
    }

    /**
     * Calcular horas trabajadas
     */
    private function calcularHorasTrabajadas($codEmp, $año, $mes)
    {
        // Implementación básica
        return 160; // 40 horas por semana * 4 semanas
    }

    /**
     * Obtener vacaciones
     */
    private function obtener// tabla no existe - Vacaciones($codEmp, $año, $mes)
    {
        return DB::table('// tabla no existe - Vacaciones')
            ->where('CodEmp', $codEmp)
            ->whereYear('Periodo', $año)
            ->whereMonth('Periodo', $mes)
            ->get();
    }

    /**
     * Obtener licencias
     */
    private function obtener// tabla no existe - Licencias($codEmp, $año, $mes)
    {
        return DB::table('// tabla no existe - Licencias')
            ->where('CodEmp', $codEmp)
            ->whereYear('Periodo', $año)
            ->whereMonth('Periodo', $mes)
            ->get();
    }

    /**
     * Calcular productividad
     */
    private function calcularProductividad($codEmp, $periodo)
    {
        // Implementación básica - en producción sería más compleja
        return 105; // 105% productividad
    }

    /**
     * Exportar planillas
     */
    public function exportar(Request $request)
    {
        $año = $request->get('año', now()->year);
        $mes = $request->get('mes', now()->month);
        $tipo = $request->get('tipo', 'excel');

        $planillas = DB::table('// tabla no existe - Planillas')
            ->leftJoin('accesoweb', '// tabla no existe - Planillas.CodEmp', '=', 'accesoweb.Usuario')
            ->whereYear('// tabla no existe - Planillas.Periodo', $año)
            ->whereMonth('// tabla no existe - Planillas.Periodo', $mes)
            ->select([
                'accesoweb.Nombre',
                'accesoweb.Dni',
                'accesoweb.Cargo',
                '// tabla no existe - Planillas.Sueldo_Basico',
                '// tabla no existe - Planillas.Horas_Extra',
                '// tabla no existe - Planillas.Bonificaciones',
                '// tabla no existe - Planillas.Total_Ingresos',
                '// tabla no existe - Planillas.Total_Descuentos',
                '// tabla no existe - Planillas.Neto_Pagar'
            ])
            ->orderBy('accesoweb.Nombre')
            ->get();

        if ($tipo === 'csv') {
            return $this->exportarCSV($planillas, $año, $mes);
        }

        return response()->json([
            'mensaje' => 'Exportación iniciada',
            'total_registros' => $planillas->count(),
            'año' => $año,
            'mes' => $mes
        ]);
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV($planillas, $año, $mes)
    {
        $filename = "planillas_{$año}_{$mes}_" . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Empleado', 'DNI', 'Cargo', 'Sueldo_Basico', 'Horas_Extra', 
            'Bonificaciones', 'Total_Ingresos', 'Total_Descuentos', 'Neto_Pagar'
        ];

        $csvData = [];
        $csvData[] = implode(',', $headers);

        foreach ($planillas as $planilla) {
            $row = [
                '"' . str_replace('"', '""', $planilla->Nombre) . '"',
                $planilla->Dni,
                '"' . str_replace('"', '""', $planilla->Cargo) . '"',
                number_format($planilla->Sueldo_Basico, 2),
                number_format($planilla->Horas_Extra, 2),
                number_format($planilla->Bonificaciones, 2),
                number_format($planilla->Total_Ingresos, 2),
                number_format($planilla->Total_Descuentos, 2),
                number_format($planilla->Neto_Pagar, 2)
            ];
            $csvData[] = implode(',', $row);
        }

        return response(implode("\n", $csvData))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
