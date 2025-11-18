<?php

namespace App\Services\Contabilidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificacionDescuentoService
{
    protected $connection = 'sqlsrv';

    /**
     * Notificar descuento de letras a administradores y contadores
     */
    public function notificarDescuentoLetras($datos)
    {
        try {
            // Obtener usuarios ADMINISTRADOR y CONTADOR activos
            $administradores = DB::connection($this->connection)
                ->table('accesoweb')
                ->whereIn('tipousuario', ['administrador', 'ADMINISTRADOR', 'contador', 'CONTADOR'])
                ->where('estado', 'ACTIVO')
                ->get();
            
            if ($administradores->isEmpty()) {
                Log::warning("No hay administradores/contadores activos para notificar");
                // Notificar al menos al usuario actual como fallback
                $usuarioActual = Auth::user();
                if ($usuarioActual) {
                    $administradores = collect([$usuarioActual]);
                } else {
                    return ['success' => false, 'mensaje' => 'No hay usuarios activos para notificar'];
                }
            }
            
            // Determinar tipo y color según monto
            $tipo = $datos['montoTotal'] > 10000 ? 'CRITICO' : 'ALERTA';
            $color = $datos['montoTotal'] > 10000 ? 'danger' : 'warning';
            $tituloPrefix = $datos['montoTotal'] > 10000 ? '⚠️ ALERTA - ' : '';
            
            $porcentajeInteres = ($datos['montoTotal'] > 0) 
                ? ($datos['interes'] / $datos['montoTotal']) * 100 
                : 0;
            
            $notificacionesCreadas = 0;
            
            foreach ($administradores as $admin) {
                // Usar el campo 'id' que es el identity autoincremental
                $usuarioId = $admin->id ?? $admin->idusuario;
                
                DB::connection($this->connection)->table('notificaciones')->insert([
                    'usuario_id' => $usuarioId,
                    'tipo' => $tipo,
                    'titulo' => $tituloPrefix . "Descuento de Letras Procesado",
                    'mensaje' => $this->construirMensaje($datos, $porcentajeInteres),
                    'icono' => 'fa-money-check-alt',
                    'color' => $color,
                    'url' => route('contador.letras_descuento.show', [
                        'serie' => $datos['serie'], 
                        'numero' => $datos['numero']
                    ]),
                    'leida' => 0,
                    'metadata' => json_encode($this->construirMetadata($datos, $porcentajeInteres)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $notificacionesCreadas++;
                
                Log::info("Notificación creada", [
                    'usuario_id' => $usuarioId,
                    'usuario' => $admin->usuario ?? 'N/A',
                    'tipo_usuario' => $admin->tipousuario ?? 'N/A'
                ]);
            }
            
            Log::info("Notificaciones de descuento enviadas", [
                'serie' => $datos['serie'],
                'numero' => $datos['numero'],
                'usuarios_notificados' => $notificacionesCreadas
            ]);
            
            return [
                'success' => true, 
                'notificados' => $notificacionesCreadas
            ];
            
        } catch (\Exception $e) {
            Log::error("Error al crear notificación descuento letras: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construir mensaje de notificación
     */
    protected function construirMensaje($datos, $porcentajeInteres)
    {
        $usuario = Auth::user();
        $nombreUsuario = $usuario ? ($usuario->usuario ?? $usuario->Usuario ?? 'Sistema') : 'Sistema';
        
        return "Se ha procesado la planilla de descuento {$datos['serie']}-{$datos['numero']}.\n\n" .
               "📊 DETALLES:\n" .
               "• Monto Total: S/ " . number_format($datos['montoTotal'], 2) . "\n" .
               "• Abono Neto: S/ " . number_format($datos['montoNeto'], 2) . "\n" .
               "• Intereses (" . number_format($porcentajeInteres, 2) . "%): S/ " . number_format($datos['interes'], 2) . "\n" .
               "• Banco: {$datos['banco']}\n" .
               "• Asiento Contable: {$datos['asiento']}\n" .
               "• Procesado por: {$nombreUsuario}";
    }

    /**
     * Construir metadata JSON
     */
    protected function construirMetadata($datos, $porcentajeInteres)
    {
        $usuario = Auth::user();
        
        return [
            'modulo' => 'descuento_letras',
            'serie' => $datos['serie'],
            'numero' => $datos['numero'],
            'asiento' => $datos['asiento'],
            'monto_total' => $datos['montoTotal'],
            'monto_neto' => $datos['montoNeto'],
            'intereses' => $datos['interes'],
            'porcentaje_interes' => round($porcentajeInteres, 2),
            'banco' => $datos['banco'],
            'usuario_procesador_id' => $usuario ? ($usuario->id ?? $usuario->idusuario) : null,
            'usuario_procesador' => $usuario ? ($usuario->usuario ?? $usuario->Usuario) : 'Sistema',
            'fecha_proceso' => now()->format('Y-m-d H:i:s')
        ];
    }
}
