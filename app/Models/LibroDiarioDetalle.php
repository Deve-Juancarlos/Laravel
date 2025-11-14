<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibroDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'libro_diario_detalles';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'asiento_id', 
        'cuenta_contable',
        'debe', 
        'haber', 
        'concepto',
        'documento_referencia'
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

  
    public function asiento(): BelongsTo
    {
        return $this->belongsTo(LibroDiario::class, 'asiento_id', 'id');
    }

  
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(PlanCuentas::class, 'cuenta_contable', 'codigo');
    }
}