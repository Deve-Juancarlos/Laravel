<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

/**
 * @property string|null $usuario
 * @property string|null $tipousuario
 * @property int|null $idusuario
 * @property string|null $password
 * @property string|null $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property int $id
 * @property string $estado
 * @property string|null $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LibroDiario> $asientosDiario
 * @property-read int|null $asientos_diario_count
 * @property-read \App\Models\Empleado|null $empleado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notificacion> $notificaciones
 * @property-read int|null $notificaciones_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereIdusuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereTipousuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereUsuario($value)
 * @mixin \Eloquent
 */
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