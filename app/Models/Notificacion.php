<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $usuario_id
 * @property string $tipo
 * @property string $titulo
 * @property string $mensaje
 * @property string|null $icono
 * @property string $color
 * @property string|null $url
 * @property bool $leida
 * @property \Illuminate\Support\Carbon|null $leida_en
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AccesoWeb|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereIcono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereLeida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereLeidaEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereMensaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUsuarioId($value)
 * @mixin \Eloquent
 */
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