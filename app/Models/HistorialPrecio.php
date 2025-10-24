<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class HistorialPrecio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codpro',
        'precio_anterior',
        'precio_nuevo',
        'tipo_precio',
        'fecha_cambio',
        'motivo_cambio',
        'usuario_id',
        'porcentaje_cambio',
        'valor_cambio',
        'activo',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'precio_anterior' => 'decimal:4',
        'precio_nuevo' => 'decimal:4',
        'porcentaje_cambio' => 'decimal:2',
        'valor_cambio' => 'decimal:4',
        'activo' => 'boolean',
    ];

    /**
     * Relación con Producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codpro', 'CodPro');
    }

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    /**
     * Scope por producto
     */
    public function scopePorProducto($query, $codpro)
    {
        return $query->where('codpro', $codpro);
    }

    /**
     * Scope por tipo de precio
     */
    public function scopePorTipo($query, $tipo)
    {
        $tipos = [
            'costo' => 1,
            'precio_mayorista' => 2,
            'precio_minorista' => 3,
        ];

        return $query->where('tipo_precio', $tipos[$tipo] ?? $tipo);
    }

    /**
     * Scope por período
     */
    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_cambio', [$inicio, $fin]);
    }

    /**
     * Scope para cambios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para cambios de precio mayorista
     */
    public function scopePreciosMayorista($query)
    {
        return $query->porTipo('precio_mayorista');
    }

    /**
     * Scope para cambios de precio minorista
     */
    public function scopePreciosMinorista($query)
    {
        return $query->porTipo('precio_minorista');
    }

    /**
     * Scope para cambios de costo
     */
    public function scopeCambiosCosto($query)
    {
        return $query->porTipo('costo');
    }

    /**
     * Obtener nombre del tipo de precio
     */
    public function getTipoPrecioNombreAttribute(): string
    {
        $tipos = [
            1 => 'Costo',
            2 => 'Precio Mayorista',
            3 => 'Precio Minorista',
        ];

        return $tipos[$this->tipo_precio] ?? 'Desconocido';
    }

    /**
     * Obtener cambio en porcentaje
     */
    public function getPorcentajeCambioCalculadoAttribute(): float
    {
        if ($this->precio_anterior > 0) {
            return (($this->precio_nuevo - $this->precio_anterior) / $this->precio_anterior) * 100;
        }
        return 0;
    }

    /**
     * Obtener cambio en valor absoluto
     */
    public function getValorCambioCalculadoAttribute(): float
    {
        return $this->precio_nuevo - $this->precio_anterior;
    }

    /**
     * Obtener descripción del cambio
     */
    public function getDescripcionCambioAttribute(): string
    {
        $descripcion = "Cambio de {$this->tipo_precio_nombre}";
        $descripcion .= ": " . number_format($this->precio_anterior, 4);
        $descripcion .= " → " . number_format($this->precio_nuevo, 4);
        
        if ($this->porcentaje_cambio_calculado != 0) {
            $signo = $this->porcentaje_cambio_calculado > 0 ? '+' : '';
            $descripcion .= " ({$signo}" . number_format($this->porcentaje_cambio_calculado, 2) . "%)";
        }
        
        if ($this->motivo_cambio) {
            $descripcion .= " - Motivo: {$this->motivo_cambio}";
        }
        
        return $descripcion;
    }

    /**
     * Verificar si fue un incremento
     */
    public function getFueIncrementoAttribute(): bool
    {
        return $this->precio_nuevo > $this->precio_anterior;
    }

    /**
     * Verificar si fue una reducción
     */
    public function getFueReduccionAttribute(): bool
    {
        return $this->precio_nuevo < $this->precio_anterior;
    }

    /**
     * Verificar si el cambio fue significativo (más del 5%)
     */
    public function getCambioSignificativoAttribute(): bool
    {
        return abs($this->porcentaje_cambio_calculado) > 5;
    }

    /**
     * Obtener impacto financiero
     */
    public function getImpactoFinancieroAttribute(): string
    {
        if ($this->producto) {
            $stock = $this->producto->Stock ?? 0;
            $impacto = $this->valor_cambio_calculado * $stock;
            
            if ($impacto > 0) {
                return "Impacto positivo: S/ " . number_format($impacto, 2);
            } elseif ($impacto < 0) {
                return "Impacto negativo: S/ " . number_format(abs($impacto), 2);
            } else {
                return "Sin impacto financiero";
            }
        }
        
        return "No calculado";
    }

    /**
     * Registrar cambio de precio
     */
    public static function registrarCambioPrecio($codpro, $precioAnterior, $precioNuevo, $tipoPrecio, $motivo = '', $usuarioId = null)
    {
        $tipo = is_string($tipoPrecio) ? $tipoPrecio : $tipoPrecio;
        
        // Crear registro del historial
        $historial = static::create([
            'codpro' => $codpro,
            'precio_anterior' => $precioAnterior,
            'precio_nuevo' => $precioNuevo,
            'tipo_precio' => $tipoPrecio,
            'fecha_cambio' => now(),
            'motivo_cambio' => $motivo,
            'usuario_id' => $usuarioId,
            'porcentaje_cambio' => $precioAnterior > 0 ? 
                (($precioNuevo - $precioAnterior) / $precioAnterior) * 100 : 0,
            'valor_cambio' => $precioNuevo - $precioAnterior,
            'activo' => true,
        ]);

        // Actualizar el precio en la tabla productos
        $producto = Producto::where('CodPro', $codpro)->first();
        if ($producto) {
            $campoPrecio = '';
            switch ($tipo) {
                case 1:
                case 'costo':
                    $campoPrecio = 'Costo';
                    break;
                case 2:
                case 'precio_mayorista':
                case 'PventaMa':
                    $campoPrecio = 'PventaMa';
                    break;
                case 3:
                case 'precio_minorista':
                case 'PventaMi':
                    $campoPrecio = 'PventaMi';
                    break;
            }

            if ($campoPrecio) {
                $producto->update([$campoPrecio => $precioNuevo]);
            }
        }

        return $historial;
    }

    /**
     * Obtener historial completo de un producto
     */
    public static function getHistorialCompleto($codpro)
    {
        return static::porProducto($codpro)
                    ->with(['producto', 'usuario'])
                    ->orderBy('fecha_cambio', 'desc')
                    ->get();
    }

    /**
     * Obtener último cambio de precio de un producto
     */
    public static function getUltimoCambio($codpro, $tipoPrecio = null)
    {
        $query = static::porProducto($codpro)->orderBy('fecha_cambio', 'desc');
        
        if ($tipoPrecio) {
            $query->porTipo($tipoPrecio);
        }
        
        return $query->first();
    }

    /**
     * Calcular variación de precio en período
     */
    public static function calcularVariacionPeriodo($codpro, $inicio, $fin, $tipoPrecio = 3)
    {
        $primerCambio = static::porProducto($codpro)
                            ->porTipo($tipoPrecio)
                            ->where('fecha_cambio', '>=', $inicio)
                            ->orderBy('fecha_cambio', 'asc')
                            ->first();

        $ultimoCambio = static::porProducto($codpro)
                             ->porTipo($tipoPrecio)
                             ->where('fecha_cambio', '<=', $fin)
                             ->orderBy('fecha_cambio', 'desc')
                             ->first();

        if ($primerCambio && $ultimoCambio) {
            $variacionPorcentual = (($ultimoCambio->precio_nuevo - $primerCambio->precio_anterior) / $primerCambio->precio_anterior) * 100;
            $variacionValor = $ultimoCambio->precio_nuevo - $primerCambio->precio_anterior;
            
            return [
                'variacion_porcentual' => round($variacionPorcentual, 2),
                'variacion_valor' => round($variacionValor, 4),
                'precio_inicial' => $primerCambio->precio_anterior,
                'precio_final' => $ultimoCambio->precio_nuevo,
                'numero_cambios' => static::porProducto($codpro)->porTipo($tipoPrecio)->enPeriodo($inicio, $fin)->count(),
            ];
        }

        return null;
    }

    /**
     * Obtener productos con más cambios de precio
     */
    public static function getProductosMasCambios($limite = 10, $tipoPrecio = null, $inicio = null, $fin = null)
    {
        $query = static::selectRaw('codpro, COUNT(*) as total_cambios, AVG(porcentaje_cambio) as promedio_cambio')
                      ->groupBy('codpro')
                      ->orderBy('total_cambios', 'desc')
                      ->limit($limite);

        if ($tipoPrecio) {
            $query->porTipo($tipoPrecio);
        }

        if ($inicio && $fin) {
            $query->enPeriodo($inicio, $fin);
        }

        return $query->with('producto')->get();
    }
}