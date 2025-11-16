<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'Empleados';
    protected $primaryKey = 'Codemp';
    public $timestamps = false;

    protected $fillable = [
        'Codemp',
        'Nombre',
        'Direccion',
        'Documento',
        'Telefono1',
        'Telefono2',
        'Celular',
        'Nextel',
        'Cumpleaños',
        'Tipo'
    ];

    // Relación con accesoweb (un empleado puede tener un usuario web)
    public function usuarioWeb()
    {
        return $this->hasOne(\App\Models\AccesoWeb::class, 'idusuario', 'Codemp');
    }
}