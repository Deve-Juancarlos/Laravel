<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class LibroDiario extends Model
{
    use HasFactory;

    protected $table = 'libro_diario';

    protected $primaryKey = 'id'; 

    public $timestamps = true; 

    
    protected $fillable = [
        'numero',
        'fecha',
        'glosa',
        'total_debe',
        'total_haber',
        'balanceado',
        'estado',
        'usuario_id',
        'observaciones',
    ];

    
    protected $casts = [
        'fecha' => 'date',
        'total_debe' => 'decimal:2',
        'total_haber' => 'decimal:2',
        'balanceado' => 'boolean',
    ];

    
    public function detalles(): HasMany
    {
       
        return $this->hasMany(LibroDiarioDetalle::class, 'asiento_id', 'id');
    }

    
    public function usuario(): BelongsTo
    {
       
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }


    public function isActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    public function isPendienteEliminacion(): bool
    {
        return $this->estado === 'PENDIENTE_ELIMINACION';
    }

  
}