<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroDiario extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'libro_diario';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * Tu esquema SÍ los tiene.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'numero',
        'fecha',
        'glosa',
        'total_debe',
        'total_haber',
        'balanceado',
        'estado',
        'usuario_id',
        'observaciones',
    ];
}