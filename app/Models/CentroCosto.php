<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroCosto extends Model
{
    use HasFactory;

    protected $table = 'Zonas';
    protected $primaryKey = 'Codzona';

    protected $fillable = [
        'Codzona',
        'Descripcion',
        'Sector',
        'Distrito',
        'Provincia',
        'Dpto',
        'Zonageo',
        'responsable_id',
        'presupuesto',
        'gastos_realizados',
        'fecha_apertura',
        'fecha_cierre',
        'activo',
        'tipo_centro',
        'notas',
        'objetivos',
        'kpis',
    ];

    protected $casts = [
        'presupuesto' => 'decimal:2',
        'gastos_realizados' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'activo' => 'boolean',
        'objetivos' => 'array',
        'kpis' => 'array',
    ];

    /**
     * Relación con Empleado (Responsable)
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'responsable_id', 'Codemp');
    }

    /**
     * Relación con Clientes del centro de costo
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'Zona', 'Codzona');
    }

    /**
     * Relación con Empleados del centro de costo
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'Zona', 'Codzona');
    }

    /**
     * Relación con Proyectos del centro de costo
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'codigo_centro', 'Codzona');
    }

    /**
     * Scope para centros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        $tipos = [
            'ventas' => 1,
            'marketing' => 2,
            'operaciones' => 3,
            'administrativo' => 4,
            'financiero' => 5,
            'logistica' => 6,
        ];

        return $query->where('tipo_centro', $tipos[$tipo] ?? $tipo);
    }

    /**
     * Scope por responsable
     */
    public function scopePorResponsable($query, $responsableId)
    {
        return $query->where('responsable_id', $responsableId);
    }

    /**
     * Scope para centros con presupuesto definido
     */
    public function scopeConPresupuesto($query)
    {
        return $query->whereNotNull('presupuesto')
                   ->where('presupuesto', '>', 0);
    }

    /**
     * Scope por región
     */
    public function scopePorRegion($query, $departamento, $provincia = null)
    {
        $query = $query->where('Dpto', 'like', "%{$departamento}%");
        
        if ($provincia) {
            $query->where('Provincia', 'like', "%{$provincia}%");
        }
        
        return $query;
    }

    /**
     * Obtener nombre del tipo de centro
     */
    public function getTipoCentroNombreAttribute(): string
    {
        $tipos = [
            1 => 'Ventas',
            2 => 'Marketing',
            3 => 'Operaciones',
            4 => 'Administrativo',
            5 => 'Financiero',
            6 => 'Logística',
        ];

        return $tipos[$this->tipo_centro] ?? 'General';
    }

    /**
     * Obtener presupuesto disponible
     */
    public function getPresupuestoDisponibleAttribute(): float
    {
        return max(0, ($this->presupuesto ?? 0) - ($this->gastos_realizados ?? 0));
    }

    /**
     * Obtener porcentaje de ejecución presupuestal
     */
    public function getPorcentajeEjecucionAttribute(): float
    {
        if (!$this->presupuesto || $this->presupuesto <= 0) {
            return 0;
        }
        
        return min(100, (($this->gastos_realizados ?? 0) / $this->presupuesto) * 100);
    }

    /**
     * Verificar si está sobre presupuesto
     */
    public function getSobrePresupuestoAttribute(): bool
    {
        return $this->gastos_realizados > $this->presupuesto;
    }

    /**
     * Verificar si está cerca del límite (90%)
     */
    public function getCercaLimiteAttribute(): bool
    {
        return $this->porcentaje_ejecucion >= 90 && !$this->sobre_presupuesto;
    }

    /**
     * Obtener estado presupuestal
     */
    public function getEstadoPresupuestalAttribute(): string
    {
        if ($this->sobre_presupuesto) {
            return 'Sobre Presupuesto';
        } elseif ($this->cerca_limite) {
            return 'Cerca del Límite';
        } elseif ($this->presupuesto_disponible > 0) {
            return 'En Presupuesto';
        } else {
            return 'Sin Presupuesto';
        }
    }

    /**
     * Obtener total de clientes
     */
    public function getTotalClientesAttribute(): int
    {
        return $this->clientes()->count();
    }

    /**
     * Obtener total de empleados
     */
    public function getTotalEmpleadosAttribute(): int
    {
        return $this->empleados()->count();
    }

    /**
     * Obtener total de proyectos activos
     */
    public function getTotalProyectosAttribute(): int
    {
        return $this->proyectos()->activos()->count();
    }

    /**
     * Obtener ventas del centro de costo (últimos 30 días)
     */
    public function getVentasRecientesAttribute(): float
    {
        return $this->clientes()
                   ->with(['ventas' => function ($query) {
                       $query->where('Fecha', '>=', now()->subDays(30))
                             ->where('Eliminado', false);
                   }])
                   ->get()
                   ->flatMap->ventas
                   ->sum('Total');
    }

    /**
     * Obtener KPI del centro
     */
    public function getKpisAttribute(): array
    {
        $kpis = $this->kpis ?? [];
        
        // KPIs automáticos basados en datos
        $kpis['total_clientes'] = $this->total_clientes;
        $kpis['total_empleados'] = $this->total_empleados;
        $kpis['total_proyectos'] = $this->total_proyectos;
        $kpis['ventas_recientes'] = $this->ventas_recientes;
        $kpis['porcentaje_ejecucion'] = round($this->porcentaje_ejecucion, 2);
        $kpis['presupuesto_disponible'] = $this->presupuesto_disponible;
        $kpis['estado_presupuestal'] = $this->estado_presupuestal;
        
        return $kpis;
    }

    /**
     * Obtener ubicación completa
     */
    public function getUbicacionCompletaAttribute(): string
    {
        $ubicacion = $this->Descripcion;
        
        if ($this->Distrito) {
            $ubicacion .= ", {$this->Distrito}";
        }
        
        if ($this->Provincia) {
            $ubicacion .= ", {$this->Provincia}";
        }
        
        if ($this->Dpto) {
            $ubicacion .= ", {$this->Dpto}";
        }
        
        return $ubicacion;
    }

    /**
     * Obtener resumen financiero
     */
    public function getResumenFinancieroAttribute(): array
    {
        return [
            'presupuesto_asignado' => $this->presupuesto ?? 0,
            'gastos_realizados' => $this->gastos_realizados ?? 0,
            'presupuesto_disponible' => $this->presupuesto_disponible,
            'porcentaje_ejecucion' => $this->porcentaje_ejecucion,
            'estado_presupuestal' => $this->estado_presupuestal,
            'sobre_presupuesto' => $this->sobre_presupuesto,
            'cerca_limite' => $this->cerca_limite,
        ];
    }

    /**
     * Obtener resumen operativo
     */
    public function getResumenOperativoAttribute(): array
    {
        return [
            'total_clientes' => $this->total_clientes,
            'total_empleados' => $this->total_empleados,
            'total_proyectos' => $this->total_proyectos,
            'ventas_recientes' => $this->ventas_recientes,
            'responsable' => $this->responsable ? $this->responsable->Nombre : 'Sin asignar',
            'tipo_centro' => $this->tipo_centro_nombre,
            'ubicacion' => $this->ubicacion_completa,
            'activo' => $this->activo,
        ];
    }

    /**
     * Actualizar gastos
     */
    public function actualizarGastos($monto, $descripcion = '')
    {
        $this->update([
            'gastos_realizados' => ($this->gastos_realizados ?? 0) + $monto,
        ]);
    }

    /**
     * Obtener performance general
     */
    public function getPerformanceAttribute(): array
    {
        return [
            'financiero' => $this->resumen_financiero,
            'operativo' => $this->resumen_operativo,
            'kpis' => $this->kpis,
            'estado_general' => $this->activo ? 'Activo' : 'Inactivo',
        ];
    }

    /**
     * Obtener alertas del centro de costo
     */
    public function getAlertasAttribute(): array
    {
        $alertas = [];
        
        if ($this->sobre_presupuesto) {
            $alertas[] = [
                'tipo' => 'error',
                'mensaje' => 'El centro de costo está sobre presupuesto',
                'valor' => $this->gastos_realizados - $this->presupuesto,
            ];
        }
        
        if ($this->cerca_limite) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'El centro de costo está cerca del límite presupuestal',
                'porcentaje' => $this->porcentaje_ejecucion,
            ];
        }
        
        if (!$this->activo) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => 'El centro de costo está inactivo',
            ];
        }
        
        return $alertas;
    }
}