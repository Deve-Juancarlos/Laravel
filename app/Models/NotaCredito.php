<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    protected $table = 'notas_credito';
    protected $primaryKey = 'Numero';
    public $incrementing = false; // PK es char, no autoincrement
    public $timestamps = false;   // La tabla no tiene created_at / updated_at

    // Tipo de clave primaria
    protected $keyType = 'string';

    protected $fillable = [
        'Numero',
        'TipoNota',
        'Fecha',
        'Documento',
        'TipoDoc',
        'Codclie',
        'Bruto',
        'Descuento',
        'Flete',
        'Monto',
        'Igv',
        'Total',
        'Observacion',
        'Estado',
        'Anulado',
        'GuiaRecojo',
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Bruto' => 'decimal:4',
        'Descuento' => 'decimal:4',
        'Flete' => 'decimal:4',
        'Monto' => 'decimal:4',
        'Igv' => 'decimal:4',
        'Total' => 'decimal:4',
        'Anulado' => 'boolean',
    ];

    // RELACIÓN → cada nota pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'Codclie', 'Codclie');
    }
}
