<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Factura extends Model
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
     * Relación con detalles (ProductoDetalle)
     * Nota: Mapea a Docdet como placeholder ya que Docdet tiene múltiples relaciones complejas
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(ProductoDetalle::class, 'Numero', 'Numero')
                   ->where('Tipo', '=', $this->Tipo);
    }

    /**
     * Relación con CuentaPorCobrar
     */
    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'Documento', 'Numero');
    }

    /**
     * Relación con Pagos
     * Nota: Mapea a Caja como placeholder para pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'Documento', 'Numero');
    }

    /**
     * Scope para facturas activas
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
     * Scope por vendedor
     */
    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('Vendedor', $vendedorId);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('Fecha', [$inicio, $fin]);
    }

    /**
     * Scope para facturas pendientes de pago
     */
    public function scopePendientesPago($query)
    {
        return $query->whereHas('cuentasPorCobrar', function ($q) {
            $q->where('Saldo', '>', 0);
        });
    }

    /**
     * Obtener total pagado
     */
    public function getTotalPagadoAttribute(): float
    {
        return $this->cuentasPorCobrar()->sum('Importe') - $this->cuentasPorCobrar()->sum('Saldo');
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
        return $this->saldo_pendiente <= 0.01;
    }

    /**
     * Obtener días de vencimiento
     */
    public function getDiasVencimientoAttribute(): ?int
    {
        if (!$this->FechaV) return null;
        
        return $this->FechaV->diffInDays(now(), false);
    }

    /**
     * Verificar si está vencida
     */
    public function getVencidaAttribute(): bool
    {
        return $this->FechaV && $this->FechaV->isPast() && !$this->pagada;
    }

    /**
     * Obtener productos únicos en esta factura
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

    /**
     * Obtener nombre del tipo de documento
     */
    public function getTipoDocumentoAttribute(): string
    {
        $tipos = [
            1 => 'Factura',
            2 => 'Boleta',
            3 => 'Nota de Crédito',
            4 => 'Nota de Débito',
            5 => 'Recibo por Honorarios',
        ];

        return $tipos[$this->Tipo] ?? 'Desconocido';
    }

    /**
     * Obtener resumen de la factura
     */
    public function getResumenFacturaAttribute(): array
    {
        return [
            'numero' => $this->Numero,
            'tipo' => $this->tipo_documento,
            'cliente' => $this->cliente ? $this->cliente->Razon : 'Cliente no encontrado',
            'fecha' => $this->Fecha->format('Y-m-d'),
            'fecha_vencimiento' => $this->FechaV ? $this->FechaV->format('Y-m-d') : null,
            'subtotal' => $this->Subtotal,
            'igv' => $this->Igv,
            'total' => $this->Total,
            'total_pagado' => $this->total_pagado,
            'saldo_pendiente' => $this->saldo_pendiente,
            'pagada' => $this->pagada,
            'vencida' => $this->vencida,
            'dias_vencimiento' => $this->dias_vencimiento,
            'vendedor' => $this->vendedor ? $this->vendedor->Nombre : 'No asignado',
            'total_productos' => $this->detalles->count(),
            'total_unidades' => $this->total_unidades,
        ];
    }

    /**
     * Marcar como eliminada
     */
    public function marcarEliminada($motivo = '')
    {
        $this->update([
            'Eliminado' => true,
            'notas' => ($this->notas ?? '') . " - Eliminada: {$motivo}",
        ]);
    }

    /**
     * Marcar como impresa
     */
    public function marcarImpresa()
    {
        $this->update(['Impreso' => true]);
    }
}