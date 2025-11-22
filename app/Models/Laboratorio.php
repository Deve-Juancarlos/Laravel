<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $CodLab
 * @property string $Descripcion
 * @property bool|null $Mantiene
 * @property bool|null $Importado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Producto> $productos
 * @property-read int|null $productos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereCodLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereImportado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereMantiene($value)
 * @mixin \Eloquent
 */
class Laboratorio extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'Laboratorios';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'CodLab';

    /**
     * Indica si el modelo usa claves autoincrementales.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indica si el modelo usa timestamps de Eloquent.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'CodLab',
        'Descripcion',
        'Mantiene',
        'Importado',
    ];

    /**
     * Los atributos que deben convertirse a booleanos.
     *
     * @var array
     */
    protected $casts = [
        'Mantiene' => 'boolean',
        'Importado' => 'boolean',
    ];

    /**
     * Relación: Un laboratorio puede tener muchos productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'CodProv', 'CodLab');
    }
}