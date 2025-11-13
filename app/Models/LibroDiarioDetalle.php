<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibroDiarioDetalle extends Model
{
    protected $table = 'libro_diario_detalles';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'asiento_id', 'cuenta_contable', 'debe', 'haber', 'concepto',
        'documento_referencia'
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public function asiento()
    {
        return $this->belongsTo(LibroDiario::class, 'asiento_id', 'id');
    }
}