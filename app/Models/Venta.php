<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'Doccab';
    
    protected $primaryKey = ['Numero', 'Tipo'];
    public $incrementing = false;

    protected $fillable = [
        'Numero',
        'Tipo',
        'CodClie',
        'Fecha',
        'Dias',
        'FechaV',
        'Bruto',
        'Descuento',
        'Flete',
        'Subtotal',
        'Igv',
        'Total',
        'Moneda',
        'Cambio',
        'Vendedor',
        'Transporte',
        'Eliminado',
        'Impreso',
        'NroPedido',
        'NroGuia',
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'FechaV' => 'datetime',
        'Bruto' => 'decimal:2',
        'Descuento' => 'decimal:2',
        'Flete' => 'decimal:2',
        'Subtotal' => 'decimal:2',
        'Igv' => 'decimal:2',
        'Total' => 'decimal:2',
        'Cambio' => 'decimal:4',
        'Eliminado' => 'boolean',
        'Impreso' => 'boolean',
    ];

    /**
     * Relación con Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    /**
     * Relación con Empleado (Vendedor)
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'Vendedor', 'Codemp');
    }

    /**
     * Relación con detalles (Docdet)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(ProductoDetalle::class, 'Numero', 'Numero')
                   ->where('Tipo', '=', $this->Tipo);
    }

    /**
     * Relación con CuentaPorCobrar
     */
    public function cuentaPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'Documento', 'Numero');
    }

    /**
     * Relación con Pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'Documento', 'Numero');
    }

    /**
     * Scope para ventas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('Eliminado', false);
    }

    /**
     * Scope por tipo de documento
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('Fecha', [$inicio, $fin]);
    }

    /**
     * Obtener el total pagado
     */
    public function getTotalPagadoAttribute(): float
    {
        return $this->pagos()->sum('Monto');
    }

    /**
     * Obtener saldo pendiente
     */
    public function getSaldoPendienteAttribute(): float
    {
        return max(0, $this->Total - $this->total_pagado);
    }

    /**
     * Verificar si está pagada completamente
     */
    public function getPagadaAttribute(): bool
    {
        return $this->saldo_pendiente <= 0;
    }

    /**
     * Obtener días desde la venta
     */
    public function getDiasVentaAttribute(): int
    {
        return $this->Fecha->diffInDays(now());
    }

    /**
     * Obtener productos únicos en esta venta
     */
    public function getProductosUnicosAttribute(): Collection
    {
        return $this->detalles()->with('producto')->get()->unique('Codpro');
    }

    /**
     * Obtener total de unidades vendidas
     */
    public function getTotalUnidadesAttribute(): float
    {
        return $this->detalles()->sum('Cantidad');
    }
}