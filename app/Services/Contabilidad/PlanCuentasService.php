<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanCuentasService
{
    /**
     * Obtiene el listado de cuentas (paginado) y los filtros.
     */
    public function get(array $filters): array
    {
        $query = DB::table('plan_cuentas')
                   ->leftJoin('plan_cuentas as padre', 'plan_cuentas.cuenta_padre', '=', 'padre.codigo')
                   ->select('plan_cuentas.*', 'padre.nombre as nombre_padre');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('plan_cuentas.codigo', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('plan_cuentas.nombre', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['tipo'])) {
            $query->where('plan_cuentas.tipo', $filters['tipo']);
        }
        
        if (!empty($filters['activo'])) {
             $query->where('plan_cuentas.activo', $filters['activo'] == '1');
        }

        $cuentas = $query->orderBy('plan_cuentas.codigo')->paginate(50)->withQueryString();
        
        $tipos = $this->getTiposDeCuenta();

        return compact('cuentas', 'tipos', 'filters');
    }

    /**
     * Obtiene los datos necesarios para los formularios (create/edit).
     */
    public function getFormData(string $codigo = null): array
    {
        $cuenta = null;
        if ($codigo) {
            $cuenta = DB::table('plan_cuentas')->where('codigo', $codigo)->first();
        }

        // Obtener solo cuentas de nivel superior para ser "Padre"
        $cuentasPadre = DB::table('plan_cuentas')
            ->where('activo', 1)
            ->where(function($q) {
                $q->where('nivel', 1) // Nivel 1
                  ->orWhereNull('cuenta_padre'); // O sin padre
            })
            ->orderBy('codigo')
            ->pluck('nombre', 'codigo');
            
        $tipos = $this->getTiposDeCuenta();

        return compact('cuenta', 'cuentasPadre', 'tipos');
    }

    /**
     * Crea una nueva cuenta.
     */
    public function create(array $data): void
    {
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('plan_cuentas')->insert($data);
    }

    /**
     * Actualiza una cuenta existente.
     */
    public function update(string $codigo, array $data): void
    {
        $data['updated_at'] = now();
        DB::table('plan_cuentas')->where('codigo', $codigo)->update($data);
    }

    /**
     * Elimina una cuenta, con validaciones.
     */
    public function delete(string $codigo): array
    {
        // 1. Validar que no sea cuenta padre
        $esPadre = DB::table('plan_cuentas')->where('cuenta_padre', $codigo)->exists();
        if ($esPadre) {
            return ['success' => false, 'message' => 'No se puede eliminar. Esta cuenta es "Cuenta Padre" de otras.'];
        }

        // 2. Validar que no tenga movimientos en el libro diario
        $estaEnUso = DB::table('libro_diario_detalles')->where('cuenta_contable', $codigo)->exists();
        if ($estaEnUso) {
            return ['success' => false, 'message' => 'No se puede eliminar. Esta cuenta ya tiene movimientos en el Libro Diario. Puede desactivarla en su lugar.'];
        }

        // 3. Eliminar
        DB::table('plan_cuentas')->where('codigo', $codigo)->delete();
        
        return ['success' => true, 'message' => 'Cuenta eliminada exitosamente.'];
    }

    /**
     * Obtiene los tipos de cuenta estándar.
     */
    public function getTiposDeCuenta(): array
    {
        return ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO'];
    }
}
