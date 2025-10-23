<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesoWeb extends Model
{
    use HasFactory;

    protected $table = 'acceso_web';

    protected $fillable = [
        'usuario_id',
        'rol',
        'permisos',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}