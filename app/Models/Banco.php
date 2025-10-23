<?php

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
     
    protected $table = 'Bancos';

    
    protected $primaryKey = 'Cuenta';

   
    public $incrementing = false;

    protected $keyType = 'string';

    
    protected $fillable = [
        'Cuenta',
        'Moneda',
        'Banco',
    ];

    protected $casts = [
        'Moneda' => 'integer',
    ];

    
    public function getNombreBancoAttribute()
    {
        return trim($this->Banco);
    }

    public function getMonedaTextoAttribute()
    {
        return $this->Moneda == 1 ? 'Soles (S/)' : 'Dólares (US$)';
    }

    public function scopeEnSoles($query)
    {
        return $query->where('Moneda', 1);
    }

    public function scopeEnDolares($query)
    {
        return $query->where('Moneda', 2);
    }
}