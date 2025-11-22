<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Doccab;
use App\Models\NotaCredito;

/**
 * @property int $Codclie
 * @property string|null $tipoDoc
 * @property string|null $Documento
 * @property string $Razon
 * @property string|null $Direccion
 * @property string|null $Telefono1
 * @property string|null $Telefono2
 * @property string|null $Fax
 * @property string|null $Celular
 * @property string|null $Nextel
 * @property int $Maymin
 * @property string|null $Fecha
 * @property string $Zona
 * @property int|null $TipoNeg
 * @property int|null $TipoClie
 * @property int|null $Vendedor
 * @property string|null $Email
 * @property string|null $Limite
 * @property bool|null $Activo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CtaCliente> $cuentasPorCobrar
 * @property-read int|null $cuentas_por_cobrar_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Doccab> $facturas
 * @property-read int|null $facturas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NotaCredito> $notasCredito
 * @property-read int|null $notas_credito_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereLimite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereMaymin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereRazon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoNeg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereVendedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereZona($value)
 * @mixin \Eloquent
 */
class Cliente extends Model
{
    protected $table = 'Clientes';
    protected $primaryKey = 'Codclie';
    public $incrementing = true;
    public $timestamps = false;

    protected $casts = [
        'Activo' => 'boolean',
    ];

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CtaCliente::class, 'CodClie', 'Codclie');
    }

     public function facturas(): HasMany
    {
        return $this->hasMany(Doccab::class, 'Codclie', 'Codclie');
    }

    
    public function notasCredito(): HasMany
    {
        return $this->hasMany(NotaCredito::class, 'Codclie', 'Codclie');
    }
}