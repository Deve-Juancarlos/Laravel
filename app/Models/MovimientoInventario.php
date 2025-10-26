<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovimientoInventario extends Model
{
    use HasFactory;

   

    protected $fillable = [
        'codpro',
        'almacen',
        'lote',
        'vencimiento',
        'saldo',
        'protocolo',
        'tipo_movimiento',
        'fecha_movimiento',
        'cantidad_anterior',
        'cantidad_nueva',
        'cantidad_diferencia',
        'motivo',
        'usuario_id',
        'observaciones',
        'stock_sistema',
        'stock_fisico',
        'diferencia_conteo',
        'fecha_conteo',
        'estado_conteo',
    ];

    protected $casts = [
        'vencimiento' => 'datetime',
        'fecha_movimiento' => 'datetime',
        'fecha_conteo' => 'datetime',
        'saldo' => 'decimal:2',
        'cantidad_anterior' => 'decimal:2',
        'cantidad_nueva' => 'decimal:2',
        'cantidad_diferencia' => 'decimal:2',
        'stock_sistema' => 'decimal:2',
        'stock_fisico' => 'decimal:2',
        'diferencia_conteo' => 'decimal:2',
        'protocolo' => 'boolean',
        'estado_conteo' => 'boolean',
    ];

    /**
     * Relación con Producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codpro', 'CodPro');
    }

    /**
     * Relación con Usuario (AccesoWeb)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    /**
     * Relación con Saldo
     */
    public function saldo(): BelongsTo
    {
        return $this->belongsTo(Saldo::class, 'codpro', 'codpro');
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
     * Scope por tipo de movimiento
     */
    public function scopePorTipo($query, $tipo)
    {
        $tipos = [
            'entrada' => 1,
            'salida' => 2,
            'ajuste_positivo' => 3,
            'ajuste_negativo' => 4,
            'conteo_fisico' => 5,
            'vencimiento' => 6,
            'merma' => 7,
        ];

        return $query->where('tipo_movimiento', $tipos[$tipo] ?? $tipo);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_movimiento', [$inicio, $fin]);
    }

    /**
     * Scope para movimientos de entrada
     */
    public function scopeEntradas($query)
    {
        return $query->porTipo('entrada');
    }

    /**
     * Scope para movimientos de salida
     */
    public function scopeSalidas($query)
    {
        return $query->porTipo('salida');
    }

    /**
     * Scope para conteos físicos
     */
    public function scopeConteosFisicos($query)
    {
        return $query->porTipo('conteo_fisico');
    }

    /**
     * Scope para productos próximos a vencer
     */
    public function scopeProximosVencer($query, $dias = 30)
    {
        return $query->where('vencimiento', '<=', now()->addDays($dias))
                   ->where('vencimiento', '>=', now());
    }

    /**
     * Scope para productos vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('vencimiento', '<', now());
    }

    /**
     * Obtener nombre del tipo de movimiento
     */
    public function getTipoMovimientoNombreAttribute(): string
    {
        $tipos = [
            1 => 'Entrada',
            2 => 'Salida',
            3 => 'Ajuste Positivo',
            4 => 'Ajuste Negativo',
            5 => 'Conteo Físico',
            6 => 'Vencimiento',
            7 => 'Merma',
        ];

        return $tipos[$this->tipo_movimiento] ?? 'Desconocido';
    }

    /**
     * Obtener días hasta vencimiento
     */
    public function getDiasVencimientoAttribute(): ?int
    {
        if (!$this->vencimiento) return null;
        
        return now()->diffInDays($this->vencimiento, false);
    }

    /**
     * Verificar si está próximo a vencer (30 días)
     */
    public function getProximoVencerAttribute(): bool
    {
        return $this->dias_vencimiento !== null && 
               $this->dias_vencimiento <= 30 && 
               $this->dias_vencimiento >= 0;
    }

    /**
     * Verificar si está vencido
     */
    public function getVencidoAttribute(): bool
    {
        return $this->dias_vencimiento !== null && $this->dias_vencimiento < 0;
    }

    /**
     * Verificar si hay diferencia en conteo físico
     */
    public function getTieneDiferenciaAttribute(): bool
    {
        return abs($this->diferencia_conteo ?? 0) > 0.01;
    }

    /**
     * Obtener descripción del movimiento
     */
    public function getDescripcionAttribute(): string
    {
        $descripcion = "{$this->tipo_movimiento_nombre}";
        
        if ($this->producto) {
            $descripcion .= " - {$this->producto->Nombre}";
        }
        
        if ($this->lote) {
            $descripcion .= " (Lote: {$this->lote})";
        }
        
        if ($this->cantidad_diferencia != 0) {
            $descripcion .= " - Cantidad: " . number_format(floatval($this->cantidad_diferencia ?? 0), 2);
        }
        
        return $descripcion;
    }

    /**
     * Obtener estado del inventario
     */
    public function getEstadoInventarioAttribute(): string
    {
        if ($this->vencido) {
            return 'Vencido';
        } elseif ($this->proximo_vencer) {
            return 'Por Vencer';
        } elseif ($this->tiene_diferencia) {
            return 'Con Diferencia';
        } else {
            return 'Normal';
        }
    }

    /**
     * Obtener valor del stock en soles
     */
    public function getValorStockAttribute(): float
    {
        if ($this->producto && $this->saldo) {
            return $this->saldo * $this->producto->Costo;
        }
        return 0;
    }

    /**
     * Registrar movimiento de inventario
     */
    public static function registrarMovimiento($codpro, $almacen, $lote, $tipo, $cantidad, $motivo = '', $usuarioId = null)
    {
        $saldo = Saldo::where('codpro', $codpro)
                     ->where('almacen', $almacen)
                     ->where('lote', $lote)
                     ->first();

        $saldoAnterior = $saldo ? $saldo->saldo : 0;
        $saldoNuevo = $saldoAnterior;

        // Calcular nuevo saldo según el tipo de movimiento
        switch ($tipo) {
            case 'entrada':
            case 'ajuste_positivo':
                $saldoNuevo = $saldoAnterior + $cantidad;
                break;
            case 'salida':
            case 'ajuste_negativo':
            case 'merma':
            case 'vencimiento':
                $saldoNuevo = max(0, $saldoAnterior - $cantidad);
                break;
        }

        // Actualizar saldo
        if ($saldo) {
            $saldo->update(['saldo' => $saldoNuevo]);
        } else {
            // Crear nuevo registro en saldos
            $saldo = Saldo::create([
                'codpro' => $codpro,
                'almacen' => $almacen,
                'lote' => $lote,
                'saldo' => $saldoNuevo,
                'vencimiento' => $saldo->vencimiento ?? null,
                'protocolo' => false,
            ]);
        }

        // Crear movimiento
        return static::create([
            'codpro' => $codpro,
            'almacen' => $almacen,
            'lote' => $lote,
            'saldo' => $saldoNuevo,
            'tipo_movimiento' => $tipo,
            'fecha_movimiento' => now(),
            'cantidad_anterior' => $saldoAnterior,
            'cantidad_nueva' => $saldoNuevo,
            'cantidad_diferencia' => $saldoNuevo - $saldoAnterior,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId,
        ]);
    }
}