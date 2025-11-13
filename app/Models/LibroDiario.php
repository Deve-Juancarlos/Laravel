<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibroDiario extends Model
{
    protected $table = 'libro_diario';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'numero', 'fecha', 'glosa', 'total_debe', 'total_haber',
        'balanceado', 'estado', 'usuario_id', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'balanceado' => 'boolean',
        'total_debe' => 'decimal:2',
        'total_haber' => 'decimal:2',
    ];

    // Relación: Un asiento tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(LibroDiarioDetalle::class, 'asiento_id', 'id');
    }
}