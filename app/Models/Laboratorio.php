<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'Laboratorios';
    
    protected $primaryKey = 'CodLab';
    public $incrementing = false;

    protected $fillable = [
        'CodLab',
        'Descripcion',
        'Mantiene',
        'Importado',
    ];

    protected $casts = [
        'Mantiene' => 'boolean',
        'Importado' => 'boolean',
    ];

    /**
     * Relación con Productos
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'CodLab', 'CodLab');
    }

    /**
     * Scope para laboratorios nacionales
     */
    public function scopeNacionales($query)
    {
        return $query->where('Importado', false);
    }

    /**
     * Scope para laboratorios importados
     */
    public function scopeImportados($query)
    {
        return $query->where('Importado', true);
    }

    /**
     * Obtener total de productos
     */
    public function getTotalProductosAttribute(): int
    {
        return $this->productos()->activos()->count();
    }

    /**
     * Obtener valor del inventario
     */
    public function getValorInventarioAttribute(): float
    {
        return $this->productos()
                   ->activos()
                   ->get()
                   ->sum(function ($producto) {
                       return $producto->Stock * $producto->Costo;
                   });
    }

    /**
     * Obtener información del laboratorio
     */
    public function getInformacionLaboratorioAttribute(): array
    {
        return [
            'codigo' => $this->CodLab,
            'descripcion' => $this->Descripcion,
            'tipo' => $this->importado ? 'Importado' : 'Nacional',
            'total_productos' => $this->total_productos,
            'valor_inventario' => $this->valor_inventario,
        ];
    }
}