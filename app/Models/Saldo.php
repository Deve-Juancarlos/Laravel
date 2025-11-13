<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Saldo extends Model
{
    protected $table = 'Saldos';
    protected $primaryKey = null; 
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'vencimiento' => 'datetime',
    ];

    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codpro', 'CodPro');
    }
}