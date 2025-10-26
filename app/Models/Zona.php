<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    use HasFactory;

    protected $table = 'Zonas';
    
    protected $primaryKey = 'Codzona';
    public $incrementing = false; // Clave primaria no autoincremental

    protected $fillable = [
        'Codzona',
        'Descripcion',
        'Sector',
        'Distrito',
        'Provincia',
        'Dpto',
        'Zonageo',
    ];

    protected $casts = [
        'Sector' => 'integer',
    ];

    /**
     * Relación con clientes
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'Zona', 'Codzona');
    }

    /**
     * Scope para filtrar por sector
     */
    public function scopePorSector($query, $sector)
    {
        return $query->where('Sector', $sector);
    }

    /**
     * Scope para filtrar por distrito
     */
    public function scopePorDistrito($query, $distrito)
    {
        return $query->where('Distrito', $distrito);
    }

    /**
     * Obtener zona por código
     */
    public static function obtenerPorCodigo($codigo)
    {
        return static::where('Codzona', $codigo)->first();
    }

    /**
     * Verificar si la zona está activa
     */
    public function getEstaActivaAttribute()
    {
        return $this->clientes()->where('Activo', true)->count() > 0;
    }

    /**
     * Contar clientes activos en la zona
     */
    public function getTotalClientesAttribute()
    {
        return $this->clientes()->count();
    }

    /**
     * Obtener información resumida de la zona
     */
    public function getInformacionZonaAttribute()
    {
        return [
            'codigo' => $this->Codzona,
            'descripcion' => $this->Descripcion,
            'distrito' => $this->Distrito,
            'provincia' => $this->Provincia,
            'departamento' => $this->Dpto,
            'total_clientes' => $this->total_clientes,
            'clientes_activos' => $this->clientes()->where('Activo', true)->count(),
        ];
    }
}