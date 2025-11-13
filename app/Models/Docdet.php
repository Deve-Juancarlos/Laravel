<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Docdet extends Model
{
    protected $table = 'Docdet';

    
    protected $primaryKey = 'Numero'; 
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'Numero', 'Tipo', 'Codpro', 'Lote', 'Nbonif', 'Vencimiento', 'Unimed',
        'Cantidad', 'Adicional', 'Precio', 'Unidades', 'Almacen', 'Descuento1',
        'Descuento2', 'Descuento3', 'Subtotal', 'Costo'
    ];

   
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('Numero', '=', $this->getAttribute('Numero'))
            ->where('Tipo', '=', $this->getAttribute('Tipo'))
            ->where('Codpro', '=', $this->getAttribute('Codpro'))
            ->where('Lote', '=', $this->getAttribute('Lote'))
            ->where('Nbonif', '=', $this->getAttribute('Nbonif'));
        return $query;
    }


    public function doccab(): BelongsTo
    {
        return $this->belongsTo(Doccab::class, 'Numero', 'Numero')
                    ->whereColumn('Doccab.Tipo', 'Docdet.Tipo');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'Codpro', 'CodPro');
    }
}