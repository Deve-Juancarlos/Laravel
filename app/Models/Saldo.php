<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder; // Importar Builder

/**
 * @property string $codpro
 * @property int $almacen
 * @property string $lote
 * @property string|null $vencimiento
 * @property string|null $saldo
 * @property int $protocolo
 * @property-read \App\Models\Producto $producto
 * @method static Builder<static>|Saldo newModelQuery()
 * @method static Builder<static>|Saldo newQuery()
 * @method static Builder<static>|Saldo query()
 * @method static Builder<static>|Saldo whereAlmacen($value)
 * @method static Builder<static>|Saldo whereCodpro($value)
 * @method static Builder<static>|Saldo whereLote($value)
 * @method static Builder<static>|Saldo whereProtocolo($value)
 * @method static Builder<static>|Saldo whereSaldo($value)
 * @method static Builder<static>|Saldo whereVencimiento($value)
 * @mixin \Eloquent
 */
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