<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para SIFANO.Docdet (Detalle de Documentos)
 * (Este modelo no es usado por el Dashboard, pero es bueno tenerlo)
 */
class Docdet extends Model
{
    protected $table = 'Docdet';
    
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'Vencimiento' => 'datetime',
    ];

  
    public function cabecera()
    {
        return $this->belongsTo(Doccab::class, ['Numero', 'Tipo'], ['Numero', 'Tipo']);
    }

    /**
     * Relación con el Producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Codpro', 'CodPro');
    }
}