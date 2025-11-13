<?php

namespace App\Models;

use GuzzleHttp\Psr7\Query;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para SIFANO.Doccab (Cabecera de Facturas/Documentos)
 * ¡Maneja clave primaria compuesta!
 * * 🚀 VERSIÓN CORREGIDA 🚀
 */
class Doccab extends Model
{
    protected $table = 'Doccab';
    
    // 1. Definimos la clave primaria compuesta (esto estaba bien)
    protected $primaryKey = ['Numero', 'Tipo'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'Fecha' => 'datetime',
        'FechaV' => 'datetime',
    ];

    
    protected function setKeysForSaveQuery($query)
    {
        
        $keys = $this->primaryKey;      
        foreach($keys as $key){
            $query->where($key, '=', $this->getAttribute($key));
        }
        return $query;
    }

    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }

    public function detalles()
    {
        return $this->hasMany(Docdet::class, 'Numero', 'Numero')
                    ->where('Tipo', $this->Tipo); // Condición extra para la PK compuesta
    }

    
    public function cuentaPorCobrar()
    {
        // Se une por Numero y Tipo
        return $this->hasOne(CtaCliente::class, 'Documento', 'Numero')
                    ->where('Tipo', $this->Tipo);
    }
}