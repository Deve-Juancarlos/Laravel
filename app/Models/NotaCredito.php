<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $Numero
 * @property int $TipoNota
 * @property \Illuminate\Support\Carbon|null $Fecha
 * @property string|null $Documento
 * @property int|null $TipoDoc
 * @property int $Codclie
 * @property numeric|null $Bruto
 * @property numeric|null $Descuento
 * @property numeric|null $Flete
 * @property numeric $Monto
 * @property numeric $Igv
 * @property numeric $Total
 * @property string|null $Observacion
 * @property int|null $Estado
 * @property bool|null $Anulado
 * @property string|null $GuiaRecojo
 * @property-read \App\Models\Cliente|null $cliente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereAnulado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereFlete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereGuiaRecojo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereObservacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTipoNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTotal($value)
 * @mixin \Eloquent
 */
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
