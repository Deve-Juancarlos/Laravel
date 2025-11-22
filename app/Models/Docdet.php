<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $Numero
 * @property int $Tipo
 * @property string $Codpro
 * @property string $Lote
 * @property string $Vencimiento
 * @property int $Unimed
 * @property string $Cantidad
 * @property string|null $Adicional
 * @property string $Precio
 * @property string|null $Unidades
 * @property int|null $Almacen
 * @property string|null $Descuento1
 * @property string|null $Descuento2
 * @property string|null $Descuento3
 * @property string $Subtotal
 * @property string|null $Costo
 * @property string|null $stock
 * @property string|null $Codprom
 * @property string|null $Des_cab
 * @property string|null $CodOferta
 * @property string|null $CodAutoriza
 * @property int $Nbonif
 * @property-read \App\Models\Doccab $doccab
 * @property-read \App\Models\Producto|null $producto
 * @method static Builder<static>|Docdet newModelQuery()
 * @method static Builder<static>|Docdet newQuery()
 * @method static Builder<static>|Docdet query()
 * @method static Builder<static>|Docdet whereAdicional($value)
 * @method static Builder<static>|Docdet whereAlmacen($value)
 * @method static Builder<static>|Docdet whereCantidad($value)
 * @method static Builder<static>|Docdet whereCodAutoriza($value)
 * @method static Builder<static>|Docdet whereCodOferta($value)
 * @method static Builder<static>|Docdet whereCodpro($value)
 * @method static Builder<static>|Docdet whereCodprom($value)
 * @method static Builder<static>|Docdet whereCosto($value)
 * @method static Builder<static>|Docdet whereDesCab($value)
 * @method static Builder<static>|Docdet whereDescuento1($value)
 * @method static Builder<static>|Docdet whereDescuento2($value)
 * @method static Builder<static>|Docdet whereDescuento3($value)
 * @method static Builder<static>|Docdet whereLote($value)
 * @method static Builder<static>|Docdet whereNbonif($value)
 * @method static Builder<static>|Docdet whereNumero($value)
 * @method static Builder<static>|Docdet wherePrecio($value)
 * @method static Builder<static>|Docdet whereStock($value)
 * @method static Builder<static>|Docdet whereSubtotal($value)
 * @method static Builder<static>|Docdet whereTipo($value)
 * @method static Builder<static>|Docdet whereUnidades($value)
 * @method static Builder<static>|Docdet whereUnimed($value)
 * @method static Builder<static>|Docdet whereVencimiento($value)
 * @mixin \Eloquent
 */
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