<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'Productos';
    
    protected $primaryKey = 'CodPro';
    public $incrementing = false;

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
        'Costo' => 'decimal:4',
        'PventaMa' => 'decimal:4',
        'PventaMi' => 'decimal:4',
        'ComisionH' => 'decimal:2',
        'ComisionV' => 'decimal:2',
        'ComisionR' => 'decimal:2',
        'Eliminado' => 'boolean',
        'AfecFle' => 'boolean',
        'CosReal' => 'decimal:4',
        'TemMax' => 'integer',
        'TemMin' => 'integer',
        'FecSant' => 'datetime',
    ];

    /**
     * Relación con Laboratorio
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'CodLab', 'CodLab');
    }

    /**
     * Relación con Historial de Precios
     */
    public function historialPrecios(): HasMany
    {
        return $this->hasMany(HistorialPrecio::class, 'codpro', 'CodPro');
    }

    /**
     * Relación con Saldos
     */
    public function saldos(): HasMany
    {
        return $this->hasMany(Saldo::class, 'codpro', 'CodPro');
    }

    /**
     * Relación con ProductoDetalle (detalles de ventas)
     */
    public function detallesFacturas(): HasMany
    {
        return $this->hasMany(ProductoDetalle::class, 'Codpro', 'CodPro');
    }

    /**
     * Relación con Movimientos de Inventario
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'codpro', 'CodPro');
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('Eliminado', false);
    }

    /**
     * Scope por laboratorio
     */
    public function scopePorLaboratorio($query, $codLab)
    {
        return $query->where('CodLab', $codLab);
    }

    /**
     * Scope por línea
     */
    public function scopePorLinea($query, $linea)
    {
        return $query->where('Clinea', $linea);
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    /**
     * Scope por stock mínimo
     */
    public function scopeStockMinimo($query)
    {
        return $query->whereColumn('Stock', '<=', 'Minimo');
    }

    /**
     * Scope para productos con control de temperatura
     */
    public function scopeConControlTemperatura($query)
    {
        return $query->whereNotNull('TemMax')
                   ->whereNotNull('TemMin')
                   ->where('TemMax', '>', 0);
    }

    /**
     * Obtener margen de ganancia mayorista
     */
    public function getMargenMayoristaAttribute(): float
    {
        if ($this->Costo > 0 && $this->PventaMa > 0) {
            return (($this->PventaMa - $this->Costo) / $this->Costo) * 100;
        }
        return 0;
    }

    /**
     * Obtener margen de ganancia minorista
     */
    public function getMargenMinoristaAttribute(): float
    {
        if ($this->Costo > 0 && $this->PventaMi > 0) {
            return (($this->PventaMi - $this->Costo) / $this->Costo) * 100;
        }
        return 0;
    }

    /**
     * Obtener valor total del inventario
     */
    public function getValorInventarioAttribute(): float
    {
        return $this->Stock * $this->Costo;
    }

    /**
     * Obtener valor potencial de venta
     */
    public function getValorVentaAttribute(): float
    {
        return $this->Stock * $this->PventaMi;
    }

    /**
     * Verificar si está bajo stock mínimo
     */
    public function getBajoStockAttribute(): bool
    {
        return $this->Stock <= $this->Minimo;
    }

    /**
     * Verificar si requiere control de temperatura
     */
    public function getRequiereControlTempAttribute(): bool
    {
        return !is_null($this->TemMax) && $this->TemMax > 0;
    }

    /**
     * Obtener días hasta vencimiento del lote más próximo
     */
    public function getDiasVencimientoLoteAttribute(): ?int
    {
        $loteProximo = $this->saldos()
                           ->whereNotNull('vencimiento')
                           ->where('vencimiento', '>', now())
                           ->orderBy('vencimiento')
                           ->first();
        
        return $loteProximo ? $loteProximo->vencimiento->diffInDays(now(), false) : null;
    }

    /**
     * Verificar si tiene lotes próximos a vencer (30 días)
     */
    public function getTieneLotesProximosVencerAttribute(): bool
    {
        return $this->saldos()
                   ->whereNotNull('vencimiento')
                   ->whereBetween('vencimiento', [now(), now()->addDays(30)])
                   ->exists();
    }

    /**
     * Obtener rotación anual
     */
    public function getRotacionAnualAttribute(): float
    {
        $ventasAnuales = $this->detallesFacturas()
                             ->whereHas('venta', function ($query) {
                                 $query->whereYear('Fecha', now()->year)
                                       ->where('Eliminado', false);
                             })
                             ->sum('Cantidad');
        
        return $ventasAnuales / 12; // Promedio mensual
    }

    /**
     * Analizar rotación del producto
     */
    public function analizarRotacion(): array
    {
        $ventasAnuales = $this->detallesFacturas()
                             ->whereHas('venta', function ($query) {
                                 $query->whereYear('Fecha', now()->year)
                                       ->where('Eliminado', false);
                             })
                             ->sum('Cantidad');

        $promedioMensual = $ventasAnuales / 12;
        $stockActual = $this->Stock;
        
        if ($stockActual <= 0) {
            return ['categoria' => 'Sin Stock', 'meses_cobertura' => 0, 'rotacion' => 0];
        }

        $mesesCobertura = $stockActual / ($promedioMensual > 0 ? $promedioMensual : 1);
        
        if ($mesesCobertura <= 1) {
            return ['categoria' => 'Alta Rotación', 'meses_cobertura' => $mesesCobertura, 'rotacion' => 'Alta'];
        } elseif ($mesesCobertura <= 3) {
            return ['categoria' => 'Rotación Normal', 'meses_cobertura' => $mesesCobertura, 'rotacion' => 'Normal'];
        } elseif ($mesesCobertura <= 6) {
            return ['categoria' => 'Rotación Lenta', 'meses_cobertura' => $mesesCobertura, 'rotacion' => 'Lenta'];
        } else {
            return ['categoria' => 'Stock Muerto', 'meses_cobertura' => $mesesCobertura, 'rotacion' => 'Muerta'];
        }
    }

    /**
     * Obtener información de control de temperatura
     */
    public function getControlTemperaturaAttribute(): array
    {
        if (!$this->requiere_control_temp) {
            return ['requiere' => false];
        }

        return [
            'requiere' => true,
            'temperatura_minima' => $this->TemMin,
            'temperatura_maxima' => $this->TemMax,
            'rango' => $this->TemMin . '°C - ' . $this->TemMax . '°C',
            'fecha_sanitaria' => $this->FecSant?->format('Y-m-d'),
        ];
    }

    /**
     * Obtener resumen del producto
     */
    public function getResumenProductoAttribute(): array
    {
        return [
            'codigo' => $this->CodPro,
            'nombre' => $this->Nombre,
            'laboratorio' => $this->laboratorio ? $this->laboratorio->Descripcion : 'No asignado',
            'stock_actual' => $this->Stock,
            'stock_minimo' => $this->Minimo,
            'bajo_stock' => $this->bajo_stock,
            'costo' => $this->Costo,
            'precio_mayorista' => $this->PventaMa,
            'precio_minorista' => $this->PventaMi,
            'margen_mayorista' => round($this->margen_mayorista, 2),
            'margen_minorista' => round($this->margen_minorista, 2),
            'valor_inventario' => $this->valor_inventario,
            'valor_venta' => $this->valor_venta,
            'rotacion' => $this->analizarRotacion(),
            'control_temperatura' => $this->control_temperatura,
            'lotes_proximos_vencer' => $this->tiene_lotes_proximos_vencer,
            'eliminado' => $this->Eliminado,
        ];
    }

    /**
     * Obtener comisiones del producto
     */
    public function getComisionesAttribute(): array
    {
        return [
            'head' => $this->ComisionH ?? 0,
            'vendedor' => $this->ComisionV ?? 0,
            'representante' => $this->ComisionR ?? 0,
        ];
    }
}