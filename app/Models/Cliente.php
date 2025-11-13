<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}