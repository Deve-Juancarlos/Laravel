<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'descripcion',
        'categoria', // Ej: 'Nómina', 'Servicios', 'Proveedores', 'Impuestos'
        'monto',
        'fecha',
        'documento', // Opcional: número de factura o comprobante
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    /**
     * Scope para gastos del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha', now()->month)
                     ->whereYear('fecha', now()->year);
    }

    /**
     * Formatear monto con símbolo de moneda
     */
    protected function montoFormateado(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format((float) $this->monto, 2),
        );
    }

    /**
     * Formatear fecha corta
     */
    protected function fechaCorta(): Attribute
    {
        return Attribute::make(
            get: fn () => \Carbon\Carbon::parse($this->fecha)->format('d/m/Y'),
        );
    }
}