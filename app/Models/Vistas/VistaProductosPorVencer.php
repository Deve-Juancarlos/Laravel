<?php

namespace App\Models\Vistas;

use Illuminate\Database\Eloquent\Model;



class VistaProductosPorVencer extends Model
{
    protected $table = 'v_productos_por_vencer';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $casts = [
        'Vencimiento' => 'datetime',
    ];
}