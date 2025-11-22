<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Id
 * @property string $Serie
 * @property string $Numero
 * @property string $TipoDoc
 * @property int $CodProv
 * @property string $FechaEmision
 * @property string|null $FechaVencimiento
 * @property int $Moneda
 * @property string|null $Cambio
 * @property string $BaseAfecta
 * @property string $BaseInafecta
 * @property string $Igv
 * @property string $Total
 * @property string $Estado
 * @property string|null $Glosa
 * @property int|null $OrdenCompraId
 * @property int|null $asiento_id
 * @property int|null $UsuarioId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $MontoPercepcion
 * @property string|null $NroPercepcionSerie
 * @property string|null $NroPercepcionNumero
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompraDet> $detalles
 * @property-read int|null $detalles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereBaseAfecta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereBaseInafecta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCodProv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereFechaEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereFechaVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereGlosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereMoneda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereMontoPercepcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNroPercepcionNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNroPercepcionSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereOrdenCompraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereUsuarioId($value)
 * @mixin \Eloquent
 */
class CompraCab extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CompraCab';
    protected $primaryKey = 'Id';
    
    protected $fillable = [
        'Serie', 'Numero', 'TipoDoc', 'CodProv', 'FechaEmision',
        'FechaVencimiento', 'Moneda', 'Cambio', 'BaseAfecta',
        'BaseInafecta', 'Igv', 'Total', 'Estado', 'Glosa',
        'OrdenCompraId', 'UsuarioId'
    ];

    public function detalles()
    {
        return $this->hasMany(CompraDet::class, 'CompraId');
    }
}
