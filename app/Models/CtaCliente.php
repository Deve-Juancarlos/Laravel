<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para SIFANO.CtaCliente (Cuentas por Cobrar)
 * ¡Maneja clave primaria compuesta!
 * * 🚀 VERSIÓN CORREGIDA 🚀
 */
class CtaCliente extends Model
{
    protected $table = 'CtaCliente';
    
    // 1. PK Compuesta [Documento, Tipo] (esto estaba bien)
    protected $primaryKey = ['Documento', 'Tipo']; 
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'FechaF' => 'datetime',
        'FechaV' => 'datetime',
        'FechaP' => 'datetime',
    ];

    /**
     * 🚀 ¡CORRECCIÓN! 🚀
     * Sobrescribir métodos de Eloquent para que funcione la PK compuesta.
     * Esta función ahora es correcta.
     */
    protected function setKeysForSaveQuery($query)
    {
        // 1. Usamos la propiedad '$this->primaryKey' (que ES un array)
        //    en lugar del método '$this->getKeyName()' (que devolvía un string).
        $keys = $this->primaryKey;
        
        // 2. Ahora el 'foreach' funciona porque $keys es un array.
        foreach($keys as $key){
            $query->where($key, '=', $this->getAttribute($key));
        }
        return $query;
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'CodClie', 'Codclie');
    }
}