<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    protected $table = 'Productos';
    protected $primaryKey = 'CodPro';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'Eliminado' => 'boolean',
        'SujetoADetraccion' => 'boolean',
    ];

    public function scopeStockBajo(Builder $query)
    {
        return $query->where('Stock', '<', DB::raw('ISNULL(NULLIF(Minimo, 0), 1)'))
                     ->where('Eliminado', false);
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'CodProv', 'CodLab');
    }
}