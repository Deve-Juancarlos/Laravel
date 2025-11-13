<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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