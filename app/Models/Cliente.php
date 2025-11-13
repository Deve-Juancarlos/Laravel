<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Cliente extends Model
{
    protected $table = 'Clientes';
    protected $primaryKey = 'Codclie'; 
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $casts = [
        'Fecha' => 'datetime', 
    ];

    
    public function cuentasPorCobrar()
    {
        return $this->hasMany(CtaCliente::class, 'CodClie', 'Codclie');
    }

   
    public function facturas()
    {
        return $this->hasMany(Doccab::class, 'CodClie', 'Codclie');
    }
}