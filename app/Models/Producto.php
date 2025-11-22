<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property string $CodPro
 * @property string|null $CodBar
 * @property int $Clinea
 * @property int|null $Clase
 * @property string $Nombre
 * @property string|null $CodProv
 * @property string|null $Peso
 * @property string|null $Minimo
 * @property string $Stock
 * @property int $Afecto
 * @property int $Tipo
 * @property string $Costo
 * @property string $PventaMa
 * @property string $PventaMi
 * @property string|null $ComisionH
 * @property string|null $ComisionV
 * @property string|null $ComisionR
 * @property bool $Eliminado
 * @property int $AfecFle
 * @property string|null $CosReal
 * @property string|null $RegSanit
 * @property int|null $TemMax
 * @property int|null $TemMin
 * @property string|null $FecSant
 * @property string|null $Coddigemin
 * @property string|null $CodLab
 * @property string|null $Codlab1
 * @property string|null $Principio
 * @property bool $SujetoADetraccion
 * @property-read \App\Models\Laboratorio|null $laboratorio
 * @method static Builder<static>|Producto newModelQuery()
 * @method static Builder<static>|Producto newQuery()
 * @method static Builder<static>|Producto query()
 * @method static Builder<static>|Producto stockBajo()
 * @method static Builder<static>|Producto whereAfecFle($value)
 * @method static Builder<static>|Producto whereAfecto($value)
 * @method static Builder<static>|Producto whereClase($value)
 * @method static Builder<static>|Producto whereClinea($value)
 * @method static Builder<static>|Producto whereCodBar($value)
 * @method static Builder<static>|Producto whereCodLab($value)
 * @method static Builder<static>|Producto whereCodPro($value)
 * @method static Builder<static>|Producto whereCodProv($value)
 * @method static Builder<static>|Producto whereCoddigemin($value)
 * @method static Builder<static>|Producto whereCodlab1($value)
 * @method static Builder<static>|Producto whereComisionH($value)
 * @method static Builder<static>|Producto whereComisionR($value)
 * @method static Builder<static>|Producto whereComisionV($value)
 * @method static Builder<static>|Producto whereCosReal($value)
 * @method static Builder<static>|Producto whereCosto($value)
 * @method static Builder<static>|Producto whereEliminado($value)
 * @method static Builder<static>|Producto whereFecSant($value)
 * @method static Builder<static>|Producto whereMinimo($value)
 * @method static Builder<static>|Producto whereNombre($value)
 * @method static Builder<static>|Producto wherePeso($value)
 * @method static Builder<static>|Producto wherePrincipio($value)
 * @method static Builder<static>|Producto wherePventaMa($value)
 * @method static Builder<static>|Producto wherePventaMi($value)
 * @method static Builder<static>|Producto whereRegSanit($value)
 * @method static Builder<static>|Producto whereStock($value)
 * @method static Builder<static>|Producto whereSujetoADetraccion($value)
 * @method static Builder<static>|Producto whereTemMax($value)
 * @method static Builder<static>|Producto whereTemMin($value)
 * @method static Builder<static>|Producto whereTipo($value)
 * @mixin \Eloquent
 */
class Producto extends Model
{
    protected $table = 'Productos';
    protected $primaryKey = 'CodPro';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'Eliminado' => 'boolean',
        'SujetoADetraccion' => 'boolean',
    ];

    public function scopeStockBajo(Builder $query)
    {
        return $query->where('Stock', '<', DB::raw('ISNULL(NULLIF(Minimo, 0), 1)'))
                     ->where('Eliminado', false);
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'CodProv', 'CodLab');
    }
}