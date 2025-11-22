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
 * @property-read mixed $telefono_formateado
 * @property-read \App\Models\AccesoWeb|null $usuarioWeb
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCodemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCumpleaños($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTipo($value)
 * @mixin \Eloquent
 */
class Empleado extends Model
{
    protected $table = 'Empleados';
    protected $primaryKey = 'Codemp';
    public $timestamps = false;

    protected $fillable = [
        'Codemp',
        'Nombre',
        'Direccion',
        'Documento',
        'Telefono1',
        'Telefono2',
        'Celular',
        'Nextel',
        'Cumpleaños',
        'Tipo'
    ];

    
    public function usuarioWeb()
    {
        return $this->hasOne(\App\Models\AccesoWeb::class, 'idusuario', 'Codemp');
    }

    public function getTelefonoFormateadoAttribute()
    {
        $tel = $this->Telefono1; 

    
        if (!$tel || strlen($tel) !== 9) {
                return $tel;
            }

        
            return '+51 ' . substr($tel, 0, 3) . ' ' . substr($tel, 3, 3) . ' ' . substr($tel, 6, 3);
    }
}