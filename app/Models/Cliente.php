<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'clientes';

    // Nombre de la clave primaria
    protected $primaryKey = 'idcliente';

    // Si la PK no es auto-incremental, pon false
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Si la tabla no tiene created_at / updated_at
    public $timestamps = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'email',
        'telefono',
    ];
}
