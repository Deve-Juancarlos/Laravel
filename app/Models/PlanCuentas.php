<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PlanCuentas extends Model
{
    use HasFactory;

    protected $table = 'plan_cuentas';

    protected $primaryKey = 'codigo';

  
    public $incrementing = false;


    protected $keyType = 'string';

   
    public $timestamps = false;

    
    public function detallesDiario(): HasMany
    {
        return $this->hasMany(LibroDiarioDetalle::class, 'cuenta_contable', 'codigo');
    }
}