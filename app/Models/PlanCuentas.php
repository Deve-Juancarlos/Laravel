<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $codigo
 * @property string $nombre
 * @property string $tipo
 * @property string|null $subtipo
 * @property bool|null $activo
 * @property int|null $nivel
 * @property string|null $cuenta_padre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LibroDiarioDetalle> $movimientos
 * @property-read int|null $movimientos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCuentaPadre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereNivel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereSubtipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanCuentas extends Model
{
    use HasFactory;

    protected $table = 'plan_cuentas';
    
   
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = true;

    protected $fillable = [
        'codigo', 
        'nombre', 
        'tipo', 
        'subtipo', 
        'activo', 
        'nivel', 
        'cuenta_padre'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

 
    public function movimientos(): HasMany
    {
        return $this->hasMany(LibroDiarioDetalle::class, 'cuenta_contable', 'codigo');
    }
}