<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    use HasFactory;

    protected $table = 'Bancos';
    
    protected $primaryKey = 'Cuenta';
    public $incrementing = false;

    protected $fillable = [
        'Cuenta',
        'Moneda',
        'Banco',
    ];

    /**
     * Relación con Pagos (Caja)
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'Razon', 'Cuenta');
    }

    /**
     * Scope por moneda
     */
    public function scopePorMoneda($query, $moneda)
    {
        return $query->where('Moneda', $moneda);
    }

    /**
     * Scope para cuentas en soles
     */
    public function scopeEnSoles($query)
    {
        return $query->where('Moneda', 1);
    }

    /**
     * Scope para cuentas en dólares
     */
    public function scopeEnDolares($query)
    {
        return $query->where('Moneda', 2);
    }

    /**
     * Obtener nombre de la moneda
     */
    public function getNombreMonedaAttribute(): string
    {
        $monedas = [
            1 => 'Soles (PEN)',
            2 => 'Dólares (USD)',
        ];

        return $monedas[$this->Moneda] ?? 'Desconocida';
    }

    /**
     * Obtener total de pagos procesados
     */
    public function getTotalPagosAttribute(): float
    {
        return $this->pagos()->where('Eliminado', false)->sum('Monto');
    }

    /**
     * Obtener información del banco
     */
    public function getInformacionBancoAttribute(): array
    {
        return [
            'cuenta' => $this->Cuenta,
            'banco' => $this->Banco,
            'moneda' => $this->nombre_moneda,
            'total_pagos' => $this->total_pagos,
        ];
    }
}   