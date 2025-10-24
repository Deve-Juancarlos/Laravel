<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saldo extends Model
{
    use HasFactory;

    protected $table = 'Saldos';
    
    protected $primaryKey = ['codpro', 'almacen', 'lote'];
    public $incrementing = false;

    protected $fillable = [
        'codpro',
        'almacen',
        'lote',
        'vencimiento',
        'saldo',
        'protocolo',
    ];

    protected $casts = [
        'vencimiento' => 'datetime',
        'saldo' => 'decimal:2',
        'protocolo' => 'boolean',
    ];

    /**
     * Relación con Producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codpro', 'CodPro');
    }

    /**
     * Relación con MovimientoInventario
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'codpro', 'codpro')
                   ->where('almacen', $this->almacen)
                   ->where('lote', $this->lote);
    }

    /**
     * Scope por producto
     */
    public function scopePorProducto($query, $codpro)
    {
        return $query->where('codpro', $codpro);
    }

    /**
     * Scope por almacén
     */
    public function scopePorAlmacen($query, $almacen)
    {
        return $query->where('almacen', $almacen);
    }

    /**
     * Scope por lote
     */
    public function scopePorLote($query, $lote)
    {
        return $query->where('lote', $lote);
    }

    /**
     * Scope para lotes próximos a vencer
     */
    public function scopeProximosVencer($query, $dias = 30)
    {
        return $query->whereNotNull('vencimiento')
                   ->whereBetween('vencimiento', [now(), now()->addDays($dias)])
                   ->where('saldo', '>', 0);
    }

    /**
     * Scope para lotes vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->whereNotNull('vencimiento')
                   ->where('vencimiento', '<', now())
                   ->where('saldo', '>', 0);
    }

    /**
     * Scope para productos con stock
     */
    public function scopeConStock($query)
    {
        return $query->where('saldo', '>', 0);
    }

    /**
     * Scope para productos sin stock
     */
    public function scopeSinStock($query)
    {
        return $query->where('saldo', '<=', 0);
    }

    /**
     * Obtener días hasta vencimiento
     */
    public function getDiasVencimientoAttribute(): ?int
    {
        if (!$this->vencimiento) return null;
        
        return $this->vencimiento->diffInDays(now(), false);
    }

    /**
     * Verificar si está próximo a vencer
     */
    public function getProximoVencerAttribute(): bool
    {
        return $this->dias_vencimiento !== null && 
               $this->dias_vencimiento <= 30 && 
               $this->dias_vencimiento >= 0 &&
               $this->saldo > 0;
    }

    /**
     * Verificar si está vencido
     */
    public function getVencidoAttribute(): bool
    {
        return $this->dias_vencimiento !== null && 
               $this->dias_vencimiento < 0 && 
               $this->saldo > 0;
    }

    /**
     * Obtener estado del lote
     */
    public function getEstadoLoteAttribute(): string
    {
        if ($this->saldo <= 0) {
            return 'Sin Stock';
        }
        
        if ($this->vencido) {
            return 'Vencido';
        }
        
        if ($this->proximo_vencer) {
            return 'Por Vencer';
        }
        
        return 'Vigente';
    }

    /**
     * Obtener valor del lote
     */
    public function getValorLoteAttribute(): float
    {
        if ($this->producto) {
            return $this->saldo * $this->producto->Costo;
        }
        return 0;
    }

    /**
     * Obtener descripción completa del lote
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $descripcion = "Lote {$this->lote}";
        
        if ($this->producto) {
            $descripcion .= " - {$this->producto->Nombre}";
        }
        
        $descripcion .= " - Stock: {$this->saldo}";
        
        if ($this->vencimiento) {
            $descripcion .= " - Vence: {$this->vencimiento->format('Y-m-d')}";
        }
        
        return $descripcion;
    }

    /**
     * Obtener resumen del saldo
     */
    public function getResumenSaldoAttribute(): array
    {
        return [
            'codigo_producto' => $this->codpro,
            'nombre_producto' => $this->producto ? $this->producto->Nombre : 'Producto no encontrado',
            'almacen' => $this->almacen,
            'lote' => $this->lote,
            'vencimiento' => $this->vencimiento ? $this->vencimiento->format('Y-m-d') : null,
            'saldo' => $this->saldo,
            'estado' => $this->estado_lote,
            'dias_vencimiento' => $this->dias_vencimiento,
            'proximo_vencer' => $this->proximo_vencer,
            'vencido' => $this->vencido,
            'valor_lote' => $this->valor_lote,
            'protocolo' => $this->protocolo,
        ];
    }

    /**
     * Actualizar saldo
     */
    public function actualizarSaldo($nuevoSaldo, $motivo = '', $usuarioId = null)
    {
        $saldoAnterior = $this->saldo;
        $this->update(['saldo' => $nuevoSaldo]);
        
        // Crear movimiento de inventario
        MovimientoInventario::registrarMovimiento(
            $this->codpro,
            $this->almacen,
            $this->lote,
            $nuevoSaldo > $saldoAnterior ? 'entrada' : 'salida',
            abs($nuevoSaldo - $saldoAnterior),
            $motivo,
            $usuarioId
        );
    }

    /**
     * Obtener totales por producto
     */
    public static function getTotalesPorProducto($codpro)
    {
        return static::where('codpro', $codpro)
                    ->selectRaw('SUM(saldo) as total_stock, COUNT(*) as total_lotes')
                    ->first();
    }

    /**
     * Obtener productos próximos a vencer en todos los almacenes
     */
    public static function getProductosProximosVencer($dias = 30)
    {
        return static::proximosVencer($dias)
                    ->with('producto')
                    ->orderBy('vencimiento')
                    ->get();
    }
}