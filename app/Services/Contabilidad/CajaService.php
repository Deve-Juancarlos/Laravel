<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CajaService
{
    // Inyectamos el servicio de Libro Diario para reutilizar su lógica
    protected $libroDiarioService;

    public function __construct(LibroDiarioService $libroDiarioService)
    {
        $this->libroDiarioService = $libroDiarioService;
    }

    /**
     * Obtiene los datos para la vista index.
     */
    public function getIndexData(array $filters): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $filters['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $tipoMovimiento = $filters['tipo_movimiento'] ?? null;
        $usuario = $filters['usuario'] ?? null;

        // 1. Saldo Inicial
        $saldoInicial = DB::table('Caja')
            ->where('Fecha', '<', $fechaInicio)
            ->where('Eliminado', 0) // Excluir anulados
            ->selectRaw('SUM(CASE WHEN Tipo = 1 THEN Monto ELSE -Monto END) as saldo')
            ->value('saldo') ?? 0;

        // 2. Query base de movimientos
        // CORRECCIÓN: La 'Razon' es un INT que ahora asumimos es un CÓDIGO DE CUENTA
        $query = DB::table('Caja as c')
            ->leftJoin('plan_cuentas as pc', 'c.Razon', '=', 'pc.codigo')
            ->whereBetween('c.Fecha', [$fechaInicio, $fechaFin])
            // ->where('c.Eliminado', 0) // Mostramos todo, pero marcamos anulados
            ->select('c.*', 'pc.nombre as razon_descripcion');

        if ($tipoMovimiento) {
            $query->where('c.Tipo', $tipoMovimiento);
        }
        if ($usuario) {
            // Asumimos que el filtro 'usuario' busca en la 'Razon' (cuenta contable)
            $query->where('c.Razon', $usuario); 
        }

        $movimientos = $query->clone()->orderBy('c.Fecha', 'desc')->orderBy('c.Numero', 'desc')->paginate(50);

        // 3. Totales del Período (Solo movimientos NO eliminados)
        $totalesQuery = DB::table('Caja')
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            ->where('Eliminado', 0); // Solo sumar activos
        
        if ($tipoMovimiento) {
            $totalesQuery->where('Tipo', $tipoMovimiento);
        }
        if ($usuario) {
            $totalesQuery->where('Razon', $usuario);
        }
        
        $totales = $totalesQuery->selectRaw('
                SUM(CASE WHEN Tipo = 1 THEN Monto ELSE 0 END) as ingresos,
                SUM(CASE WHEN Tipo = 2 THEN Monto ELSE 0 END) as egresos
            ')
            ->first();

        $totalesPeriodo = [
            'ingresos' => $totales->ingresos ?? 0,
            'egresos' => $totales->egresos ?? 0,
            'neto' => ($totales->ingresos ?? 0) - ($totales->egresos ?? 0)
        ];

        // 4. Saldo Final
        $saldoFinal = $saldoInicial + $totalesPeriodo['neto'];

        // 5. Lista de Razones (Cuentas)
        $listaUsuarios = DB::table('plan_cuentas')
            ->where('activo', 1)
            ->where(function($q) {
                $q->where('codigo', 'LIKE', '6%') // Gastos
                  ->orWhere('codigo', 'LIKE', '7%') // Ingresos
                  ->orWhere('codigo', 'LIKE', '12%'); // Clientes
            })
            ->select('codigo as id', 'nombre')
            ->orderBy('codigo')
            ->get();

        return [
            'movimientos' => $movimientos,
            'saldoInicial' => $saldoInicial,
            'totalesPeriodo' => (object) $totalesPeriodo,
            'saldoFinal' => $saldoFinal,
            'listaUsuarios' => $listaUsuarios, // Renombrado, pero es la lista de cuentas
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tipoMovimiento' => $tipoMovimiento,
            'usuario' => $usuario, // 'usuario' es el 'razon_id' filtrado
        ];
    }

    /**
     * Obtiene los datos necesarios para el formulario de creación.
     */
    public function getCreateData(): array
    {
        // Usamos la función de la BD para obtener descripciones de la tabla maestra
        $tiposMovimiento = collect(DB::select("SELECT n_numero, c_describe FROM Tablas WHERE n_codtabla = 3 AND n_numero IN (1, 2)"));
        $clasesOperacion = collect(DB::select("SELECT n_numero, c_describe FROM Tablas WHERE n_codtabla = 4"));
        
        // Cuentas de Caja (Ej: 10101 - Caja General M/N)
        $cuentasCaja = DB::table('plan_cuentas')
            ->where('codigo', 'LIKE', '101%') // Cuentas de Caja
            ->where('activo', 1)
            ->select('codigo', 'nombre')
            ->get();
            
        // Cuentas de contrapartida (gastos, ingresos, etc.)
        $cuentasContrapartida = DB::table('plan_cuentas')
            ->where('activo', 1)
            ->where('codigo', 'NOT LIKE', '101%') // Excluir las propias cuentas de caja
            ->select('codigo', 'nombre', 'tipo')
            ->orderBy('codigo')
            ->get()
            ->groupBy('tipo');

        return compact('tiposMovimiento', 'clasesOperacion', 'cuentasCaja', 'cuentasContrapartida');
    }

    /**
     * Guarda el movimiento en Caja y crea el Asiento Contable (Transaccional)
     */
    public function storeMovimiento(array $data)
    {
        return DB::transaction(function () use ($data) {
            $monto = (float) $data['monto'];
            $tipo = (int) $data['tipo'];
            $glosa = $data['glosa'];
            $fecha = $data['fecha'];
            $usuarioId = Auth::id() ?? null;
            
            // 1. Definir cuentas
            $cuentaCaja = $data['cuenta_caja']; // Ej: 10101
            $cuentaContrapartida = $data['razon_id']; // Ej: 63 (Gastos) o 70 (Ventas)
            
            $esIngreso = ($tipo == 1);
            
            // 2. Crear Asiento Contable
            $asientoId = DB::table('libro_diario')->insertGetId([
                // ******** LA CORRECCIÓN ESTÁ AQUÍ ********
                // Antes: 'numero' => $this->libroDiarioService->generarNumeroUnicoAsiento(),
                'numero' => $this->libroDiarioService->obtenerSiguienteNumeroAsiento(), // <-- Nombre corregido
                // *****************************************
                'fecha' => $fecha,
                'glosa' => $glosa,
                'total_debe' => $monto,
                'total_haber' => $monto,
                'balanceado' => 1,
                'estado' => 'ACTIVO',
                'usuario_id' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Crear Detalles del Asiento
            // Detalle 1: Cuenta de Caja (101)
            DB::table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId,
                'cuenta_contable' => $cuentaCaja,
                'debe' => $esIngreso ? $monto : 0,
                'haber' => $esIngreso ? 0 : $monto,
                'concepto' => $glosa,
                'documento_referencia' => $data['documento'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Detalle 2: Cuenta Contrapartida (63, 70, etc.)
            DB::table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId,
                'cuenta_contable' => $cuentaContrapartida,
                'debe' => $esIngreso ? 0 : $monto,
                'haber' => $esIngreso ? $monto : 0,
                'concepto' => $glosa,
                'documento_referencia' => $data['documento'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Crear Movimiento de Caja y vincularlo
            // La columna 'Razon' ahora guarda el código de la contrapartida (razon_id)
            $cajaId = DB::table('Caja')->insertGetId([
                'Documento' => $data['documento'],
                'Tipo' => $tipo,
                'Razon' => $data['razon_id'], // Guardamos la contrapartida
                'Fecha' => $fecha,
                'Moneda' => 1, // Asumimos Soles (debería venir del form)
                'Cambio' => 1.00,
                'Monto' => $monto,
                'Eliminado' => 0,
                'asiento_id' => $asientoId // ¡Vinculamos el asiento!
            ]);
            
            // 5. Devolver el objeto de caja con el número de asiento
            $movimiento = DB::table('Caja')->where('Numero', $cajaId)->first();
            $movimiento->asiento_numero = DB::table('libro_diario')->where('id', $asientoId)->value('numero');
            
            return $movimiento;
        });
    }

    /**
     * Obtiene los datos para la vista show.
     */
    public function getShowData($id): array
    {
        $movimiento = DB::table('Caja as c')
            ->leftJoin('plan_cuentas as pc', 'c.Razon', '=', 'pc.codigo')
            ->where('c.Numero', $id)
            ->select('c.*', 'pc.nombre as contrapartida_nombre')
            ->first();

        $asiento = null;
        $detalles = null;

        if ($movimiento && $movimiento->asiento_id) {
            $asiento = DB::table('libro_diario')->where('id', $movimiento->asiento_id)->first();
            if ($asiento) {
                $detalles = DB::table('libro_diario_detalles as d')
                    ->leftJoin('plan_cuentas as pc', 'd.cuenta_contable', '=', 'pc.codigo')
                    ->where('d.asiento_id', $asiento->id)
                    ->select('d.*', 'pc.nombre as cuenta_nombre')
                    ->get();
            }
        }

        return compact('movimiento', 'asiento', 'detalles');
    }

    /**
     * Obtiene los datos para la vista edit.
     */
    public function getEditData($id): array
    {
        // Obtenemos el movimiento y sus datos relacionados
        $data = $this->getShowData($id);
        
        // Obtenemos los datos para los dropdowns del formulario
        $formData = $this->getCreateData();
        
        return array_merge($data, $formData);
    }

    /**
     * Actualiza el movimiento en Caja y el Asiento Contable (Transaccional)
     */
    public function updateMovimiento($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $movimiento = DB::table('Caja')->where('Numero', $id)->first();
            if (!$movimiento) {
                throw new \Exception('Movimiento de caja no encontrado.');
            }
            
            // 1. Actualizar Caja
            DB::table('Caja')->where('Numero', $id)->update([
                'Fecha' => $data['fecha'],
                'Documento' => $data['documento'],
                // 'glosa' no existe en la tabla Caja, se actualiza en el asiento
            ]);

            // 2. Actualizar Asiento Contable
            if ($movimiento->asiento_id) {
                DB::table('libro_diario')->where('id', $movimiento->asiento_id)->update([
                    'fecha' => $data['fecha'],
                    'glosa' => $data['glosa'],
                    'observaciones' => 'Actualizado el ' . now(),
                    'updated_at' => now()
                ]);
                
                // 3. Actualizar Detalles del Asiento
                DB::table('libro_diario_detalles')->where('asiento_id', $movimiento->asiento_id)->update([
                    'concepto' => $data['glosa'],
                    'documento_referencia' => $data['documento'],
                    'updated_at' => now()
                ]);
            }
            
            return $movimiento;
        });
    }

    /**
     * Anula el movimiento en Caja y el Asiento Contable (Transaccional)
     */
    public function anularMovimiento($id)
    {
        return DB::transaction(function () use ($id) {
            $movimiento = DB::table('Caja')->where('Numero', $id)->first();
            if (!$movimiento) {
                throw new \Exception('Movimiento de caja no encontrado.');
            }

            // 1. Marcar Caja como Eliminado (Anulado)
            DB::table('Caja')->where('Numero', $id)->update([
                'Eliminado' => 1 // Usamos el campo 'Eliminado'
            ]);

            // 2. Marcar Asiento como ANULADO
            if ($movimiento->asiento_id) {
                DB::table('libro_diario')->where('id', $movimiento->asiento_id)->update([
                    'estado' => 'ANULADO',
                    'glosa' => '[ANULADO] ' . DB::table('libro_diario')->where('id', $movimiento->asiento_id)->value('glosa'),
                    'total_debe' => 0,
                    'total_haber' => 0,
                    'updated_at' => now()
                ]);
                
                // 3. Poner en CERO los detalles (o borrarlos)
                DB::table('libro_diario_detalles')->where('asiento_id', $movimiento->asiento_id)->update([
                    'debe' => 0,
                    'haber' => 0,
                ]);
            }

            return $movimiento;
        });
    }
}

