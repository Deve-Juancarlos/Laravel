<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class AccesoWeb extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'accesoweb';
    protected $primaryKey = 'idusuario';
    protected $keyType = 'int';
    
   
    public $timestamps = false;

    protected $fillable = [
        'idusuario',
        'usuario',
        'tipousuario',
        'password',
        'rol',
        'estado',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

  
    public function asientosDiario(): HasMany
    {
        
        return $this->hasMany(LibroDiario::class, 'usuario_id', 'idusuario');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id', 'idusuario')
                    ->orderBy('created_at', 'desc');
    }
     public function empleado()
    {
        return $this->belongsTo(\App\Models\Empleado::class, 'idusuario', 'Codemp');
    }
}