<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';
    protected $primaryKey = 'id';

    
    public $timestamps = true;

    protected $fillable = [
        'usuario_id', 
        'tipo',
        'titulo',
        'mensaje',
        'icono',
        'color',
        'url',
        'leida',
        'leida_en',
        'metadata',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_en' => 'datetime',
        'metadata' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }
}