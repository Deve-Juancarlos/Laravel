<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Doccab;
use App\Models\NotaCredito;

class Cliente extends Model
{
    protected $table = 'Clientes';
    protected $primaryKey = 'Codclie';
    public $incrementing = true;
    public $timestamps = false;

    protected $casts = [
        'Activo' => 'boolean',
    ];

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CtaCliente::class, 'CodClie', 'Codclie');
    }

     public function facturas(): HasMany
    {
        return $this->hasMany(Doccab::class, 'Codclie', 'Codclie');
    }

    
    public function notasCredito(): HasMany
    {
        return $this->hasMany(NotaCredito::class, 'Codclie', 'Codclie');
    }
}