<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'Caja';
    
    protected $primaryKey = 'Numero';
    public $incrementing = false;

    protected $fillable = [
        'Numero',
        'Documento',
        'Tipo',
        'Razon',
        'Fecha',
        'Moneda',
        'Cambio',
        'Monto',
        'Eliminado',
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Cambio' => 'decimal:4',
        'Monto' => 'decimal:2',
        'Eliminado' => 'boolean',
    ];

    /**
     * Relación con CuentaPorCobrar
     */
    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'Documento', 'Documento');
    }

    /**
     * Relación con Venta (a través de CuentaPorCobrar)
     */
    public function venta(): BelongsTo
    {
        return $this->cuentaPorCobrar()->with('venta');
    }

    /**
     * Relación con Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->cuentaPorCobrar()->with('cliente');
    }

    /**
     * Relación con Empleado que realizó el pago
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'Razon', 'Codemp');
    }

    /**
     * Relación con Banco (si aplica)
     */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'Razon', 'Cuenta');
    }

    /**
     * Scope por tipo de pago
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Tipo', $tipo);
    }

    /**
     * Scope para pagos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('Eliminado', false);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('Fecha', [$inicio, $fin]);
    }

    /**
     * Scope por documento
     */
    public function scopePorDocumento($query, $documento)
    {
        return $query->where('Documento', $documento);
    }

    /**
     * Scope por moneda
     */
    public function scopePorMoneda($query, $moneda)
    {
        return $query->where('Moneda', $moneda);
    }

    /**
     * Obtener tipo de pago como texto
     */
    public function getTipoPagoAttribute(): string
    {
        $tipos = [
            1 => 'Efectivo',
            2 => 'Cheque',
            3 => 'Transferencia',
            4 => 'Depósito',
            5 => 'Tarjeta de Débito',
            6 => 'Tarjeta de Crédito',
            7 => 'Letra',
        ];

        return $tipos[$this->Tipo] ?? 'Desconocido';
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
     * Obtener monto en soles (conversión si es necesario)
     */
    public function getMontoEnSolesAttribute(): float
    {
        if ($this->Moneda === 1) {
            return $this->Monto;
        } elseif ($this->Moneda === 2 && $this->Cambio > 0) {
            return $this->Monto * $this->Cambio;
        }
        return 0;
    }

    /**
     * Obtener días desde el pago
     */
    public function getDiasTranscurridosAttribute(): int
    {
        return $this->Fecha->diffInDays(now());
    }

    /**
     * Verificar si es un pago completo
     */
    public function getEsPagoCompletoAttribute(): bool
    {
        if ($this->cuentaPorCobrar) {
            $saldoAntes = $this->cuentaPorCobrar->Saldo + $this->Monto;
            return abs($this->Monto - $saldoAntes) <= 0.01; // Tolerancia para decimales
        }
        return false;
    }

    /**
     * Obtener descripción del pago
     */
    public function getDescripcionAttribute(): string
    {
        $descripcion = "Pago de {$this->tipo_pago}";
        
        if ($this->Documento) {
            $descripcion .= " para documento {$this->Documento}";
        }
        
        if ($this->empleado) {
            $descripcion .= " - {$this->empleado->Nombre}";
        }
        
        return $descripcion;
    }

    /**
     * Obtener valor real del pago en soles
     */
    public function getValorRealAttribute(): float
    {
        return $this->monto_en_soles;
    }

    /**
     * Scope para pagos de cuentas por cobrar
     */
    public function scopePagosCuentas($query)
    {
        return $query->whereHas('cuentaPorCobrar');
    }

    /**
     * Scope para pagos de efectivo
     */
    public function scopeEfectivo($query)
    {
        return $query->where('Tipo', 1);
    }

    /**
     * Scope para pagos con cheque
     */
    public function scopeCheque($query)
    {
        return $query->where('Tipo', 2);
    }

    /**
     * Obtener total de pagos en el período
     */
    public static function getTotalEnPeriodo($inicio, $fin, $tipo = null): float
    {
        $query = static::activos()->enPeriodo($inicio, $fin);
        
        if ($tipo) {
            $query->porTipo($tipo);
        }
        
        return $query->sum('Monto');
    }
}