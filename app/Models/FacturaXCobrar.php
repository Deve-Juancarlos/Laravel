<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaXCobrar extends Model
{
    protected $table = 't_FacturasXCobrar';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Fecha',
        'tipo',
        'Documento',
        'Ruc',
        'Razon',
        'Importe',
        'Saldo'
    ];
}