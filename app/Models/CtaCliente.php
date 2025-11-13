<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtaCliente extends Model
{
    protected $table = 'CtaCliente';


    protected $primaryKey = 'Documento';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['Documento', 'Tipo', 'CodClie', 'FechaF', 'FechaV', 'Importe', 'Saldo'];

  
   
   protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('Documento', '=', $this->getAttribute('Documento'))
            ->where('Tipo', '=', $this->getAttribute('Tipo'));
        return $query;
    }




    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

 
    public function doccab(): BelongsTo
    {
        return $this->belongsTo(Doccab::class, 'Documento', 'Numero')
                    ->whereColumn('Doccab.Tipo', 'CtaCliente.Tipo');
    }

}