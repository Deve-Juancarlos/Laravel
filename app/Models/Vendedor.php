<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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