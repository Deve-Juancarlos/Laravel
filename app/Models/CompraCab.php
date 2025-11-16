<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
