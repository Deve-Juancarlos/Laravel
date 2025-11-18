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

    
    public function usuarioWeb()
    {
        return $this->hasOne(\App\Models\AccesoWeb::class, 'idusuario', 'Codemp');
    }

    public function getTelefonoFormateadoAttribute()
    {
        $tel = $this->Telefono1; 

    
        if (!$tel || strlen($tel) !== 9) {
                return $tel;
            }

        
            return '+51 ' . substr($tel, 0, 3) . ' ' . substr($tel, 3, 3) . ' ' . substr($tel, 6, 3);
    }
}