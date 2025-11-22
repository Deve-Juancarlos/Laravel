<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $Id
 * @property int $CodClie
 * @property string $NumeroConstancia
 * @property string $FechaPago
 * @property string $Monto
 * @property string $DoccabNumero
 * @property int $DoccabTipo
 * @property int|null $UsuarioId
 * @property string|null $created_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Doccab $documento
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereDoccabNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereDoccabTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereFechaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereNumeroConstancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereUsuarioId($value)
 * @mixin \Eloquent
 */
class ConstanciaDetraccion extends Model
{
    protected $table = 'ConstanciaDetraccion';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    
    // No olvides añadir los campos al $fillable
    protected $fillable = [
        'CodClie', 'NumeroConstancia', 'FechaPago', 'Monto',
        'DoccabNumero', 'DoccabTipo', 'UsuarioId'
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Doccab::class, 'DoccabNumero', 'Numero')
                    ->whereColumn('Doccab.Tipo', 'ConstanciaDetraccion.DoccabTipo');
    }
}