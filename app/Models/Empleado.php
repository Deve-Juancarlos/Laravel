<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'Empleados';
    
    protected $primaryKey = 'Codemp';
    public $incrementing = true;

    protected $fillable = [
        'Codemp',
        'Nombre',
        'Direccion',
        'Documento',
        'Telefono1',
        'Telefono2',
        'Celular',
        'Nextel',
        'Cumpleaños',
        'Tipo',
    ];

    /**
     * Relación con Clientes asignados (como vendedor)
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'Vendedor', 'Codemp');
    }

    /**
     * Relación con Ventas realizadas
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'Vendedor', 'Codemp');
    }

    /**
     * Relación con Pagos procesados
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'Razon', 'Codemp');
    }

    /**
     * Relación con Proyectos como responsable
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'Vendedor', 'Codemp');
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    /**
     * Scope para vendedores
     */
    public function scopeVendedores($query)
    {
        return $query->where('Tipo', 1);
    }

    /**
     * Scope para administrativos
     */
    public function scopeAdministrativos($query)
    {
        return $query->where('Tipo', 2);
    }

    /**
     * Obtener ventas del mes
     */
    public function getVentasDelMesAttribute(): float
    {
        return $this->ventas()
                   ->whereMonth('Fecha', now()->month)
                   ->whereYear('Fecha', now()->year)
                   ->where('Eliminado', false)
                   ->sum('Total');
    }

    /**
     * Obtener total de clientes asignados
     */
    public function getTotalClientesAttribute(): int
    {
        return $this->clientes()->activos()->count();
    }

    /**
     * Obtener número de ventas realizadas
     */
    public function getNumeroVentasAttribute(): int
    {
        return $this->ventas()
                   ->where('Eliminado', false)
                   ->count();
    }

    /**
     * Obtener promedio de venta
     */
    public function getPromedioVentaAttribute(): float
    {
        $ventas = $this->ventas()->where('Eliminado', false)->get();
        
        if ($ventas->isEmpty()) return 0;
        
        return $ventas->sum('Total') / $ventas->count();
    }

    /**
     * Obtener información del empleado
     */
    public function getInformacionEmpleadoAttribute(): array
    {
        return [
            'codigo' => $this->Codemp,
            'nombre' => $this->Nombre,
            'documento' => $this->Documento,
            'direccion' => $this->Direccion,
            'telefono' => $this->Telefono1,
            'celular' => $this->Celular,
            'cumpleanos' => $this->Cumpleaños,
            'tipo' => $this->tipo_nombre,
            'total_clientes' => $this->total_clientes,
            'ventas_mes' => $this->ventas_del_mes,
            'numero_ventas' => $this->numero_ventas,
            'promedio_venta' => round($this->promedio_venta, 2),
        ];
    }

    /**
     * Obtener nombre del tipo
     */
    public function getTipoNombreAttribute(): string
    {
        $tipos = [
            1 => 'Vendedor',
            2 => 'Administrativo',
            3 => 'Supervisor',
            4 => 'Gerente',
        ];

        return $tipos[$this->Tipo] ?? 'Desconocido';
    }
}