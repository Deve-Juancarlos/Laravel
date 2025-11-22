<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Models\Docdet; 

/**
 * @property string $Numero
 * @property int $Tipo
 * @property int|null $CodClie
 * @property string $Fecha
 * @property int|null $Dias
 * @property string|null $FechaV
 * @property string|null $Bruto
 * @property string|null $Descuento
 * @property string|null $Flete
 * @property string $Subtotal
 * @property string|null $Igv
 * @property string $Total
 * @property int $Moneda
 * @property string|null $Cambio
 * @property int|null $Vendedor
 * @property string|null $Transporte
 * @property int $Eliminado
 * @property int $Impreso
 * @property string|null $NroPedido
 * @property string|null $NroGuia
 * @property string|null $Usuario
 * @property string|null $estado_sunat
 * @property string|null $hash_cdr
 * @property string|null $mensaje_sunat
 * @property string|null $nombre_archivo
 * @property string|null $qr_data
 * @property string|null $MontoDetraccion
 * @property int|null $asiento_id
 * @property string|null $serie_sunat
 * @property int|null $correlativo_sunat
 * @property string $tipo_documento_sunat
 * @property-read \App\Models\Cliente|null $cliente
 * @property-read \App\Models\CtaCliente|null $cuentaPorCobrar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Docdet> $detalles
 * @property-read int|null $detalles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCorrelativoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereEliminado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereEstadoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFlete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereHashCdr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereImpreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMensajeSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMoneda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMontoDetraccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNombreArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNroGuia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNroPedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereQrData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereSerieSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTipoDocumentoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTransporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereVendedor($value)
 * @mixin \Eloquent
 */
class Doccab extends Model
{
    protected $table = 'Doccab';
   
    protected $primaryKey = 'Numero'; 
    public $incrementing = false;
    protected $keyType = 'string'; 
    public $timestamps = false;

    protected $fillable = [
        'Numero', 'Tipo', 'CodClie', 'Fecha', 'Total', 'Eliminado', 'estado_sunat',
        'serie_sunat', 'correlativo_sunat', 'tipo_documento_sunat' 
    ];

   
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('Numero', '=', $this->getAttribute('Numero'))
            ->where('Tipo', '=', $this->getAttribute('Tipo'));
        return $query;
    }



    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CtaCliente::class, 'Documento', 'Numero')
                    ->whereColumn('CtaCliente.Tipo', 'Doccab.Tipo');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(Docdet::class, 'Numero', 'Numero')
                    ->whereColumn('Docdet.Tipo', 'Doccab.Tipo');
    }

   
}