<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Producto extends Model
{
    protected $table = 'Productos';
    protected $primaryKey = 'CodPro';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; 

    
    public function laboratorio()
    {
        
        return $this->belongsTo(Laboratorio::class, 'CodProv', 'CodLab');
    }

    
    public function saldos()
    {
        return $this->hasMany(Saldo::class, 'codpro', 'CodPro');
    }

   
    public function proveedor()
    {
        // Asume que Proveedores.CodProv es la PK (int)
        return $this->belongsTo(Proveedor::class, 'CodProv', 'CodProv');
    }

    
    public function scopeStockBajo($query)
    {
        return $query->where('Eliminado', 0)
                     ->whereColumn('Stock', '<=', 'Minimo') // Compara dos columnas
                     ->where('Stock', '>', 0);
    }
}