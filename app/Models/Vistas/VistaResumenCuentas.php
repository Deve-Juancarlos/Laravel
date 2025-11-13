<?php

namespace App\Models\Vistas;

use Illuminate\Database\Eloquent\Model;


class VistaResumenCuentas extends Model
{
    protected $table = 'v_resumen_cuentas';
    public $timestamps = false;
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}