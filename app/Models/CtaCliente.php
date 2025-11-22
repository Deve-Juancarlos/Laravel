<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $Documento
 * @property int $Tipo
 * @property int $CodClie
 * @property string $FechaF
 * @property string|null $FechaV
 * @property string $Importe
 * @property string $Saldo
 * @property int $NroDeuda
 * @property int|null $cliente_id
 * @property string|null $FechaP
 * @property string|null $Utilidades
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Doccab|null $doccab
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaF($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaP($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereImporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereNroDeuda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereUtilidades($value)
 * @mixin \Eloquent
 */
class CtaCliente extends Model
{
    protected $table = 'CtaCliente';


    protected $primaryKey = 'Documento';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['Documento', 'Tipo', 'CodClie', 'FechaF', 'FechaV', 'Importe', 'Saldo'];

  
   
   protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('Documento', '=', $this->getAttribute('Documento'))
            ->where('Tipo', '=', $this->getAttribute('Tipo'));
        return $query;
    }




    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

 
    public function doccab(): BelongsTo
    {
        return $this->belongsTo(Doccab::class, 'Documento', 'Numero')
                    ->whereColumn('Doccab.Tipo', 'CtaCliente.Tipo');
    }

}