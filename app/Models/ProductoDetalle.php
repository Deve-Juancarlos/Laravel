<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ProductoDetalle extends Model
{
    use HasFactory;

    protected $table = 'Docdet';
    
    protected $primaryKey = ['Numero', 'Tipo', 'Codpro', 'Lote', 'Subtotal', 'Nbonif'];
    public $incrementing = false;

    protected $fillable = [
        'Numero',
        'Tipo',
        'Codpro',
        'Lote',
        'Vencimiento',
        'Unimed',
        'Cantidad',
        'Adicional',
        'Precio',
        'Unidades',
        'Almacen',
        'Descuento1',
        'Descuento2',
        'Descuento3',
        'Subtotal',
        'Costo',
        'stock',
        'Codprom',
        'Des_cab',
        'CodOferta',
        'CodAutoriza',
        'Nbonif',
    ];

    protected $casts = [
        'Vencimiento' => 'datetime',
        'Cantidad' => 'decimal:2',
        'Adicional' => 'decimal:2',
        'Precio' => 'decimal:2',
        'Unidades' => 'decimal:2',
        'Descuento1' => 'decimal:2',
        'Descuento2' => 'decimal:2',
        'Descuento3' => 'decimal:2',
        'Subtotal' => 'decimal:2',
        'Costo' => 'decimal:2',
        'stock' => 'decimal:2',
    ];

    /**
     * Relación con Producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'Codpro', 'CodPro');
    }

    /**
     * Relación con Venta (Doccab)
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'Numero', 'Numero')
                   ->where('Tipo', '=', $this->Tipo);
    }

    /**
     * Relación con Cliente a través de la venta
     */
    public function cliente(): BelongsTo
    {
        return $this->venta()->with('cliente');
    }

    /**
     * Relación con Laboratorio a través del producto
     */
    public function laboratorio(): BelongsTo
    {
        return $this->producto()->with('laboratorio');
    }

    /**
     * Scope por producto
     */
    public function scopePorProducto($query, $codpro)
    {
        return $query->where('Codpro', $codpro);
    }

    /**
     * Scope por lote
     */
    public function scopePorLote($query, $lote)
    {
        return $query->where('Lote', $lote);
    }

    /**
     * Scope por vencimiento
     */
    public function scopePorVencimiento($query, $fecha)
    {
        return $query->where('Vencimiento', '<=', $fecha);
    }

    /**
     * Scope por almacén
     */
    public function scopePorAlmacen($query, $almacen)
    {
        return $query->where('Almacen', $almacen);
    }

    /**
     * Obtener descuento total aplicado
     */
    public function getDescuentoTotalAttribute(): float
    {
        return ($this->Descuento1 ?? 0) + ($this->Descuento2 ?? 0) + ($this->Descuento3 ?? 0);
    }

    /**
     * Obtener precio final con descuentos
     */
    public function getPrecioFinalAttribute(): float
    {
        $precio = $this->Precio + ($this->Adicional ?? 0);
        return max(0, $precio - $this->descuento_total);
    }

    /**
     * Obtener margen de ganancia
     */
    public function getMargenGananciaAttribute(): float
    {
        if ($this->Costo && $this->Subtotal > 0) {
            return (($this->Subtotal - ($this->Costo * $this->Cantidad)) / $this->Subtotal) * 100;
        }
        return 0;
    }

    /**
     * Obtener días hasta vencimiento
     */
    public function getDiasVencimientoAttribute(): ?int
    {
        if (!$this->Vencimiento) return null;
        
        $diferencia = now()->diffInDays($this->Vencimiento, false);
        return $diferencia;
    }

    /**
     * Verificar si está próximo a vencer (30 días)
     */
    public function getProximoVencerAttribute(): bool
    {
        return $this->dias_vencimiento !== null && $this->dias_vencimiento <= 30 && $this->dias_vencimiento >= 0;
    }

    /**
     * Verificar si está vencido
     */
    public function getVencidoAttribute(): bool
    {
        return $this->dias_vencimiento !== null && $this->dias_vencimiento < 0;
    }

    /**
     * Obtener valor total con descuentos aplicados
     */
    public function getValorTotalAttribute(): float
    {
        return $this->Cantidad * $this->precio_final;
    }

    /**
     * Obtener descripción del producto
     */
    public function getNombreProductoAttribute(): string
    {
        return $this->producto ? $this->producto->Nombre : 'Producto no encontrado';
    }

    /**
     * Obtener laboratorio del producto
     */
    public function getNombreLaboratorioAttribute(): string
    {
        return $this->laboratorio ? $this->laboratorio->Descripcion : '';
    }
}