<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder; // Importar Builder

class Saldo extends Model
{
    protected $table = 'Saldos';
    public $timestamps = false;


    protected $primaryKey = 'codpro';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('codpro', '=', $this->getAttribute('codpro'))
            ->where('almacen', '=', $this->getAttribute('almacen'))
            ->where('lote', '=', $this->getAttribute('lote'));
            
        return $query;
    }



    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codpro', 'CodPro');
    }
}