<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LibrosElectronicosController extends Controller
{
    /**
     * Display a listing of electronic books.
     */
    public function index()
    {
        $libros = $this->getLibrosElectronicos();
        $estadisticas = $this->getEstadisticasLibros();

        return view('contabilidad.libros-electronicos.index', compact('libros', 'estadisticas'));
    }

    /**
     * Show electronic books entries (asientos).
     */
    public function asientos()
    {
        $asientos = $this->getAsientos();
        $cuentas = $this->getPlanCuentas();

        return view('contabilidad.libros-electronicos.asientos', compact('asientos', 'cuentas'));
    }

    /**
     * Store a new entry.
     */
    public function storeAsiento(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'glosa' => 'required|string|max:255',
            'cuentas' => 'required|array',
            'cuentas.*.cuenta_id' => 'required|integer',
            'cuentas.*.descripcion' => 'required|string',
            'cuentas.*.debe' => 'required|numeric|min:0',
            'cuentas.*.haber' => 'required|numeric|min:0',
        ]);

        // TODO: Implementar creación de asiento en base de datos
        // Asiento::create($request->all());

        return redirect()
            ->route('contabilidad.libros.asientos')
            ->with('success', 'Asiento contable registrado correctamente.');
    }

    /**
     * Show a specific entry.
     */
    public function showAsiento($asiento)
    {
        $asientoData = $this->getAsiento($asiento);

        if (!$asientoData) {
            return redirect()
                ->route('contabilidad.libros.asientos')
                ->with('error', 'Asiento contable no encontrado.');
        }

        return view('contabilidad.libros-electronicos.asiento-detalle', compact('asientoData'));
    }

    /**
     * Update a specific entry.
     */
    public function updateAsiento(Request $request, $asiento)
    {
        $request->validate([
            'glosa' => 'required|string|max:255',
            'cuentas' => 'required|array',
        ]);

        // TODO: Implementar actualización en base de datos
        // Asiento::where('id', $asiento)->update($request->all());

        return redirect()
            ->route('contabilidad.libros.asientos')
            ->with('success', 'Asiento contable actualizado correctamente.');
    }

    /**
     * Delete a specific entry.
     */
    public function destroyAsiento($asiento)
    {
        // TODO: Implementar eliminación en base de datos
        // Asiento::where('id', $asiento)->delete();

        return redirect()
            ->route('contabilidad.libros.asientos')
            ->with('success', 'Asiento contable eliminado correctamente.');
    }

    /**
     * Show the general ledger (libro mayor).
     */
    public function libroMayor()
    {
        $cuentas = $this->getPlanCuentas();
        $movimientos = $this->getMovimientosLibroMayor();

        return view('contabilidad.libros-electronicos.libro-mayor', compact('cuentas', 'movimientos'));
    }

    /**
     * Show the trial balance (balance de comprobación).
     */
    public function balanceComprobacion()
    {
        $balances = $this->getBalancesComprobacion();
        $totales = $this->getTotalesBalance();

        return view('contabilidad.libros-electronicos.balance-comprobacion', compact('balances', 'totales'));
    }

    /**
     * Show chart of accounts (plan de cuentas).
     */
    public function planCuentas()
    {
        $cuentas = $this->getPlanCuentas();
        $estructura = $this->getEstructuraPlan();

        return view('contabilidad.libros-electronicos.plan-cuentas', compact('cuentas', 'estructura'));
    }

    /**
     * Store a new account.
     */
    public function storeCuenta(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|unique:plan_cuentas,codigo',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:active,passive,patrimonio,ingreso,gasto',
            'cuenta_padre' => 'nullable|integer',
        ]);

        // TODO: Implementar creación de cuenta en base de datos
        // PlanCuenta::create($request->all());

        return redirect()
            ->route('contabilidad.libros.plan-cuentas')
            ->with('success', 'Cuenta contable creada correctamente.');
    }

    /**
     * Show electronic books reports.
     */
    public function reportes()
    {
        $reportes = $this->getReportesDisponibles();
        $periodos = $this->getPeriodos();

        return view('contabilidad.libros-electronicos.reportes', compact('reportes', 'periodos'));
    }

    /**
     * Export electronic books data.
     */
    public function exportar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:asientos,mayor,balance,reportes',
            'formato' => 'required|in:excel,pdf,csv',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        // TODO: Implementar exportación según tipo y formato
        // Generar archivo y retornarlo para descarga

        return redirect()
            ->route('contabilidad.libros.reportes')
            ->with('success', 'Exportación generada correctamente.');
    }

    /**
     * Get available accounts for API
     */
    public function getCuentasDisponibles()
    {
        $cuentas = $this->getPlanCuentas();

        return response()->json([
            'cuentas' => $cuentas->map(function($cuenta) {
                return [
                    'id' => $cuenta['id'],
                    'codigo' => $cuenta['codigo'],
                    'nombre' => $cuenta['nombre'],
                    'tipo' => $cuenta['tipo'],
                ];
            })
        ]);
    }

    /**
     * Validate entry for API
     */
    public function validarAsiento(Request $request)
    {
        $request->validate([
            'cuentas' => 'required|array',
        ]);

        $validacion = $this->validarAsientoData($request->cuentas);

        return response()->json($validacion);
    }

    /**
     * Get electronic books data
     */
    private function getLibrosElectronicos()
    {
        return collect([
            [
                'id' => 1,
                'tipo' => 'Diario',
                'nombre' => 'Libro Diario Electrónico',
                'descripcion' => 'Registro cronológico de operaciones contables',
                'estado' => 'activo',
                'ultimo_asiento' => '2025-10-26',
                'total_asientos' => 1250,
            ],
            [
                'id' => 2,
                'tipo' => 'Mayor',
                'nombre' => 'Libro Mayor Electrónico',
                'descripcion' => 'Registro por cuentas del movimiento de valores',
                'estado' => 'activo',
                'ultimo_asiento' => '2025-10-26',
                'total_cuentas' => 85,
            ],
            [
                'id' => 3,
                'tipo' => 'Inventarios',
                'nombre' => 'Libro de Inventarios y Balances',
                'descripcion' => 'Registro detallado del patrimonio de la empresa',
                'estado' => 'activo',
                'ultimo_asiento' => '2025-10-25',
                'total_items' => 245,
            ],
        ]);
    }

    /**
     * Get books statistics
     */
    private function getEstadisticasLibros()
    {
        return [
            'total_libros' => 3,
            'libros_activos' => 3,
            'asientos_mes' => 156,
            'operaciones_hoy' => 8,
            'balance_cuadrado' => true,
        ];
    }

    /**
     * Get accounting entries
     */
    private function getAsientos()
    {
        return collect([
            [
                'id' => 1,
                'numero' => '001',
                'fecha' => '2025-10-26',
                'glosa' => 'Venta de medicamentos - Farmacia San Rafael',
                'total_debe' => 1250.80,
                'total_haber' => 1250.80,
                'estado' => 'aprobado',
                'usuario' => 'Ana Martínez',
            ],
            [
                'id' => 2,
                'numero' => '002',
                'fecha' => '2025-10-26',
                'glosa' => 'Compra de inventario - Laboratorio XYZ',
                'total_debe' => 750.00,
                'total_haber' => 750.00,
                'estado' => 'aprobado',
                'usuario' => 'Carlos López',
            ],
            [
                'id' => 3,
                'numero' => '003',
                'fecha' => '2025-10-25',
                'glosa' => 'Pago de servicios básicos',
                'total_debe' => 450.25,
                'total_haber' => 450.25,
                'estado' => 'borrador',
                'usuario' => 'María González',
            ],
        ]);
    }

    /**
     * Get plan of accounts
     */
    private function getPlanCuentas()
    {
        return collect([
            [
                'id' => 1,
                'codigo' => '10',
                'nombre' => 'Efectivo y Equivalentes de Efectivo',
                'tipo' => 'active',
                'nivel' => 1,
                'saldo' => 45680.50,
            ],
            [
                'id' => 2,
                'codigo' => '10.1',
                'nombre' => 'Caja General',
                'tipo' => 'active',
                'nivel' => 2,
                'saldo' => 25680.50,
            ],
            [
                'id' => 3,
                'codigo' => '10.2',
                'nombre' => 'Bancos',
                'tipo' => 'active',
                'nivel' => 2,
                'saldo' => 20000.00,
            ],
            [
                'id' => 4,
                'codigo' => '20',
                'nombre' => 'Cuentas por Cobrar Comerciales',
                'tipo' => 'active',
                'nivel' => 1,
                'saldo' => 12840.50,
            ],
            [
                'id' => 5,
                'codigo' => '40',
                'nombre' => 'Cuentas por Pagar Comerciales',
                'tipo' => 'passive',
                'nivel' => 1,
                'saldo' => 8930.25,
            ],
            [
                'id' => 6,
                'codigo' => '70',
                'nombre' => 'Ventas',
                'tipo' => 'ingreso',
                'nivel' => 1,
                'saldo' => 156780.50,
            ],
        ]);
    }

    /**
     * Get general ledger movements
     */
    private function getMovimientosLibroMayor()
    {
        return collect([
            [
                'cuenta' => 'Caja General',
                'codigo' => '10.1',
                'saldo_anterior' => 24580.50,
                'debe_mes' => 1250.80,
                'haber_mes' => 1800.00,
                'saldo_actual' => 24031.30,
            ],
            [
                'cuenta' => 'Bancos',
                'codigo' => '10.2',
                'saldo_anterior' => 18500.00,
                'debe_mes' => 0.00,
                'haber_mes' => 750.00,
                'saldo_actual' => 17750.00,
            ],
        ]);
    }

    /**
     * Get trial balances
     */
    private function getBalancesComprobacion()
    {
        return collect([
            [
                'codigo' => '10',
                'nombre' => 'Efectivo y Equivalentes',
                'saldo_anterior' => 43080.50,
                'movimientos_debe' => 1250.80,
                'movimientos_haber' => 2550.00,
                'saldo_actual' => 41781.30,
            ],
            [
                'codigo' => '20',
                'nombre' => 'Cuentas por Cobrar',
                'saldo_anterior' => 11540.50,
                'movimientos_debe' => 1250.80,
                'movimientos_haber' => 0.00,
                'saldo_actual' => 12791.30,
            ],
        ]);
    }

    /**
     * Get balance totals
     */
    private function getTotalesBalance()
    {
        return [
            'total_debe_anterior' => 54621.00,
            'total_haber_anterior' => 54621.00,
            'total_movimientos_debe' => 1250.80,
            'total_movimientos_haber' => 2550.00,
            'total_saldo_debe' => 54572.60,
            'total_saldo_haber' => 54572.60,
        ];
    }

    /**
     * Get account structure
     */
    private function getEstructuraPlan()
    {
        return [
            'activos' => ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19'],
            'pasivos' => ['20', '21', '22', '23', '24', '25', '26', '27', '28', '29'],
            'patrimonio' => ['30', '31', '32', '33', '34', '35', '36', '37', '38', '39'],
            'ingresos' => ['40', '41', '42', '43', '44', '45', '46', '47', '48', '49'],
            'gastos' => ['50', '51', '52', '53', '54', '55', '56', '57', '58', '59'],
        ];
    }

    /**
     * Get available reports
     */
    private function getReportesDisponibles()
    {
        return collect([
            [
                'codigo' => 'LD01',
                'nombre' => 'Libro Diario Completo',
                'descripcion' => 'Reporte completo de todas las operaciones',
                'formato' => ['excel', 'pdf'],
            ],
            [
                'codigo' => 'LM01',
                'nombre' => 'Libro Mayor por Cuenta',
                'descripcion' => 'Movimientos detallados por cuenta contable',
                'formato' => ['excel', 'pdf', 'csv'],
            ],
            [
                'codigo' => 'BC01',
                'nombre' => 'Balance de Comprobación',
                'descripcion' => 'Verificación de cuadre contable',
                'formato' => ['excel', 'pdf'],
            ],
        ]);
    }

    /**
     * Get reporting periods
     */
    private function getPeriodos()
    {
        return collect([
            ['periodo' => '2025-10', 'nombre' => 'Octubre 2025'],
            ['periodo' => '2025-09', 'nombre' => 'Septiembre 2025'],
            ['periodo' => '2025-08', 'nombre' => 'Agosto 2025'],
            ['periodo' => '2025-07', 'nombre' => 'Julio 2025'],
        ]);
    }

    /**
     * Get a specific entry
     */
    private function getAsiento($id)
    {
        $asientos = $this->getAsientos();
        return $asientos->where('id', $id)->first();
    }

    /**
     * Validate entry data
     */
    private function validarAsientoData($cuentas)
    {
        $totalDebe = 0;
        $totalHaber = 0;
        $errores = [];

        foreach ($cuentas as $index => $cuenta) {
            if ($cuenta['debe'] < 0 || $cuenta['haber'] < 0) {
                $errores[] = "Línea " . ($index + 1) . ": Los montos deben ser positivos";
            }
            
            if ($cuenta['debe'] > 0 && $cuenta['haber'] > 0) {
                $errores[] = "Línea " . ($index + 1) . ": No puede tener débito y crédito simultáneos";
            }

            $totalDebe += $cuenta['debe'];
            $totalHaber += $cuenta['haber'];
        }

        if (abs($totalDebe - $totalHaber) > 0.01) {
            $errores[] = "El asiento no cuadra: Débito (" . number_format($totalDebe, 2) . ") ≠ Crédito (" . number_format($totalHaber, 2) . ")";
        }

        return [
            'valido' => empty($errores),
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'diferencia' => abs($totalDebe - $totalHaber),
            'errores' => $errores,
        ];
    }
}