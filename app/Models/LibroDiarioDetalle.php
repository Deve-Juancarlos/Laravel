<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $asiento_id
 * @property string $cuenta_contable
 * @property numeric|null $debe
 * @property numeric|null $haber
 * @property string $concepto
 * @property string|null $documento_referencia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LibroDiario $asiento
 * @property-read \App\Models\PlanCuentas $cuenta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereConcepto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereCuentaContable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereDebe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereDocumentoReferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereHaber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LibroDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'libro_diario_detalles';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'asiento_id', 
        'cuenta_contable',
        'debe', 
        'haber', 
        'concepto',
        'documento_referencia'
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

  
    public function asiento(): BelongsTo
    {
        return $this->belongsTo(LibroDiario::class, 'asiento_id', 'id');
    }

  
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(PlanCuentas::class, 'cuenta_contable', 'codigo');
    }
}