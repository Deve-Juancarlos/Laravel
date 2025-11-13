<?php

namespace App\Models\Vistas;

use Illuminate\Database\Eloquent\Model;

class VistaAgingCartera extends Model
{
    protected $table = 'v_aging_cartera';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $casts = [
        'FechaF' => 'datetime',
        'FechaV' => 'datetime',
    ];
}