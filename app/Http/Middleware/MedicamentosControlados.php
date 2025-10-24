<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MedicamentosControlados
{
    /**
     * Handle an incoming request para control de medicamentos controlados según DIGEMID
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $tipo_operacion  // tipo: venta, dispensacion, inventario, prescripcion
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $tipo_operacion = 'venta')
    {
        $productoId = $request->input('producto_id') ?: $request->route('producto');
        $cantidad = $request->input('cantidad', 0);
        
        if (!$productoId) {
            return response()->json([
                'error' => 'Producto no especificado',
                'mensaje' => 'Debe especificar el medicamento para verificación de control'
            ], 422);
        }

        // Obtener información del producto
        $producto = DB::table('Productos')
            ->where('Codigo', $productoId)
            ->first();

        if (!$producto) {
            return response()->json([
                'error' => 'Producto no encontrado',
                'mensaje' => "El medicamento {$productoId} no existe"
            ], 404);
        }

        // Verificar si es medicamento controlado
        $medicamentoControlado = $this->esMedicamentoControlado($productoId);
        
        if (!$medicamentoControlado) {
            // Producto no controlado, continuar normalmente
            if ($request->expectsJson()) {
                return response()->json([
                    'medicamento_controlado' => false,
                    'mensaje' => 'Producto no requiere control especial',
                    'producto' => $producto
                ]);
            }
            
            return $next($request);
        }

        // Si es medicamento controlado, aplicar controles específicos
        $controlResult = $this->procesarControlMedicamento($request, $producto, $tipo_operacion, $cantidad);
        
        if ($controlResult['error']) {
            return response()->json($controlResult, $controlResult['codigo_error'] ?? 422);
        }

        // Registrar la operación de control
        $this->registrarOperacionControl($request, $producto, $tipo_operacion, $controlResult);

        if ($request->expectsJson()) {
            return response()->json([
                'medicamento_controlado' => true,
                'operacion_autorizada' => true,
                'control_aplicado' => $controlResult,
                'producto' => $producto,
                'timestamp' => now()
            ]);
        }

        // Agregar datos de control al request
        $request->attributes->set('medicamento_controlado', $medicamentoControlado);
        $request->attributes->set('control_resultado', $controlResult);
        
        return $next($request);
    }

    /**
     * Verificar si un medicamento es controlado
     */
    private function esMedicamentoControlado(string $productoId): ?object
    {
        $controlado = DB::table('Medicamentos_Controlados')
            ->where('Producto_id', $productoId)
            ->first();

        if ($controlado) {
            // Obtener detalles de la clasificación
            $clasificacion = DB::table('Clasificaciones_Control')
                ->where('Id', $controlado->Clasificacion_id)
                ->first();
                
            $controlado->clasificacion_detalle = $clasificacion;
        }

        return $controlado;
    }

    /**
     * Procesar control según el tipo de operación
     */
    private function procesarControlMedicamento(Request $request, object $producto, string $tipoOperacion, float $cantidad): array
    {
        $medicamentoControlado = $this->esMedicamentoControlado($producto->Codigo);
        
        switch ($tipoOperacion) {
            case 'venta':
                return $this->procesarVenta($request, $producto, $medicamentoControlado, $cantidad);
                
            case 'dispensacion':
                return $this->procesarDispensacion($request, $producto, $medicamentoControlado, $cantidad);
                
            case 'inventario':
                return $this->procesarInventario($request, $producto, $medicamentoControlado);
                
            case 'prescripcion':
                return $this->procesarPrescripcion($request, $producto, $medicamentoControlado);
                
            case 'ajuste':
                return $this->procesarAjuste($request, $producto, $medicamentoControlado);
                
            default:
                return $this->validacionesGenerales($request, $producto, $medicamentoControlado);
        }
    }

    /**
     * Procesar venta de medicamento controlado
     */
    private function procesarVenta(Request $request, object $producto, object $medicamentoControlado, float $cantidad): array
    {
        $clienteId = $request->input('cliente_id');
        $numeroReceta = $request->input('numero_receta');
        $medicoId = $request->input('medico_id');
        
        if (!$clienteId) {
            return ['error' => true, 'mensaje' => 'Cliente requerido para medicamentos controlados'];
        }

        // Verificar si requiere receta
        if ($medicamentoControlado->Requiere_Receta) {
            if (!$numeroReceta) {
                return [
                    'error' => true,
                    'mensaje' => 'Medicamento requiere receta médica',
                    'codigo_error' => 422
                ];
            }

            // Validar receta
            $validacionReceta = $this->validarReceta($numeroReceta, $medicoId, $clienteId, $producto->Codigo, $cantidad);
            if (!$validacionReceta['valida']) {
                return [
                    'error' => true,
                    'mensaje' => $validacionReceta['mensaje'],
                    'codigo_error' => 422
                ];
            }
        }

        // Verificar límites de venta
        $limiteVenta = $this->verificarLimiteVenta($producto->Codigo, $clienteId, $cantidad);
        if (!$limiteVenta['permitido']) {
            return [
                'error' => true,
                'mensaje' => $limiteVenta['mensaje'],
                'detalles' => $limiteVenta,
                'codigo_error' => 422
            ];
        }

        // Verificar stock disponible vs mínimo legal
        $stockDisponible = $this->getStockDisponible($producto->Codigo);
        $minimoLegal = $medicamentoControlado->Stock_Minimo ?? 0;
        
        if ($stockDisponible < $minimoLegal) {
            return [
                'error' => true,
                'mensaje' => "Stock insuficiente. Mínimo legal requerido: {$minimoLegal} unidades",
                'stock_actual' => $stockDisponible,
                'stock_minimo' => $minimoLegal,
                'codigo_error' => 422
            ];
        }

        // Generar registro de venta controlada
        $registroVenta = DB::table('Ventas_Controladas')->insertGetId([
            'Producto_id' => $producto->Codigo,
            'Cliente_id' => $clienteId,
            'Numero_Receta' => $numeroReceta,
            'Medico_id' => $medicoId,
            'Cantidad_Vendida' => $cantidad,
            'Fecha_Venta' => now(),
            'Usuario_id' => auth()->id(),
            'IP_Usuario' => $request->ip(),
            'Observaciones' => $request->input('observaciones')
        ]);

        return [
            'error' => false,
            'venta_autorizada' => true,
            'registro_venta_id' => $registroVenta,
            'cumple_regulaciones' => true,
            'stock_actual' => $stockDisponible,
            'stock_minimo_legal' => $minimoLegal
        ];
    }

    /**
     * Procesar dispensación en farmacia
     */
    private function procesarDispensacion(Request $request, object $producto, object $medicamentoControlado, float $cantidad): array
    {
        $recetaId = $request->input('receta_id');
        $pacienteId = $request->input('paciente_id');
        
        // Verificar autorización del farmacéutico
        $farmaceutico = DB::table('Accesoweb')
            ->where('Id', auth()->id())
            ->whereIn('Rol', ['Farmacéutico', 'Jefe Farmacia'])
            ->first();
            
        if (!$farmaceutico) {
            return [
                'error' => true,
                'mensaje' => 'Solo farmacéuticos autorizados pueden dispensar medicamentos controlados',
                'codigo_error' => 403
            ];
        }

        // Verificar receta válida
        if ($medicamentoControlado->Requiere_Receta && !$recetaId) {
            return [
                'error' => true,
                'mensaje' => 'Receta médica requerida para dispensación',
                'codigo_error' => 422
            ];
        }

        // Crear registro de dispensación
        $dispensacionId = DB::table('Dispensaciones_Controladas')->insertGetId([
            'Producto_id' => $producto->Codigo,
            'Receta_id' => $recetaId,
            'Paciente_id' => $pacienteId,
            'Farmaceutico_id' => auth()->id(),
            'Cantidad_Dispensada' => $cantidad,
            'Fecha_Dispensacion' => now(),
            'Estado' => 'Dispensado'
        ]);

        return [
            'error' => false,
            'dispensacion_autorizada' => true,
            'dispensacion_id' => $dispensacionId,
            'farmaceutico_responsable' => $farmaceutico->Nombre,
            'cumple_normativa' => true
        ];
    }

    /**
     * Procesar operaciones de inventario
     */
    private function procesarInventario(Request $request, object $producto, object $medicamentoControlado): array
    {
        $operacion = $request->input('operacion_inventario'); // conteo, ajuste, ingreso, salida
        $justificacion = $request->input('justificacion');
        
        if (!$operacion) {
            return ['error' => true, 'mensaje' => 'Tipo de operación de inventario requerido'];
        }

        // Operaciones sensibles requieren autorización especial
        $operacionesSensibles = ['ajuste', 'salida'];
        if (in_array($operacion, $operacionesSensibles) && !$justificacion) {
            return [
                'error' => true,
                'mensaje' => 'Justificación requerida para operaciones de inventario de medicamentos controlados'
            ];
        }

        // Crear registro de control de inventario
        $registroId = DB::table('Inventario_Controlado')->insertGetId([
            'Producto_id' => $producto->Codigo,
            'Operacion' => $operacion,
            'Usuario_id' => auth()->id(),
            'Fecha_Registro' => now(),
            'Justificacion' => $justificacion,
            'Estado' => 'Registrado'
        ]);

        return [
            'error' => false,
            'inventario_registrado' => true,
            'registro_id' => $registroId,
            'operacion' => $operacion,
            'requiere_seguimiento' => in_array($operacion, $operacionesSensibles)
        ];
    }

    /**
     * Procesar validación de prescripción
     */
    private function procesarPrescripcion(Request $request, object $producto, object $medicamentoControlado): array
    {
        $recetaId = $request->input('receta_id');
        $pacienteId = $request->input('paciente_id');
        $medicoId = $request->input('medico_id');
        
        if (!$recetaId || !$medicoId) {
            return [
                'error' => true,
                'mensaje' => 'Receta y médico son obligatorios para prescripción'
            ];
        }

        // Verificar médico autorizado
        $medicoAutorizado = $this->verificarMedicoAutorizado($medicoId, $producto->Codigo);
        if (!$medicoAutorizado['autorizado']) {
            return [
                'error' => true,
                'mensaje' => $medicoAutorizado['mensaje'],
                'codigo_error' => 403
            ];
        }

        // Verificar validez de la receta
        $validezReceta = $this->verificarValidezReceta($recetaId);
        if (!$validezReceta['valida']) {
            return [
                'error' => true,
                'mensaje' => $validezReceta['mensaje'],
                'codigo_error' => 422
            ];
        }

        return [
            'error' => false,
            'prescripcion_valida' => true,
            'medico_autorizado' => true,
            'receta_valida' => true,
            'dias_restantes' => $validezReceta['dias_restantes']
        ];
    }

    /**
     * Validaciones generales
     */
    private function validacionesGenerales(Request $request, object $producto, object $medicamentoControlado): array
    {
        // Verificar que el usuario tiene permisos
        $usuario = DB::table('Accesoweb')->where('Id', auth()->id())->first();
        
        if (!$usuario || !in_array($usuario->Rol, ['Administrador', 'Jefe Farmacia', 'Farmacéutico'])) {
            return [
                'error' => true,
                'mensaje' => 'Permisos insuficientes para manejar medicamentos controlados',
                'codigo_error' => 403
            ];
        }

        // Verificar período de retención de registros
        $periodoRetencion = $medicamentoControlado->Periodo_Retencion ?? 60; // meses
        $fechaUltimaAuditoria = DB::table('Auditoria_Controlados')
            ->where('Producto_id', $producto->Codigo)
            ->orderBy('Fecha_Auditoria', 'desc')
            ->value('Fecha_Auditoria');

        if ($fechaUltimaAuditoria) {
            $mesesUltimaAuditoria = now()->diffInMonths($fechaUltimaAuditoria);
            if ($mesesUltimaAuditoria > 6) {
                // Programar auditoría automática
                $this->programarAuditoria($producto->Codigo);
            }
        }

        return [
            'error' => false,
            'permisos_verificados' => true,
            'ultima_auditoria' => $fechaUltimaAuditoria,
            'requiere_auditoria' => $fechaUltimaAuditoria ? 
                now()->diffInMonths($fechaUltimaAuditoria) > 6 : true
        ];
    }

    /**
     * Validar receta médica
     */
    private function validarReceta(string $numeroReceta, ?string $medicoId, string $clienteId, string $productoId, float $cantidad): array
    {
        $receta = DB::table('Recetas_Medicas')
            ->where('Numero', $numeroReceta)
            ->where('Estado', 'Vigente')
            ->first();

        if (!$receta) {
            return ['valida' => false, 'mensaje' => 'Receta no encontrada o no vigente'];
        }

        // Verificar vigencia de la receta
        if ($receta->Fecha_Vencimiento < now()) {
            return ['valida' => false, 'mensaje' => 'Receta vencida'];
        }

        // Verificar límites de cantidad
        if ($receta->Cantidad_Maxima && $cantidad > $receta->Cantidad_Maxima) {
            return ['valida' => false, 'mensaje' => 'Cantidad excede la prescrita'];
        }

        return ['valida' => true, 'mensaje' => 'Receta válida'];
    }

    /**
     * Verificar límites de venta por cliente
     */
    private function verificarLimiteVenta(string $productoId, string $clienteId, float $cantidad): array
    {
        $medicamentoControlado = $this->esMedicamentoControlado($productoId);
        $limitePorVenta = $medicamentoControlado->Limite_Por_Venta ?? null;
        $limitePorMes = $medicamentoControlado->Limite_Por_Mes ?? null;

        // Verificar límite por venta
        if ($limitePorVenta && $cantidad > $limitePorVenta) {
            return [
                'permitido' => false,
                'mensaje' => "Cantidad excede el límite por venta ({$limitePorVenta})"
            ];
        }

        // Verificar límite mensual
        if ($limitePorMes) {
            $ventasMes = DB::table('Ventas_Controladas')
                ->where('Producto_id', $productoId)
                ->where('Cliente_id', $clienteId)
                ->whereMonth('Fecha_Venta', now()->month)
                ->whereYear('Fecha_Venta', now()->year)
                ->sum('Cantidad_Vendida');

            if ($ventasMes + $cantidad > $limitePorMes) {
                return [
                    'permitido' => false,
                    'mensaje' => "Límite mensual excedido. Disponible: " . ($limitePorMes - $ventasMes)
                ];
            }
        }

        return ['permitido' => true, 'mensaje' => 'Venta permitida'];
    }

    /**
     * Obtener stock disponible
     */
    private function getStockDisponible(string $productoId): float
    {
        $saldo = DB::table('Saldos')
            ->where('Producto', $productoId)
            ->value('Stock');

        return $saldo ?? 0;
    }

    /**
     * Verificar médico autorizado
     */
    private function verificarMedicoAutorizado(string $medicoId, string $productoId): array
    {
        $medico = DB::table('Medicos_Autorizados')
            ->where('Medico_id', $medicoId)
            ->where('Producto_id', $productoId)
            ->where('Vigente', true)
            ->first();

        if (!$medico) {
            return [
                'autorizado' => false,
                'mensaje' => 'Médico no autorizado para prescribir este medicamento'
            ];
        }

        return ['autorizado' => true, 'mensaje' => 'Médico autorizado'];
    }

    /**
     * Verificar validez de receta
     */
    private function verificarValidezReceta(string $recetaId): array
    {
        $receta = DB::table('Recetas_Medicas')
            ->where('Id', $recetaId)
            ->first();

        if (!$receta) {
            return ['valida' => false, 'mensaje' => 'Receta no encontrada'];
        }

        if ($receta->Fecha_Vencimiento < now()) {
            return ['valida' => false, 'mensaje' => 'Receta vencida'];
        }

        $diasRestantes = now()->diffInDays($receta->Fecha_Vencimiento, false);
        
        return [
            'valida' => true,
            'dias_restantes' => $diasRestantes
        ];
    }

    /**
     * Programar auditoría automática
     */
    private function programarAuditoria(string $productoId): void
    {
        DB::table('Auditorias_Programadas')->insert([
            'Producto_id' => $productoId,
            'Tipo_Auditoria' => 'Automatica',
            'Fecha_Programada' => now(),
            'Estado' => 'Programada',
            'Motivo' => 'Período de auditoría excedido'
        ]);
    }

    /**
     * Registrar operación de control
     */
    private function registrarOperacionControl(Request $request, object $producto, string $tipoOperacion, array $controlResult): void
    {
        DB::table('Log_Controles_Medicados')->insert([
            'Producto_id' => $producto->Codigo,
            'Operacion' => $tipoOperacion,
            'Usuario_id' => auth()->id(),
            'IP_Usuario' => $request->ip(),
            'User_Agent' => $request->userAgent(),
            'Resultado' => $controlResult['error'] ? 'Denegado' : 'Autorizado',
            'Detalles' => json_encode($controlResult),
            'Fecha_Operacion' => now()
        ]);

        Log::info("Control medicamento controlado: {$tipoOperacion}", [
            'producto' => $producto->Codigo,
            'usuario' => auth()->id(),
            'resultado' => $controlResult['error'] ? 'denegado' : 'autorizado'
        ]);
    }
}