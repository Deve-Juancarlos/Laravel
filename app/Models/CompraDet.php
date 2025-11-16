<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDet extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CompraDet';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    
    protected $fillable = [
        'CompraId', 'CodPro', 'Cantidad',
        'CostoUnitario', 'Subtotal', 'Lote', 'Vencimiento'
    ];

    public function cabecera()
    {
        return $this->belongsTo(CompraCab::class, 'CompraId');
    }
}
