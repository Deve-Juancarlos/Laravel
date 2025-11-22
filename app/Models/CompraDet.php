<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Id
 * @property int $CompraId
 * @property string $CodPro
 * @property string $Cantidad
 * @property string $CostoUnitario
 * @property string $Subtotal
 * @property string|null $Lote
 * @property string|null $Vencimiento
 * @property-read \App\Models\CompraCab $cabecera
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCodPro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCompraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCostoUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereVencimiento($value)
 * @mixin \Eloquent
 */
class CompraDet extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CompraDet';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    
    protected $fillable = [
        'CompraId', 'CodPro', 'Cantidad',
        'CostoUnitario', 'Subtotal', 'Lote', 'Vencimiento'
    ];

    public function cabecera()
    {
        return $this->belongsTo(CompraCab::class, 'CompraId');
    }
}
