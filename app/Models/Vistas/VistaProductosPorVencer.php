<?php
namespace App\Models\Vistas;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $CodPro
 * @property string $Nombre
 * @property string|null $Laboratorio
 * @property int $Almacen
 * @property string $Lote
 * @property string|null $Vencimiento
 * @property string|null $Stock
 * @property int|null $DiasParaVencer
 * @property string $EstadoVencimiento
 * @property string|null $ValorInventario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereAlmacen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereCodPro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereDiasParaVencer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereEstadoVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereLaboratorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereValorInventario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereVencimiento($value)
 * @mixin \Eloquent
 */
class VistaProductosPorVencer extends Model
{
    protected $table = 'v_productos_por_vencer';
    public $timestamps = false;
    protected $primaryKey = null;
}