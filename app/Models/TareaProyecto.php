<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TareaProyecto extends Model
{
    use HasFactory;

    protected $table = 'PlanD_cobranza'; // Usando planD_cobranza como base para tareas
    
    protected $primaryKey = 'Orden';
    public $incrementing = true;

    protected $fillable = [
        'Orden',
        'Serie',
        'Numero',
        'CodClie',
        'Documento',
        'TipoDoc',
        'FechaFac',
        'Valor',
        'NroRecibo',
        'NotaCred',
        'Descuento',
        'Efectivo',
        'Cheque',
        'NroCheque',
        'Banco',
        'Moneda',
        'Cambio',
        // Campos adicionales para proyecto
        'titulo',
        'descripcion',
        'asignado_a',
        'fecha_inicio',
        'fecha_fin',
        'prioridad',
        'completada',
        'fecha_completado',
        'horas_estimadas',
        'horas_reales',
        'usuario_id',
        'estado',
        'notas',
    ];

    protected $casts = [
        'FechaFac' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_completado' => 'datetime',
        'Valor' => 'decimal:2',
        'Descuento' => 'decimal:2',
        'Efectivo' => 'decimal:2',
        'Cheque' => 'decimal:2',
        'Cambio' => 'decimal:4',
        'horas_estimadas' => 'decimal:2',
        'horas_reales' => 'decimal:2',
        'completada' => 'boolean',
        'prioridad' => 'integer',
        'estado' => 'integer',
    ];

    /**
     * Relación con Proyecto
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'Serie', 'Serie')
                   ->where('Numero', '=', $this->Numero);
    }

    /**
     * Relación con Empleado (Asignado a)
     */
    public function asignado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'asignado_a', 'Codemp');
    }

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    /**
     * Relación con Cliente (si aplica)
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    /**
     * Scope por proyecto
     */
    public function scopePorProyecto($query, $serie, $numero)
    {
        return $query->where('Serie', $serie)->where('Numero', $numero);
    }

    /**
     * Scope por empleado asignado
     */
    public function scopeAsignadoA($query, $empleadoId)
    {
        return $query->where('asignado_a', $empleadoId);
    }

    /**
     * Scope por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope por prioridad
     */
    public function scopePorPrioridad($query, $prioridad)
    {
        $prioridades = [
            'baja' => 1,
            'media' => 2,
            'alta' => 3,
            'critica' => 4,
        ];

        return $query->where('prioridad', $prioridades[$prioridad] ?? $prioridad);
    }

    /**
     * Scope para tareas completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('completada', true);
    }

    /**
     * Scope para tareas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('completada', false);
    }

    /**
     * Scope por fecha
     */
    public function scopeEnFecha($query, $fecha)
    {
        return $query->whereDate('fecha_inicio', '<=', $fecha)
                   ->whereDate('fecha_fin', '>=', $fecha);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_fin', '<', now())
                   ->where('completada', false);
    }

    /**
     * Obtener nombre del estado
     */
    public function getEstadoNombreAttribute(): string
    {
        $estados = [
            1 => 'Pendiente',
            2 => 'En Progreso',
            3 => 'En Revisión',
            4 => 'Completada',
            5 => 'Cancelada',
        ];

        return $estados[$this->estado] ?? 'Desconocido';
    }

    /**
     * Obtener nombre de la prioridad
     */
    public function getPrioridadNombreAttribute(): string
    {
        $prioridades = [
            1 => 'Baja',
            2 => 'Media',
            3 => 'Alta',
            4 => 'Crítica',
        ];

        return $prioridades[$this->prioridad] ?? 'Media';
    }

    /**
     * Obtener progreso de la tarea
     */
    public function getProgresoAttribute(): float
    {
        if ($this->completada) return 100;
        
        if ($this->horas_estimadas > 0 && $this->horas_reales > 0) {
            return min(100, ($this->horas_reales / $this->horas_estimadas) * 100);
        }
        
        return $this->estado == 2 ? 50 : 0; // 50% si está en progreso
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
     * Verificar si está vencida
     */
    public function getVencidaAttribute(): bool
    {
        return $this->fecha_fin && $this->fecha_fin->isPast() && !$this->completada;
    }

    /**
     * Verificar si está próximo a vencer (3 días)
     */
    public function getProximoVencerAttribute(): bool
    {
        return $this->dias_restantes !== null && 
               $this->dias_restantes <= 3 && 
               $this->dias_restantes >= 0 &&
               !$this->completada;
    }

    /**
     * Obtener horas de diferencia
     */
    public function getHorasDiferenciaAttribute(): ?float
    {
        if ($this->horas_estimadas && $this->horas_reales) {
            return $this->horas_reales - $this->horas_estimadas;
        }
        return null;
    }

    /**
     * Marcar como completada
     */
    public function marcarCompletada($horasReales = null)
    {
        $this->update([
            'completada' => true,
            'estado' => 4,
            'fecha_completado' => now(),
            'horas_reales' => $horasReales ?: $this->horas_reales,
        ]);
    }

    /**
     * Obtener descripción completa
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $descripcion = "Tarea {$this->titulo}";
        
        if ($this->descripcion) {
            $descripcion .= ": {$this->descripcion}";
        }
        
        $descripcion .= " - Prioridad: {$this->prioridad_nombre}";
        $descripcion .= " - Estado: {$this->estado_nombre}";
        
        if ($this->asignado) {
            $descripcion .= " - Asignado a: {$this->asignado->Nombre}";
        }
        
        return $descripcion;
    }

    /**
     * Obtener resumen de la tarea
     */
    public function getResumenAttribute(): array
    {
        return [
            'id' => $this->Orden,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado_nombre,
            'prioridad' => $this->prioridad_nombre,
            'progreso' => $this->progreso,
            'proyecto' => $this->proyecto ? $this->proyecto->codigo_completo : null,
            'asignado' => $this->asignado ? $this->asignado->Nombre : 'Sin asignar',
            'fecha_inicio' => $this->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $this->fecha_fin?->format('Y-m-d'),
            'dias_restantes' => $this->dias_restantes,
            'vencida' => $this->vencida,
            'proximo_vencer' => $this->proximo_vencer,
            'completada' => $this->completada,
            'horas_estimadas' => $this->horas_estimadas,
            'horas_reales' => $this->horas_reales,
            'horas_diferencia' => $this->horas_diferencia,
        ];
    }
}