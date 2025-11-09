<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'libro_diario_detalles';

    
    public $timestamps = true;

    
    protected $fillable = [
        'asiento_id',
        'cuenta_contable',
        'debe',
        'haber',
        'concepto',
        'documento_referencia',
    ];

  
    public function libroDiario()
    {
        return $this->belongsTo(LibroDiario::class, 'asiento_id');
    }
}