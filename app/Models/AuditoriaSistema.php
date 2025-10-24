<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaSistema extends Model
{
    use HasFactory;

    protected $table = 'Auditoria_Sistema';
    
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'usuario',
        'accion',
        'tabla',
        'detalle',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Scope por acción
     */
    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope por tabla
     */
    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla', $tabla);
    }

    /**
     * Scope por usuario
     */
    public function scopePorUsuario($query, $usuario)
    {
        return $query->where('usuario', $usuario);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha', [$inicio, $fin]);
    }
}