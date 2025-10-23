<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CtaCliente;

class ClienteReniec extends Model
{
    use HasFactory;

    protected $table = 'Clientes_reniec';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Para SQL Server - especificar el key type
    protected $keyType = 'int';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'razon_social',
        'direccion',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'fecha_nacimiento',
        'estado',
        'creado_por'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'estado' => 'boolean'
    ];

    /**
     * Relación con CtaCliente
     */
    public function cuentas(): HasMany
    {
        return $this->hasMany(CtaCliente::class, 'cliente_id');
    }

    /**
     * Usuario que lo creó
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(AccesoWeb::class, 'creado_por');
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para buscar por número de documento
     */
    public function scopePorDocumento($query, $numero)
    {
        return $query->where('numero_documento', $numero);
    }

    /**
     * Scope para buscar por tipo de documento
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', strtoupper($tipo));
    }

    /**
     * Obtener nombre completo calculado
     */
    public function getNombreCompleto()
    {
        if (!empty($this->razon_social)) {
            return $this->razon_social;
        }
        
        $nombres = trim(($this->nombres ?? '') . ' ' . ($this->apellido_paterno ?? '') . ' ' . ($this->apellido_materno ?? ''));
        return $nombres ?: 'NO ESPECIFICADO';
    }

    /**
     * Obtener edad en años
     */
    public function getEdadAttribute()
    {
        if ($this->fecha_nacimiento) {
            return $this->fecha_nacimiento->age;
        }
        return null;
    }

    /**
     * Obtener ubicación completa
     */
    public function getUbicacionCompletaAttribute()
    {
        $ubicacion = [];
        
        if ($this->distrito) $ubicacion[] = $this->distrito;
        if ($this->provincia) $ubicacion[] = $this->provincia;
        if ($this->departamento) $ubicacion[] = $this->departamento;
        
        return implode(' - ', $ubicacion);
    }

    /**
     * Validar si es mayor de edad
     */
    public function getEsMayorEdadAttribute()
    {
        return $this->edad && $this->edad >= 18;
    }

    /**
     * Formatear número de documento
     */
    public function getNumeroDocumentoFormateadoAttribute()
    {
        return number_format((float)$this->numero_documento, 0, '', '');
    }

    
    public function validarDocumento()
    {
        $numero = $this->numero_documento;
        
        switch ($this->tipo_documento) {
            case 'DNI':
                return strlen($numero) === 8 && is_numeric($numero);
            case 'RUC':
                return strlen($numero) === 11 && is_numeric($numero);
            default:
                return false;
        }
    }
}