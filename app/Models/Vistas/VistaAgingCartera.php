<?php
namespace App\Models\Vistas;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $Codclie
 * @property string|null $Razon
 * @property string $Documento
 * @property string $FechaF
 * @property string|null $FechaV
 * @property string $Importe
 * @property string $Saldo
 * @property int|null $dias_vencidos
 * @property string $rango
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereDiasVencidos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereFechaF($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereImporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereRango($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereRazon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereSaldo($value)
 * @mixin \Eloquent
 */
class VistaAgingCartera extends Model
{
    protected $table = 'v_aging_cartera';
    public $timestamps = false;
    protected $primaryKey = null;
}