<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtaCliente extends Model
{
    use HasFactory;

    /**
     * Nombre real de la tabla
     */
    protected $table = 'ctacliente';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'NroDeuda';

    /**
     * Indicar si la clave primaria es autoincremental
     */
    public $incrementing = false;

    /**
     * Tipo de clave primaria
     */
    protected $keyType = 'int';

    /**
     * Desactivar timestamps (ya que no existen created_at/updated_at)
     */
    public $timestamps = false;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'Documento',
        'Tipo',
        'CodClie',
        'FechaF',
        'FechaV',
        'Importe',
        'Saldo',
        'NroDeuda',
        'cliente_id'
    ];

    /**
     * Casts automáticos
     */
    protected $casts = [
        'Tipo' => 'integer',
        'CodClie' => 'integer',
        'Importe' => 'decimal:2',
        'Saldo' => 'decimal:2',
        'NroDeuda' => 'integer',
        'cliente_id' => 'integer',
        'FechaF' => 'datetime',
        'FechaV' => 'datetime',
    ];

    /**
     * Relación con el modelo Cliente (si existe)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }
}
