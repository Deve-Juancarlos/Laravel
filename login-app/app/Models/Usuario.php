<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'usuario',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function accesoWeb()
    {
        return $this->hasOne(AccesoWeb::class, 'usuario_id');
    }
}