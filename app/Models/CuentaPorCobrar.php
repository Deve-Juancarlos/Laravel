<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaPorCobrar extends Model
{
    use HasFactory;

    protected $table = 'CtaCliente';
    
    protected $primaryKey = ['Documento', 'Tipo'];
    public $incrementing = false;

    protected $fillable = [
        'Documento',
        'Tipo',
        'CodClie',
        'FechaF',
        'FechaV',
        'Importe',
        'Saldo',
        'cliente_id',
    ];

    protected $casts = [
        'FechaF' => 'datetime',
        'FechaV' => 'datetime',
        'Importe' => 'decimal:2',
        'Saldo' => 'decimal:2',
    ];

    /**
     * Relación con Cliente (Clientes)
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    /**
     * Relación con Cliente RENIEC (opcional)
     */
    public function clienteReniec(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ClienteReniec::class, 'cliente_id');
    }

    /**
     * Relación con Factura (Doccab)
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'Documento', 'Numero');
    }

    /**
     * Relación con Pagos (Caja)
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'Documento', 'Documento');
    }

    /**
     * Scope para cuentas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('Saldo', '>', 0);
    }

    /**
     * Scope para vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('FechaV', '<', now());
    }

    /**
     * Obtener el saldo pendiente
     */
    public function getSaldoPendienteAttribute(): float
    {
        return max(0, $this->Saldo);
    }

    /**
     * Verificar si está vencida
     */
    public function getVencidaAttribute(): bool
    {
        return $this->FechaV && $this->FechaV->isPast();
    }

    /**
     * Obtener días de vencimiento
     */
    public function getDiasVencimientoAttribute(): ?int
    {
        if (!$this->FechaV) return null;
        
        $diferencia = now()->diffInDays($this->FechaV, false);
        return $diferencia < 0 ? abs($diferencia) : 0;
    }

    /**
     * Relación con empleados (vendedores)
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'Vendedor', 'Codemp');
    }
}