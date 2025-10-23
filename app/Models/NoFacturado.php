<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoFacturado extends Model
{
    protected $table = 'NoFacturado';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codpro',
        'nombre',
        'Pedido',
        'stock'
    ];
}