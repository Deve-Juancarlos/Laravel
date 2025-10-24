<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ControlTemperatura
{
    /**
     * Handle an incoming request para control de temperatura de medicamentos
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $tipo_control  // tipo de control: lectura, alerta, ajuste
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $tipo_control = 'lectura')
    {
        $productoId = $request->input('producto_id') ?: $request->route('producto');
        $almacenId = $request->input('almacen_id') ?: 1; // Por defecto almacén principal
        
        if (!$productoId) {
            return response()->json([
                'error' => 'Producto no especificado',
                'mensaje' => 'Debe especificar el producto para el control de temperatura'
            ], 422);
        }

        // Obtener información del producto
        $producto = DB::table('Productos')
            ->where('Codigo', $productoId)
            ->first();

        if (!$producto) {
            return response()->json([
                'error' => 'Producto no encontrado',
                'mensaje' => "El producto {$productoId} no existe en el sistema"
            ], 404);
        }

        // Verificar si el producto requiere control de temperatura
        if (!$producto->ControlTemperatura) {
            if ($request->expectsJson()) {
                return response()->json([
                    'producto' => $producto,
                    'control_temperatura' => [
                        'requerido' => false,
                        'mensaje' => 'Este producto no requiere control de temperatura específico'
                    ]
                ]);
            }
            
            return $next($request);
        }

        // Obtener configuraciones de temperatura para el producto
        $configTemp = DB::table('Productos_Control_Temperatura')
            ->where('Producto_id', $productoId)
            ->where('Almacen_id', $almacenId)
            ->first();

        if (!$configTemp) {
            Log::warning('Producto requiere control de temperatura pero no tiene configuración', [
                'producto_id' => $productoId,
                'almacen_id' => $almacenId
            ]);

            return response()->json([
                'error' => 'Configuración de temperatura faltante',
                'mensaje' => 'El producto requiere control de temperatura pero no tiene configuración definida'
            ], 422);
        }

        // Obtener lectura actual de temperatura
        $lecturaActual = $this->getLecturaTemperaturaActual($almacenId);
        
        $controlData = [
            'producto' => $producto,
            'configuracion' => $configTemp,
            'lectura_actual' => $lecturaActual,
            'timestamp' => now(),
            'requiere_control' => true
        ];

        switch ($tipo_control) {
            case 'lectura':
                $controlResult = $this->procesarLectura($request, $controlData);
                break;
                
            case 'alerta':
                $controlResult = $this->procesarAlerta($request, $controlData);
                break;
                
            case 'ajuste':
                $controlResult = $this->procesarAjuste($request, $controlData);
                break;
                
            default:
                $controlResult = $this->validarTemperatura($controlData);
        }

        // Registrar la operación
        $this->registrarOperacionControl($request, $productoId, $almacenId, $tipo_control, $controlData);

        if ($request->expectsJson()) {
            return response()->json($controlResult);
        }

        // Agregar datos de control al request
        $request->attributes->set('control_temperatura', $controlData);
        
        return $next($request);
    }

    /**
     * Procesar lectura de temperatura
     */
    private function procesarLectura(Request $request, array $controlData): array
    {
        $temp = $request->input('temperatura');
        $humedad = $request->input('humedad');
        
        if (!$temp) {
            return [
                'error' => 'Temperatura requerida',
                'mensaje' => 'Debe proporcionar la temperatura actual'
            ];
        }

        $estado = $this->evaluarEstadoTemperatura($temp, $controlData['configuracion']);
        $alerta = $estado['alerta'];
        
        // Registrar la lectura
        DB::table('Lecturas_Temperatura')->insert([
            'Producto_id' => $controlData['producto']->Codigo,
            'Almacen_id' => $controlData['configuracion']->Almacen_id,
            'Temperatura' => $temp,
            'Humedad' => $humedad,
            'Estado' => $estado['nivel'],
            'Fecha_Lectura' => now(),
            'Usuario_id' => auth()->id(),
            'Observaciones' => $request->input('observaciones')
        ]);

        // Si hay alerta, generar notificación
        if ($alerta) {
            $this->generarAlerta($controlData['producto'], $temp, $estado);
        }

        return [
            'temperatura_lectura' => [
                'valor' => $temp,
                'unidad' => '°C',
                'estado' => $estado['nivel'],
                'alerta' => $alerta,
                'mensaje' => $estado['mensaje'],
                'fecha_lectura' => now()
            ],
            'configuracion' => [
                'temperatura_min' => $controlData['configuracion']->TempMin,
                'temperatura_max' => $controlData['configuracion']->TempMax,
                'humedad_max' => $controlData['configuracion']->HumedadMax
            ],
            'historial_alertas' => $this->getHistorialAlertas($controlData['producto']->Codigo)
        ];
    }

    /**
     * Procesar alerta de temperatura
     */
    private function procesarAlerta(Request $request, array $controlData): array
    {
        $tempActual = $controlData['lectura_actual']['temperatura'];
        $estado = $this->evaluarEstadoTemperatura($tempActual, $controlData['configuracion']);
        
        if (!$estado['alerta']) {
            return [
                'status' => 'normal',
                'mensaje' => 'Temperatura dentro del rango permitido',
                'temperatura' => $tempActual
            ];
        }

        // Generar alerta urgente
        $this->generarAlertaUrgente($controlData['producto'], $tempActual, $estado);
        
        // Notificar a responsables
        $this->notificarResponsables($controlData['producto'], $tempActual, $estado);

        return [
            'alerta_generada' => true,
            'nivel_alerta' => $estado['nivel'],
            'temperatura_actual' => $tempActual,
            'rango_permitido' => [
                'min' => $controlData['configuracion']->TempMin,
                'max' => $controlData['configuracion']->TempMax
            ],
            'acciones_requeridas' => $this->getAccionesRequeridas($estado['nivel']),
            'timestamp' => now()
        ];
    }

    /**
     * Procesar ajuste de temperatura
     */
    private function procesarAjuste(Request $request, array $controlData): array
    {
        $accion = $request->input('accion'); // aumentar, reducir, mantener
        $temperaturaObjetivo = $request->input('temperatura_objetivo');
        
        if (!$accion) {
            return [
                'error' => 'Acción no especificada',
                'mensaje' => 'Debe especificar la acción de ajuste (aumentar, reducir, mantener)'
            ];
        }

        // Validar que la temperatura objetivo esté en rango
        if ($temperaturaObjetivo) {
            $tempMin = $controlData['configuracion']->TempMin;
            $tempMax = $controlData['configuracion']->TempMax;
            
            if ($temperaturaObjetivo < $tempMin || $temperaturaObjetivo > $tempMax) {
                return [
                    'error' => 'Temperatura fuera de rango',
                    'mensaje' => "La temperatura objetivo ({$temperaturaObjetivo}°C) debe estar entre {$tempMin}°C y {$tempMax}°C"
                ];
            }
        }

        // Registrar el ajuste
        $ajusteId = DB::table('Ajustes_Temperatura')->insertGetId([
            'Producto_id' => $controlData['producto']->Codigo,
            'Almacen_id' => $controlData['configuracion']->Almacen_id,
            'Temperatura_Anterior' => $controlData['lectura_actual']['temperatura'],
            'Temperatura_Objetivo' => $temperaturaObjetivo,
            'Accion' => $accion,
            'Fecha_Ajuste' => now(),
            'Usuario_id' => auth()->id(),
            'Estado' => 'En Proceso'
        ]);

        // Programar verificación de ajuste en 30 minutos
        $this->programarVerificacion($ajusteId);

        return [
            'ajuste_registrado' => true,
            'ajuste_id' => $ajusteId,
            'accion' => $accion,
            'temperatura_objetivo' => $temperaturaObjetivo,
            'estado' => 'En Proceso',
            'verificacion_programada' => now()->addMinutes(30)->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Evaluar el estado de la temperatura
     */
    private function evaluarEstadoTemperatura(float $temp, object $config): array
    {
        $tempMin = $config->TempMin;
        $tempMax = $config->TempMax;
        $tempCriticaMin = $config->TempCriticaMin ?? ($tempMin - 2);
        $tempCriticaMax = $config->TempCriticaMax ?? ($tempMax + 2);
        
        $estado = [
            'nivel' => 'Normal',
            'mensaje' => 'Temperatura dentro del rango permitido',
            'alerta' => false
        ];
        
        if ($temp < $tempCriticaMin || $temp > $tempCriticaMax) {
            $estado = [
                'nivel' => 'Crítico',
                'mensaje' => 'Temperatura en nivel crítico - Acción inmediata requerida',
                'alerta' => true
            ];
        } elseif ($temp < $tempMin || $temp > $tempMax) {
            $estado = [
                'nivel' => 'Alerta',
                'mensaje' => 'Temperatura fuera del rango óptimo',
                'alerta' => true
            ];
        }
        
        return $estado;
    }

    /**
     * Obtener lectura actual de temperatura
     */
    private function getLecturaTemperaturaActual(int $almacenId): array
    {
        $lectura = DB::table('Lecturas_Temperatura')
            ->where('Almacen_id', $almacenId)
            ->orderBy('Fecha_Lectura', 'desc')
            ->first();
            
        return [
            'temperatura' => $lectura ? $lectura->Temperatura : null,
            'humedad' => $lectura ? $lectura->Humedad : null,
            'fecha_lectura' => $lectura ? $lectura->Fecha_Lectura : null
        ];
    }

    /**
     * Generar alerta de temperatura
     */
    private function generarAlerta(object $producto, float $temp, array $estado): void
    {
        DB::table('Alertas_Temperatura')->insert([
            'Producto_id' => $producto->Codigo,
            'Temperatura' => $temp,
            'Estado' => $estado['nivel'],
            'Fecha_Alerta' => now(),
            'Mensaje' => $estado['mensaje'],
            'Procesada' => false
        ]);
        
        Log::warning("Alerta de temperatura para producto {$producto->Descripcion}", [
            'producto' => $producto->Codigo,
            'temperatura' => $temp,
            'estado' => $estado['nivel']
        ]);
    }

    /**
     * Generar alerta urgente
     */
    private function generarAlertaUrgente(object $producto, float $temp, array $estado): void
    {
        // Insertar alerta urgente
        DB::table('Alertas_Urgentes')->insert([
            'Tipo' => 'Temperatura',
            'Producto_id' => $producto->Codigo,
            'Descripcion' => "Temperatura crítica: {$temp}°C",
            'Prioridad' => 'Urgente',
            'Fecha_Creacion' => now(),
            'Asignado_a' => null,
            'Estado' => 'Abierta'
        ]);
        
        Log::emergency("Alerta urgente de temperatura para producto {$producto->Descripcion}", [
            'producto' => $producto->Codigo,
            'temperatura' => $temp,
            'estado' => $estado['nivel']
        ]);
    }

    /**
     * Registrar operación de control
     */
    private function registrarOperacionControl(Request $request, string $productoId, int $almacenId, string $tipo, array $data): void
    {
        Log::info("Control de temperatura: {$tipo}", [
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'usuario_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Obtener historial de alertas
     */
    private function getHistorialAlertas(string $productoId): array
    {
        return DB::table('Alertas_Temperatura')
            ->where('Producto_id', $productoId)
            ->where('Fecha_Alerta', '>=', now()->subDays(30))
            ->orderBy('Fecha_Alerta', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Obtener acciones requeridas según el nivel
     */
    private function getAccionesRequeridas(string $nivel): array
    {
        $acciones = [
            'Crítico' => [
                'Aislar producto inmediatamente',
                'Contactar farmacéutico responsable',
                'Evaluar integridad del producto',
                'Documentar incidente',
                'Notificar a supervisor'
            ],
            'Alerta' => [
                'Verificar equipos de refrigeración',
                'Ajustar temperatura gradualmente',
                'Monitorear cada 15 minutos',
                'Documentar acción tomada'
            ]
        ];
        
        return $acciones[$nivel] ?? ['Verificar condiciones'];
    }

    /**
     * Notificar a responsables
     */
    private function notificarResponsables(object $producto, float $temp, array $estado): void
    {
        // Esta función enviaría notificaciones por email/SMS
        // Implementación específica según el sistema de notificaciones
        Log::info("Notificación enviada - Temperatura crítica para {$producto->Descripcion}");
    }

    /**
     * Programar verificación de ajuste
     */
    private function programarVerificacion(int $ajusteId): void
    {
        DB::table('Verificaciones_Programadas')->insert([
            'Tipo' => 'Ajuste_Temperatura',
            'Referencia_id' => $ajusteId,
            'Fecha_Programada' => now()->addMinutes(30),
            'Estado' => 'Pendiente'
        ]);
    }
}