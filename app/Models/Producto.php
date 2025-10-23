<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'Productos';
    protected $primaryKey = 'CodPro';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'CodPro',
        'CodBar',
        'Clinea',
        'Clase',
        'Nombre',
        'CodProv',
        'Peso',
        'Minimo',
        'Stock',
        'Afecto',
        'Tipo',
        'Costo',
        'PventaMa',
        'PventaMi',
        'ComisionH',
        'ComisionV',
        'ComisionR',
        'Eliminado',
        'AfecFle',
        'CosReal',
        'RegSanit',
        'TemMax',
        'TemMin',
        'FecSant',
        'Coddigemin',
        'CodLab',
        'Codlab1',
        'Principio',
    ];

    protected $casts = [
        'Peso' => 'decimal:3',
        'Minimo' => 'decimal:2',
        'Stock' => 'decimal:2',
        'Afecto' => 'boolean',
        'Tipo' => 'integer',
        'Costo' => 'decimal:2',
        'PventaMa' => 'decimal:2',
        'PventaMi' => 'decimal:2',
        'ComisionH' => 'decimal:2',
        'ComisionV' => 'decimal:2',
        'ComisionR' => 'decimal:2',
        'Eliminado' => 'boolean',
        'AfecFle' => 'boolean',
        'CosReal' => 'decimal:2',
        'TemMax' => 'integer',
        'TemMin' => 'integer',
        'FecSant' => 'datetime',
        'Clinea' => 'integer',
        'Clase' => 'integer',
    ];

    public function scopeActivos($query)
    {
        return $query->where('Eliminado', false);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereRaw('Stock <= Minimo');
    }

    public function scopeBuscar($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('Nombre', 'like', "%{$term}%")
              ->orWhere('CodPro', 'like', "%{$term}%")
              ->orWhere('CodBar', 'like', "%{$term}%")
              ->orWhere('Principio', 'like', "%{$term}%");
        });
    }

    public function getCostoFormateadoAttribute()
    {
        return number_format((float) $this->Costo, 2);
    }

    public function getPrecioVentaMayorAttribute()
    {
        return number_format((float) $this->PventaMa, 2);
    }

    public function getPrecioVentaMenorAttribute()
    {
        return number_format((float) $this->PventaMi, 2);
    }

    public function getStockFormateadoAttribute()
    {
        return number_format((float) $this->Stock, 2);
    }


    public function hasLowStock()
    {
        return $this->Stock <= $this->Minimo;
    }

    public function isActive()
    {
        return !$this->Eliminado;
    }

    public function getComisionHospitalAttribute()
    {
        return $this->ComisionH ?? 0;
    }

    public function getComisionVendedorAttribute()
    {
        return $this->ComisionV ?? 0;
    }

    public function getComisionRepresentanteAttribute()
    {
        return $this->ComisionR ?? 0;
    }

    public function getMargenAttribute()
    {
        if ($this->Costo > 0) {
            return (($this->PventaMa - $this->Costo) / $this->Costo) * 100;
        }
        return 0;
    }

    public function getEstadoVencimientoAttribute()
    {
        if (!$this->FecSant) {
            return null;
        }

        $fechaVencimiento = \Carbon\Carbon::parse($this->FecSant);
        $hoy = \Carbon\Carbon::now();
        $diasParaVencer = $hoy->diffInDays($fechaVencimiento, false);

        if ($diasParaVencer < 0) {
            return 'vencido';
        } elseif ($diasParaVencer <= 30) {
            return 'por_vencer';
        } else {
            return 'vigente';
        }
    }
}
