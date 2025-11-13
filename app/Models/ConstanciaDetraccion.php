<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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