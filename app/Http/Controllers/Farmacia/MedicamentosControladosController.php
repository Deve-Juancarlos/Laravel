<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MedicamentosControladosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'rol:farmaceutico,administrador']);
    }

    /**
     * Display a listing of resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Presentacion',
                'p.Coddigemin',
                'p.TipoControl',
                'p.RegistroSanitario',
                'p.CondicionesEspeciales',
                'l.Descripcion as Laboratorio',
                's.Lote',
                's.Vencimiento',
                's.Cantidad',
                's.Costo',
                'dc.Tipo as TipoIngreso',
                'dc.Fecha as FechaIngreso',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento'),
                DB::raw('(s.Cantidad * s.Costo) as ValorTotal')
            ])
            ->where('p.TipoControl', '!=', '')
            ->whereNotNull('p.TipoControl');

        // Filtros por tipo de control
        if ($request->filled('tipo_control')) {
            $query->where('p.TipoControl', $request->tipo_control);
        }

        if ($request->filled('codigo_digemid')) {
            $query->where('p.Coddigemin', 'like', '%' . $request->codigo_digemid . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('p.Nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('laboratorio')) {
            $query->where('l.CodLab', $request->laboratorio);
        }

        if ($request->filled('categoria_control')) {
            switch ($request->categoria_control) {
                case 'estupefacientes':
                    $query->where('p.Coddigemin', 'like', 'ESTUPEFACIENTE%');
                    break;
                case 'psicotropos':
                    $query->where('p.Coddigemin', 'like', 'PSICOTROPO%');
                    break;
                case 'controlados_especiales':
                    $query->where('p.Coddigemin', 'like', 'CONTROLADO%');
                    break;
                case 'retencion_receta':
                    $query->where('p.TipoControl', 'RECETA_RETENCIDA');
                    break;
            }
        }

        if ($request->filled('stock_bajo')) {
            $query->whereRaw('s.Cantidad <= s.StockMinimo');
        }

        $medicamentos = $query->orderBy('p.Nombre')
            ->paginate(25);

        // Estadísticas de medicamentos controlados
        $estadisticas = $this->calcularEstadisticasMedicamentosControlados();

        // Alertas de medicamentos controlados
        $alertas = $this->generarAlertasMedicamentosControlados();

        // Análisis por categoría
        $analisisCategorias = $this->analizarMedicamentosPorCategoria();

        return compact('medicamentos', 'estadisticas', 'alertas', 'analisisCategorias');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        $tiposControl = [
            'ESTUPEFACIENTE' => 'Estupefacientes',
            'PSICOTROPO' => 'Psicotrópicos',
            'CONTROLADO_ESPECIAL' => 'Controlados Especiales',
            'RECETA_RETENCIDA' => 'Receta Retenida',
            'REQUIERE_AUTORIZACION' => 'Requiere Autorización DIGEMID',
            'RECETA_ESPECIAL' => 'Receta Especial',
            'CONTROLADO' => 'Controlado'
        ];

        $categoriasDigemid = [
            'Lista I' => 'Lista I - Estupefacientes',
            'Lista II' => 'Lista II - Psicotrópicos',
            'Lista III' => 'Lista III - Sustancias Controladas',
            'Lista IV' => 'Lista IV - Medicamentos Controlados'
        ];

        return compact('laboratorios', 'tiposControl', 'categoriasDigemid');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codpro' => 'required|string|max:20|unique:Productos,CodPro',
            'nombre' => 'required|string|max:200',
            'presentacion' => 'required|string|max:100',
            'codlab' => 'required|exists:Laboratorios,CodLab',
            'coddigemin' => 'required|string|max:50',
            'tipo_control' => 'required|in:ESTUPEFACIENTE,PSICOTROPO,CONTROLADO_ESPECIAL,RECETA_RETENCIDA,REQUIERE_AUTORIZACION,RECETA_ESPECIAL,CONTROLADO',
            'registro_sanitario' => 'required|string|max:50',
            'condiciones_especiales' => 'nullable|string|max:500',
            'concentracion' => 'required|string|max:100',
            'forma_farmaceutica' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que el código DIGEMID no esté duplicado
            $codigoExiste = DB::table('Productos')
                ->where('Coddigemin', $request->coddigemin)
                ->exists();

            if ($codigoExiste) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un medicamento con este código DIGEMID'
                ], 400);
            }

            // Crear medicamento controlado
            DB::table('Productos')->insert([
                'CodPro' => $request->codpro,
                'Nombre' => $request->nombre,
                'Presentacion' => $request->presentacion,
                'CodLab' => $request->codlab,
                'Coddigemin' => $request->coddigemin,
                'TipoControl' => $request->tipo_control,
                'RegistroSanitario' => $request->registro_sanitario,
                'CondicionesEspeciales' => $request->condiciones_especiales,
                'Concentracion' => $request->concentracion,
                'FormaFarmaceutica' => $request->forma_farmaceutica,
                'UnidadMedida' => $request->unidad_medida,
                'EsControlado' => true,
                'FechaCreacion' => Carbon::now(),
                'UsuarioCreacion' => Auth::id()
            ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadMedicamento($request->codpro, 'CREACION_MEDICAMENTO_CONTROLADO', 
                "Medicamento controlado creado - Código DIGEMID: {$request->coddigemin}", 0);

            // Generar alerta si es sustancia altamente controlada
            if (in_array($request->tipo_control, ['ESTUPEFACIENTE', 'PSICOTROPO'])) {
                $this->generarAlertaAltoControl($request->codpro, $request->tipo_control);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medicamento controlado registrado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar medicamento controlado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($codpro)
    {
        $medicamento = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->leftJoin('users as uc', 'p.UsuarioCreacion', '=', 'uc.id')
            ->leftJoin('users as um', 'p.UsuarioModificacion', '=', 'um.id')
            ->select([
                'p.*',
                'l.Descripcion as NombreLab',
                'uc.name as UsuarioCreacionNombre',
                'um.name as UsuarioModificacionNombre'
            ])
            ->where('p.CodPro', $codpro)
            ->where('p.EsControlado', true)
            ->first();

        if (!$medicamento) {
            return response()->json(['message' => 'Medicamento controlado no encontrado'], 404);
        }

        // Stock actual por lotes
        $stockActual = DB::table('Saldos as s')
            ->leftJoin('Doccab as dc', 's.CodDoc', '=', 'dc.CodDoc')
            ->where('s.CodPro', $codpro)
            ->where('s.Cantidad', '>', 0)
            ->select([
                's.*',
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                DB::raw('DATEDIFF(day, GETDATE(), s.Vencimiento) as DiasVencimiento')
            ])
            ->orderBy('s.Vencimiento')
            ->get();

        // Movimientos recientes
        $movimientosRecientes = $this->obtenerMovimientosRecientes($codpro);

        // Control de recetas y dispensación
        $controlDispensacion = $this->obtenerControlDispensacion($codpro);

        // Alertas específicas
        $alertasEspecificas = $this->obtenerAlertasEspecificas($codpro);

        return compact('medicamento', 'stockActual', 'movimientosRecientes', 
            'controlDispensacion', 'alertasEspecificas');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codpro)
    {
        $medicamento = DB::table('Productos as p')
            ->leftJoin('Laboratorios as l', 'p.CodLab', '=', 'l.CodLab')
            ->where('p.CodPro', $codpro)
            ->where('p.EsControlado', true)
            ->select('p.*', 'l.Descripcion as NombreLab')
            ->first();

        if (!$medicamento) {
            return response()->json(['message' => 'Medicamento controlado no encontrado'], 404);
        }

        $laboratorios = DB::table('Laboratorios')
            ->orderBy('Descripcion')
            ->get();

        $tiposControl = [
            'ESTUPEFACIENTE' => 'Estupefacientes',
            'PSICOTROPO' => 'Psicotrópicos',
            'CONTROLADO_ESPECIAL' => 'Controlados Especiales',
            'RECETA_RETENCIDA' => 'Receta Retenida',
            'REQUIERE_AUTORIZACION' => 'Requiere Autorización DIGEMID',
            'RECETA_ESPECIAL' => 'Receta Especial',
            'CONTROLADO' => 'Controlado'
        ];

        return compact('medicamento', 'laboratorios', 'tiposControl');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codpro)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'presentacion' => 'required|string|max:100',
            'codlab' => 'required|exists:Laboratorios,CodLab',
            'coddigemin' => 'required|string|max:50',
            'tipo_control' => 'required|in:ESTUPEFACIENTE,PSICOTROPO,CONTROLADO_ESPECIAL,RECETA_RETENCIDA,REQUIERE_AUTORIZACION,RECETA_ESPECIAL,CONTROLADO',
            'registro_sanitario' => 'required|string|max:50',
            'condiciones_especiales' => 'nullable|string|max:500',
            'concentracion' => 'required|string|max:100',
            'forma_farmaceutica' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $medicamento = DB::table('Productos')
                ->where('CodPro', $codpro)
                ->where('EsControlado', true)
                ->first();

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento controlado no encontrado'], 404);
            }

            // Verificar que el código DIGEMID no esté duplicado (excluyendo el actual)
            $codigoExiste = DB::table('Productos')
                ->where('Coddigemin', $request->coddigemin)
                ->where('CodPro', '!=', $codpro)
                ->exists();

            if ($codigoExiste) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro medicamento con este código DIGEMID'
                ], 400);
            }

            // Actualizar medicamento
            DB::table('Productos')
                ->where('CodPro', $codpro)
                ->update([
                    'Nombre' => $request->nombre,
                    'Presentacion' => $request->presentacion,
                    'CodLab' => $request->codlab,
                    'Coddigemin' => $request->coddigemin,
                    'TipoControl' => $request->tipo_control,
                    'RegistroSanitario' => $request->registro_sanitario,
                    'CondicionesEspeciales' => $request->condiciones_especiales,
                    'Concentracion' => $request->concentracion,
                    'FormaFarmaceutica' => $request->forma_farmaceutica,
                    'UnidadMedida' => $request->unidad_medida,
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id()
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadMedicamento($codpro, 'MODIFICACION_MEDICAMENTO_CONTROLADO', 
                "Medicamento controlado modificado - Código DIGEMID: {$request->coddigemin}", 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medicamento controlado actualizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar medicamento controlado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codpro)
    {
        try {
            DB::beginTransaction();

            $medicamento = DB::table('Productos')
                ->where('CodPro', $codpro)
                ->where('EsControlado', true)
                ->first();

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento controlado no encontrado'], 404);
            }

            // Verificar si tiene stock
            $tieneStock = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Cantidad', '>', 0)
                ->exists();

            if ($tieneStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un medicamento que tiene stock'
                ], 400);
            }

            // Verificar si tiene movimientos de venta
            $tieneMovimientos = DB::table('Doccab as dc')
                ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
                ->where('dd.CodPro', $codpro)
                ->exists();

            if ($tieneMovimientos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un medicamento que tiene movimientos de venta'
                ], 400);
            }

            // Marcar como no controlado en lugar de eliminar para mantener trazabilidad
            DB::table('Productos')
                ->where('CodPro', $codpro)
                ->update([
                    'EsControlado' => false,
                    'TipoControl' => '',
                    'FechaModificacion' => Carbon::now(),
                    'UsuarioModificacion' => Auth::id(),
                    'Observaciones' => 'Medicamento desactivado del control especial'
                ]);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadMedicamento($codpro, 'DESACTIVACION_CONTROL', 
                'Medicamento desactivado del control especial', 0);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medicamento desactivado del control correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar medicamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Control de dispensación de medicamentos controlados
     */
    public function controlDispensacion(Request $request, $codpro)
    {
        $request->validate([
            'dni_paciente' => 'required|string|max:15',
            'nombre_paciente' => 'required|string|max:100',
            'medico' => 'required|string|max:100',
            'cmp_medico' => 'required|string|max:20',
            'cantidad_dispensada' => 'required|numeric|min:0.01',
            'lote_utilizado' => 'required|string|max:50',
            'receta_numero' => 'required|string|max:50',
            'fecha_receta' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $medicamento = DB::table('Productos')
                ->where('CodPro', $codpro)
                ->where('EsControlado', true)
                ->first();

            if (!$medicamento) {
                return response()->json(['message' => 'Medicamento controlado no encontrado'], 404);
            }

            // Verificar stock del lote
            $lote = DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $request->lote_utilizado)
                ->where('Cantidad', '>=', $request->cantidad_dispensada)
                ->first();

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente en el lote especificado'
                ], 400);
            }

            // Registrar dispensación
            $codDispensacion = $this->generarCodDispensacion();
            
            DB::table('ControlDispensacionMedicamentos')->insert([
                'CodDispensacion' => $codDispensacion,
                'CodPro' => $codpro,
                'DNIPaciente' => $request->dni_paciente,
                'NombrePaciente' => $request->nombre_paciente,
                'Medico' => $request->medico,
                'CMPCMedico' => $request->cmp_medico,
                'CantidadDispensada' => $request->cantidad_dispensada,
                'LoteUtilizado' => $request->lote_utilizado,
                'RecetaNumero' => $request->receta_numero,
                'FechaReceta' => $request->fecha_receta,
                'Observaciones' => $request->observaciones,
                'FechaDispensacion' => Carbon::now(),
                'Usuario' => Auth::id(),
                'Estado' => 'dispensada'
            ]);

            // Actualizar stock
            DB::table('Saldos')
                ->where('CodPro', $codpro)
                ->where('Lote', $request->lote_utilizado)
                ->decrement('Cantidad', $request->cantidad_dispensada);

            // Registrar en trazabilidad
            $this->registrarTrazabilidadMedicamento($codpro, 'DISPENSACION_CONTROLADA', 
                "Dispensación controlada - Paciente: {$request->nombre_paciente} - Cantidad: {$request->cantidad_dispensada}", 
                -$request->cantidad_dispensada);

            // Generar alerta si es alto control
            if (in_array($medicamento->TipoControl, ['ESTUPEFACIENTE', 'PSICOTROPO'])) {
                $this->generarAlertaDispensacion($codpro, $codDispensacion, $request->nombre_paciente);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dispensación registrada correctamente',
                'data' => ['cod_dispensacion' => $codDispensacion]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar dispensación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte mensual para DIGEMID
     */
    public function reporteDigemid(Request $request)
    {
        $request->validate([
            'año' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'mes' => 'required|integer|min:1|max:12',
        ]);

        $inicioMes = Carbon::create($request->año, $request->mes, 1)->startOfMonth();
        $finMes = Carbon::create($request->año, $request->mes, 1)->endOfMonth();

        // Movimientos de entrada
        $entradas = DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
            ->join('Productos as p', 'dd.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->whereBetween('dc.Fecha', [$inicioMes, $finMes])
            ->whereIn('dc.Tipo', ['COM', 'NOT'])
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Coddigemin',
                'p.TipoControl',
                'dd.Cantidad',
                'dd.Lote',
                'dd.Costo',
                'dc.Tipo as TipoDoc',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha'
            ])
            ->get();

        // Movimientos de salida (dispensaciones)
        $salidas = DB::table('ControlDispensacionMedicamentos as cdm')
            ->join('Productos as p', 'cdm.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->whereBetween('cdm.FechaDispensacion', [$inicioMes, $finMes])
            ->select([
                'p.CodPro',
                'p.Nombre',
                'p.Coddigemin',
                'p.TipoControl',
                'cdm.CantidadDispensada',
                'cdm.LoteUtilizado',
                'cdm.DNIPaciente',
                'cdm.NombrePaciente',
                'cdm.Medico',
                'cdm.RecetaNumero',
                'cdm.FechaDispensacion'
            ])
            ->get();

        // Resumen por medicamento
        $resumen = [];
        foreach ($entradas->unique('CodPro') as $medicamento) {
            $cantidadEntradas = $entradas->where('CodPro', $medicamento->CodPro)->sum('Cantidad');
            $cantidadSalidas = $salidas->where('CodPro', $medicamento->CodPro)->sum('CantidadDispensada');
            
            $resumen[] = [
                'codpro' => $medicamento->CodPro,
                'nombre' => $medicamento->Nombre,
                'coddigemin' => $medicamento->Coddigemin,
                'tipo_control' => $medicamento->TipoControl,
                'cantidad_entradas' => $cantidadEntradas,
                'cantidad_salidas' => $cantidadSalidas,
                'stock_final' => $cantidadEntradas - $cantidadSalidas
            ];
        }

        return compact('entradas', 'salidas', 'resumen');
    }

    /**
     * Dashboard de medicamentos controlados
     */
    public function dashboard()
    {
        // Estadísticas generales
        $totalControlados = DB::table('Productos')
            ->where('EsControlado', true)
            ->count();

        $porTipo = DB::table('Productos')
            ->where('EsControlado', true)
            ->select('TipoControl', DB::raw('COUNT(*) as Cantidad'))
            ->groupBy('TipoControl')
            ->get();

        // Stock en riesgo
        $stockEnRiesgo = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->where('s.Cantidad', '>', 0)
            ->where('s.Vencimiento', '<=', Carbon::now()->addDays(30)->toDateString())
            ->select('p.Nombre', 's.Lote', 's.Cantidad', 's.Vencimiento')
            ->get();

        // Dispensaciones del mes
        $dispensacionesMes = DB::table('ControlDispensacionMedicamentos as cdm')
            ->join('Productos as p', 'cdm.CodPro', '=', 'p.CodPro')
            ->where('cdm.FechaDispensacion', '>=', Carbon::now()->startOfMonth()->toDateString())
            ->count();

        return compact('totalControlados', 'porTipo', 'stockEnRiesgo', 'dispensacionesMes');
    }

    /**
     * Calcular estadísticas de medicamentos controlados
     */
    private function calcularEstadisticasMedicamentosControlados()
    {
        $total = DB::table('Productos')->where('EsControlado', true)->count();
        $porTipo = DB::table('Productos')
            ->where('EsControlado', true)
            ->select('TipoControl', DB::raw('COUNT(*) as Cantidad'))
            ->groupBy('TipoControl')
            ->get()
            ->keyBy('TipoControl');

        $stockTotal = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->sum('s.Cantidad');

        $valorTotal = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->sum(DB::raw('s.Cantidad * s.Costo'));

        return [
            'total_medicamentos' => $total,
            'por_tipo' => $porTipo,
            'stock_total_unidades' => $stockTotal,
            'valor_total_soles' => $valorTotal,
            'estupefacientes' => $porTipo['ESTUPEFACIENTE']->Cantidad ?? 0,
            'psicotropicos' => $porTipo['PSICOTROPO']->Cantidad ?? 0,
            'controlados_especiales' => $porTipo['CONTROLADO_ESPECIAL']->Cantidad ?? 0
        ];
    }

    /**
     * Generar alertas de medicamentos controlados
     */
    private function generarAlertasMedicamentosControlados()
    {
        $alertas = [];

        // Medicamentos próximos a vencer
        $proximosVencer = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->where('s.Vencimiento', '<=', Carbon::now()->addDays(15)->toDateString())
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 's.Lote', 's.Cantidad', 's.Vencimiento', 'p.TipoControl')
            ->get();

        foreach ($proximosVencer as $med) {
            $alertas[] = [
                'tipo' => 'vencimiento',
                'nivel' => 'critico',
                'medicamento' => $med->Nombre,
                'lote' => $med->Lote,
                'cantidad' => $med->Cantidad,
                'vencimiento' => $med->Vencimiento,
                'mensaje' => "MEDICAMENTO CONTROLADO: {$med->Nombre} ({$med->TipoControl}) próximo a vencer",
                'accion' => 'verificar_retiro'
            ];
        }

        // Stock bajo de medicamentos críticos
        $stockBajo = DB::table('Saldos as s')
            ->join('Productos as p', 's.CodPro', '=', 'p.CodPro')
            ->where('p.EsControlado', true)
            ->whereRaw('s.Cantidad <= s.StockMinimo')
            ->where('s.Cantidad', '>', 0)
            ->select('p.Nombre', 's.Lote', 's.Cantidad', 's.StockMinimo', 'p.TipoControl')
            ->get();

        foreach ($stockBajo as $med) {
            $alertas[] = [
                'tipo' => 'stock_bajo',
                'nivel' => 'alto',
                'medicamento' => $med->Nombre,
                'lote' => $med->Lote,
                'cantidad' => $med->Cantidad,
                'stock_minimo' => $med->StockMinimo,
                'mensaje' => "MEDICAMENTO CONTROLADO: {$med->Nombre} con stock bajo",
                'accion' => 'reabastecer_urgente'
            ];
        }

        return $alertas;
    }

    /**
     * Analizar medicamentos por categoría
     */
    private function analizarMedicamentosPorCategoria()
    {
        $categorias = [];

        // Estupefacientes
        $estupefacientes = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->where('p.TipoControl', 'ESTUPEFACIENTE')
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(COALESCE(s.Cantidad, 0)) as StockTotal'),
                DB::raw('COUNT(s.CodPro) as Lotes')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->get();

        // Psicotrópicos
        $psicotropicos = DB::table('Productos as p')
            ->leftJoin('Saldos as s', 'p.CodPro', '=', 's.CodPro')
            ->where('p.TipoControl', 'PSICOTROPO')
            ->select([
                'p.CodPro',
                'p.Nombre',
                DB::raw('SUM(COALESCE(s.Cantidad, 0)) as StockTotal'),
                DB::raw('COUNT(s.CodPro) as Lotes')
            ])
            ->groupBy('p.CodPro', 'p.Nombre')
            ->get();

        $categorias['estupefacientes'] = $estupefacientes;
        $categorias['psicotropicos'] = $psicotropicos;

        return $categorias;
    }

    /**
     * Obtener movimientos recientes
     */
    private function obtenerMovimientosRecientes($codpro)
    {
        // Entradas
        $entradas = DB::table('Doccab as dc')
            ->join('Docdet as dd', 'dc.CodDoc', '=', 'dd.CodDoc')
            ->where('dd.CodPro', $codpro)
            ->whereIn('dc.Tipo', ['COM', 'NOT'])
            ->select([
                'dc.Tipo',
                'dc.Serie',
                'dc.Numero',
                'dc.Fecha',
                'dd.Cantidad',
                'dd.Lote',
                'dd.Costo'
            ])
            ->orderBy('dc.Fecha', 'desc')
            ->limit(10)
            ->get();

        // Salidas (dispensaciones)
        $salidas = DB::table('ControlDispensacionMedicamentos')
            ->where('CodPro', $codpro)
            ->select([
                'CantidadDispensada as Cantidad',
                'LoteUtilizado as Lote',
                'FechaDispensacion as Fecha',
                'NombrePaciente',
                'Medico',
                'RecetaNumero'
            ])
            ->orderBy('FechaDispensacion', 'desc')
            ->limit(10)
            ->get();

        return [
            'entradas' => $entradas,
            'salidas' => $salidas
        ];
    }

    /**
     * Obtener control de dispensación
     */
    private function obtenerControlDispensacion($codpro)
    {
        return DB::table('ControlDispensacionMedicamentos')
            ->where('CodPro', $codpro)
            ->orderBy('FechaDispensacion', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Obtener alertas específicas
     */
    private function obtenerAlertasEspecificas($codpro)
    {
        $alertas = [];

        // Verificar vencimiento próximo
        $vencimientoProximo = DB::table('Saldos')
            ->where('CodPro', $codpro)
            ->where('Vencimiento', '<=', Carbon::now()->addDays(15)->toDateString())
            ->where('Cantidad', '>', 0)
            ->count();

        if ($vencimientoProximo > 0) {
            $alertas[] = [
                'tipo' => 'vencimiento',
                'mensaje' => 'Hay lotes próximos a vencer',
                'nivel' => 'alto'
            ];
        }

        return $alertas;
    }

    /**
     * Generar alerta de alto control
     */
    private function generarAlertaAltoControl($codpro, $tipoControl)
    {
        DB::table('AlertasMedicamentosControlados')->insert([
            'CodPro' => $codpro,
            'TipoAlerta' => 'ALTO_CONTROL',
            'Mensaje' => "Medicamento de alto control registrado: {$tipoControl}",
            'Fecha' => Carbon::now(),
            'Estado' => 'nueva',
            'Usuario' => Auth::id()
        ]);
    }

    /**
     * Generar alerta de dispensación
     */
    private function generarAlertaDispensacion($codpro, $codDispensacion, $paciente)
    {
        DB::table('AlertasMedicamentosControlados')->insert([
            'CodPro' => $codpro,
            'TipoAlerta' => 'DISPENSACION',
            'Mensaje' => "Dispensación registrada para paciente: {$paciente}",
            'Referencia' => $codDispensacion,
            'Fecha' => Carbon::now(),
            'Estado' => 'nueva',
            'Usuario' => Auth::id()
        ]);
    }

    /**
     * Generar código de dispensación
     */
    private function generarCodDispensacion()
    {
        $ultimo = DB::table('ControlDispensacionMedicamentos')
            ->max('CodDispensacion');

        if ($ultimo) {
            $numero = (int) substr($ultimo, 3) + 1;
        } else {
            $numero = 1;
        }

        return 'DIS' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Registrar trazabilidad de medicamento
     */
    private function registrarTrazabilidadMedicamento($codpro, $accion, $descripcion, $cantidad)
    {
        DB::table('TrazabilidadMedicamentosControlados')->insert([
            'CodPro' => $codpro,
            'Accion' => $accion,
            'Descripcion' => $descripcion,
            'Cantidad' => $cantidad,
            'Fecha' => Carbon::now(),
            'Usuario' => Auth::id()
        ]);
    }
}