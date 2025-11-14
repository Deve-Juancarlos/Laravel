<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LibroDiario extends Model
{
    use HasFactory;

    protected $table = 'libro_diario';
    protected $primaryKey = 'id';
    
  
    public $timestamps = true;

    protected $fillable = [
        'numero', 
        'fecha', 
        'glosa', 
        'total_debe', 
        'total_haber',
        'balanceado', 
        'estado', 
        'usuario_id', 
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'balanceado' => 'boolean',
        'total_debe' => 'decimal:2',
        'total_haber' => 'decimal:2',
    ];

   
    public function detalles(): HasMany
    {
        return $this->hasMany(LibroDiarioDetalle::class, 'asiento_id', 'id');
    }

   
    public function usuario(): BelongsTo
    {
        
        return $this->belongsTo(AccesoWeb::class, 'usuario_id', 'idusuario');
    }

    
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha ? Carbon::parse($this->fecha)->format('d/m/Y') : 'N/A';
    }

    public function notificarAdmin(string $titulo, string $mensaje, string $url)
    {
        try {
            // 1. Encontrar a todos los administradores
            $adminIds = AccesoWeb::where('tipousuario', 'ADMIN') // O 'ADMINISTRADOR', revisa tu tabla
                                ->pluck('idusuario');

            if ($adminIds->isEmpty()) {
                Log::warning("No se encontraron administradores para notificar la eliminación del asiento {$this->numero}.");
                return;
            }

            $dataParaInsertar = [];
            $now = Carbon::now();

            // 2. Preparar una notificación para CADA admin
            foreach ($adminIds as $adminId) {
                $dataParaInsertar[] = [
                    'usuario_id' => $adminId,
                    'tipo' => 'SolicitudEliminacion',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'icono' => 'fas fa-trash-alt',
                    'color' => 'danger',
                    'url' => $url,
                    'leida' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // 3. Insertar todas las notificaciones en 1 sola consulta (Optimizado)
            Notificacion::insert($dataParaInsertar);

        } catch (\Exception $e) {
            Log::error("Error al crear notificaciones de eliminación para el asiento {$this->numero}: " . $e->getMessage());
        }
    }
}