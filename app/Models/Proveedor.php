<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Proveedor extends Model
{
    protected $table = 'Proveedores';
    protected $primaryKey = 'CodProv'; 
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

   
    public function cuentasPorPagar()
    {
        return $this->hasMany(CtaProveedor::class, 'CodProv', 'CodProv');
    }
}