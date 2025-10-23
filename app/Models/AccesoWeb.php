<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AccesoWeb extends Authenticatable
{
    use Notifiable;

    protected $table = 'accesoweb';
    protected $primaryKey = 'idusuario';

    protected $keyType = 'int';

    protected $fillable = [
        'idusuario',
        'usuario',
        'tipousuario',
        'password',
        'rol', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
