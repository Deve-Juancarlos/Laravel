<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Models\Docdet; 

class Doccab extends Model
{
    protected $table = 'Doccab';
   
    protected $primaryKey = 'Numero'; 
    public $incrementing = false;
    protected $keyType = 'string'; 
    public $timestamps = false;

    protected $fillable = [
        'Numero', 'Tipo', 'CodClie', 'Fecha', 'Total', 'Eliminado', 'estado_sunat',
        'serie_sunat', 'correlativo_sunat', 'tipo_documento_sunat' 
    ];

   
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('Numero', '=', $this->getAttribute('Numero'))
            ->where('Tipo', '=', $this->getAttribute('Tipo'));
        return $query;
    }



    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CtaCliente::class, 'Documento', 'Numero')
                    ->whereColumn('CtaCliente.Tipo', 'Doccab.Tipo');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(Docdet::class, 'Numero', 'Numero')
                    ->whereColumn('Docdet.Tipo', 'Doccab.Tipo');
    }

   
}