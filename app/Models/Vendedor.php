<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Codemp
 * @property string $Nombre
 * @property string|null $Direccion
 * @property string|null $Documento
 * @property string|null $Telefono1
 * @property string|null $Telefono2
 * @property string|null $Celular
 * @property string|null $Nextel
 * @property string|null $Cumpleaños
 * @property int $Tipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Doccab> $documentos
 * @property-read int|null $documentos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCodemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCumpleaños($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTipo($value)
 * @mixin \Eloquent
 */
class Vendedor extends Model
{
    protected $table = 'Empleados';
    protected $primaryKey = 'Codemp';
    public $timestamps = false;

    // Ocultar campos sensibles
    protected $hidden = ['Direccion', 'Documento', 'Telefono1', 'Telefono2'];

    // Relación: Un vendedor tiene muchas facturas
    public function documentos()
    {
        return $this->hasMany(Doccab::class, 'Vendedor', 'Codemp');
    }
}