<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'PlanC_cobranza'; // Usando planillas como base para proyectos
    
    protected $primaryKey = ['Serie', 'Numero'];
    public $incrementing = false;

    protected $fillable = [
        'Serie',
        'Numero',
        'Vendedor',
        'FechaCrea',
        'FechaIng',
        'Confirmacion',
        'Impreso',
        'estado',
        'descripcion',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
        'presupuesto',
        'avance_porcentaje',
        'notas',
        'usuario_id',
    ];

    protected $casts = [
        'FechaCrea' => 'datetime',
        'FechaIng' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'presupuesto' => 'decimal:2',
        'avance_porcentaje' => 'decimal:2',
        'Confirmacion' => 'boolean',
        'Impreso' => 'boolean',
        'estado' => 'integer',
    ];

    /**
     * Relación con Empleado (Vendedor/Responsable)
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'Vendedor', 'Codemp');
    }

    /**
     * Relación con Usuario (AccesoWeb)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    /**
     * Relación con detalles (PlanD_cobranza como tareas del proyecto)
     */
    public function tareas(): HasMany
    {
        return $this->hasMany(TareaProyecto::class, 'serie', 'Serie')
                   ->where('numero', '=', $this->Numero);
    }

    /**
     * Scope por estado
     */
    public function scopePorEstado($query, $estado)
    {
        $estados = [
            'planificacion' => 1,
            'en_progreso' => 2,
            'completado' => 3,
            'suspendido' => 4,
            'cancelado' => 5,
        ];

        return $query->where('estado', $estados[$estado] ?? $estado);
    }

    /**
     * Scope por vendedor
     */
    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('Vendedor', $vendedorId);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('FechaCrea', [$inicio, $fin]);
    }

    /**
     * Scope para proyectos activos
     */
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]); // Planificación y En Progreso
    }

    /**
     * Scope para proyectos completados
     */
    public function scopeCompletados($query)
    {
        return $query->porEstado('completado');
    }

    /**
     * Scope para proyectos atrasados
     */
    public function scopeAtrasados($query)
    {
        return $query->where('fecha_fin', '<', now())
                   ->where('estado', '!=', 3); // No completados
    }

    /**
     * Obtener nombre del estado
     */
    public function getEstadoNombreAttribute(): string
    {
        $estados = [
            1 => 'Planificación',
            2 => 'En Progreso',
            3 => 'Completado',
            4 => 'Suspendido',
            5 => 'Cancelado',
        ];

        return $estados[$this->estado] ?? 'Desconocido';
    }

    /**
     * Obtener código completo del proyecto
     */
    public function getCodigoCompletoAttribute(): string
    {
        return "{$this->Serie}-{$this->Numero}";
    }

    /**
     * Obtener duración en días
     */
    public function getDuracionDiasAttribute(): ?int
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            return $this->fecha_inicio->diffInDays($this->fecha_fin);
        }
        return null;
    }

    /**
     * Obtener días transcurridos
     */
    public function getDiasTranscurridosAttribute(): int
    {
        if (!$this->fecha_inicio) return 0;
        
        return $this->fecha_inicio->diffInDays(now());
    }

    /**
     * Obtener días restantes
     */
    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_fin) return null;
        
        return $this->fecha_fin->diffInDays(now(), false);
    }

    /**
     * Verificar si está atrasado
     */
    public function getAtrasadoAttribute(): bool
    {
        return $this->fecha_fin && $this->fecha_fin->isPast() && $this->estado != 3;
    }

    /**
     * Verificar si está en progreso
     */
    public function getEnProgresoAttribute(): bool
    {
        return $this->estado == 2;
    }

    /**
     * Obtener progreso real basado en tareas
     */
    public function getProgresoRealAttribute(): float
    {
        $tareas = $this->tareas;
        
        if ($tareas->isEmpty()) {
            return $this->avance_porcentaje ?? 0;
        }

        $tareasCompletadas = $tareas->where('completada', true)->count();
        $porcentajeTareas = ($tareasCompletadas / $tareas->count()) * 100;
        
        // Promedio entre avance manual y avance por tareas
        $avanceManual = $this->avance_porcentaje ?? 0;
        
        return round(($avanceManual + $porcentajeTareas) / 2, 2);
    }

    /**
     * Obtener descripción completa
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $descripcion = "Proyecto {$this->codigo_completo}";
        
        if ($this->descripcion) {
            $descripcion .= ": {$this->descripcion}";
        }
        
        $descripcion .= " - Estado: {$this->estado_nombre}";
        
        if ($this->responsable) {
            $descripcion .= " - Responsable: {$this->responsable->Nombre}";
        }
        
        return $descripcion;
    }

    /**
     * Obtener avance con información adicional
     */
    public function getAvanceDetalladoAttribute(): array
    {
        return [
            'progreso_manual' => $this->avance_porcentaje ?? 0,
            'progreso_tareas' => $this->progreso_real,
            'tareas_total' => $this->tareas->count(),
            'tareas_completadas' => $this->tareas->where('completada', true)->count(),
            'tareas_pendientes' => $this->tareas->where('completada', false)->count(),
        ];
    }

    /**
     * Marcar como completado
     */
    public function marcarCompletado()
    {
        $this->update([
            'estado' => 3,
            'avance_porcentaje' => 100,
        ]);
    }

    /**
     * Suspender proyecto
     */
    public function suspender($motivo = '')
    {
        $this->update([
            'estado' => 4,
            'notas' => ($this->notas ? $this->notas . "\n" : '') . "Suspendido: {$motivo}",
        ]);
    }

    /**
     * Obtener estadísticas del proyecto
     */
    public function getEstadisticasAttribute(): array
    {
        $tareas = $this->tareas;
        
        return [
            'total_tareas' => $tareas->count(),
            'tareas_completadas' => $tareas->where('completada', true)->count(),
            'tareas_pendientes' => $tareas->where('completada', false)->count(),
            'presupuesto_total' => $this->presupuesto ?? 0,
            'avance_porcentaje' => $this->avance_porcentaje ?? 0,
            'duracion_dias' => $this->duracion_dias,
            'dias_transcurridos' => $this->dias_transcurridos,
            'dias_restantes' => $this->dias_restantes,
            'estado' => $this->estado_nombre,
            'responsable' => $this->responsable ? $this->responsable->Nombre : 'No asignado',
        ];
    }
}