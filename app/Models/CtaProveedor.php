<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para SIFANO.CtaProveedor (Cuentas por Pagar)
 */
class CtaProveedor extends Model
{
    protected $table = 'CtaProveedor';
   
    protected $primaryKey = ['Documento', 'Tipo', 'CodProv'];
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'FechaF' => 'datetime',
        'FechaV' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        foreach($keys as $key){
            $query->where($key, '=', $this->getAttribute($key));
        }
        return $query;
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'CodProv', 'CodProv');
    }
}