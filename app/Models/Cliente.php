<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'Clientes';
    
    protected $primaryKey = 'Codclie';
    public $incrementing = true;

    protected $fillable = [
        'Codclie',
        'tipoDoc',
        'Documento',
        'Razon',
        'Direccion',
        'Telefono1',
        'Telefono2',
        'Fax',
        'Celular',
        'Nextel',
        'Maymin',
        'Fecha',
        'Zona',
        'TipoNeg',
        'TipoClie',
        'Vendedor',
        'Email',
        'Limite',
        'Activo',
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Maymin' => 'boolean',
        'Limite' => 'decimal:2',
        'Activo' => 'boolean',
    ];

    /**
     * Relación con CentroCosto (Zona)
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'Zona', 'Codzona');
    }

    /**
     * Relación con Empleado (Vendedor)
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'Vendedor', 'Codemp');
    }

    /**
     * Relación con Venta
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'CodClie', 'Codclie');
    }

    /**
     * Relación con CuentaPorCobrar
     */
    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'CodClie', 'Codclie')
                   ->where('Saldo', '>', 0);
    }

    /**
     * Relación con ProductoDetalle (a través de ventas)
     */
    public function productosPreferidos(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductoDetalle::class,
            Venta::class,
            'CodClie',  // Clave foránea en "ventas" que referencia al cliente
            'Numero',   // Clave foránea en "producto_detalle" que referencia a la venta
            'Codclie',  // Clave local en el modelo actual (cliente)
            'Numero'    // Clave local en el modelo "ventas"
        )
        ->selectRaw('Codpro, COUNT(*) as frecuencia')
        ->groupBy('Codpro')
        ->orderBy('frecuencia', 'desc')
        ->limit(10);
    }

    public function scopeActivos($query)
    {
        return $query->where('Activo', true);
    }

    
    public function scopePorZona($query, $zona)
    {
        return $query->where('Zona', $zona);
    }

    /**
     * Scope por vendedor
     */
    public function scopePorVendedor($query, $vendedorId)
    {
        return $query->where('Vendedor', $vendedorId);
    }

    /**
     * Scope por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('TipoClie', $tipo);
    }

    /**
     * Obtener total de compras del año
     */
    public function getTotalComprasAnualAttribute(): float
    {
        return $this->ventas()
                   ->whereYear('Fecha', now()->year)
                   ->where('Eliminado', false)
                   ->sum('Total');
    }

    /**
     * Obtener saldo total pendiente
     */
    public function getSaldoTotalPendienteAttribute(): float
    {
        return $this->cuentasPorCobrar()->sum('Saldo');
    }

    /**
     * Verificar si tiene saldo vencido
     */
    public function getTieneSaldoVencidoAttribute(): bool
    {
        return $this->cuentasPorCobrar()
                   ->where('FechaV', '<', now())
                   ->where('Saldo', '>', 0)
                   ->exists();
    }

    /**
     * Obtener últimos 5 productos comprados
     */
    public function getUltimosProductosAttribute(): \Illuminate\Support\Collection
    {
        return $this->hasManyThrough(
            ProductoDetalle::class,
            Venta::class,
            'CodClie',
            'Numero',
            'Codclie',
            'Numero'
        )
        ->whereHas('venta', function ($query) {
            $query->where('Eliminado', false)
                  ->orderBy('Fecha', 'desc')
                  ->limit(5);
        })
        ->with('producto')
        ->get()
        ->unique('Codpro')
        ->take(5);
    }

    /**
     * Obtener días desde última compra
     */
    public function getDiasUltimaCompraAttribute(): ?int
    {
        $ultimaVenta = $this->ventas()
                           ->where('Eliminado', false)
                           ->orderBy('Fecha', 'desc')
                           ->first();
        
        return $ultimaVenta ? $ultimaVenta->Fecha->diffInDays(now()) : null;
    }

    /**
     * Obtener estado del cliente
     */
    public function getEstadoClienteAttribute(): string
    {
        if (!$this->Activo) {
            return 'Inactivo';
        }
        
        if ($this->tiene_saldo_vencido) {
            return 'Con Deuda Vencida';
        }
        
        $dias = $this->dias_ultima_compra;
        if ($dias && $dias > 90) {
            return 'Inactivo (Sin compras recientes)';
        }
        
        return 'Activo';
    }

    /**
     * Obtener información completa del cliente
     */
    public function getInformacionCompletaAttribute(): array
    {
        return [
            'codigo' => $this->Codclie,
            'documento' => $this->Documento,
            'razon_social' => $this->Razon,
            'direccion' => $this->Direccion,
            'telefono' => $this->Telefono1,
            'email' => $this->Email,
            'zona' => $this->zona ? $this->zona->Descripcion : 'No asignada',
            'vendedor' => $this->vendedor ? $this->vendedor->Nombre : 'No asignado',
            'limite_credito' => $this->Limite,
            'estado' => $this->estado_cliente,
            'total_compras_anual' => $this->total_compras_anual,
            'saldo_pendiente' => $this->saldo_total_pendiente,
            'dias_ultima_compra' => $this->dias_ultima_compra,
        ];
    }
}