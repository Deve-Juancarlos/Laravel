<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AccesoWeb;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

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
        'metadata'
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_en' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    public function marcarComoLeida()
    {
        $this->update([
            'leida' => true,
            'leida_en' => now()
        ]);
    }

    public function marcarComoNoLeida()
    {
        $this->update([
            'leida' => false,
            'leida_en' => null
        ]);
    }
}