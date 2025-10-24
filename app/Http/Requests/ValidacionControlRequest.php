<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidacionControlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Aquí puedes agregar lógica de autorización específica
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Información básica del control
            'tipo_control' => [
                'required',
                'string',
                Rule::in([
                    'Inventario',
                    'Caja',
                    'Banco',
                    'Clientes',
                    'Proveedores',
                    'Activos Fijos',
                    'Precios',
                    'Compras',
                    'Ventas',
                    'Contabilidad',
                    'SUNAT',
                    'Stock',
                    'Lotes',
                    'Vencimientos',
                    'Operativo'
                ])
            ],
            'subtipo_control' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Identificación del control
            'codigo_control' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-_]+$/'
            ],
            'nombre_control' => [
                'required',
                'string',
                'max:200'
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:500'
            ],

            // Información de fechas
            'fecha_inicio' => [
                'required',
                'date',
                'date_format:Y-m-d'
            ],
            'fecha_fin' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after:fecha_inicio'
            ],
            'fecha_programada' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],
            'fecha_realizacion' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            // Estado y clasificación
            'estado' => [
                'required',
                'string',
                Rule::in([
                    'Programado',
                    'En Proceso',
                    'Completado',
                    'Suspendido',
                    'Cancelado',
                    'Rechazado',
                    'Aprobado',
                    'Pendiente Revisión'
                ])
            ],
            'prioridad' => [
                'required',
                'string',
                Rule::in(['Alta', 'Media', 'Baja', 'Crítica'])
            ],
            'frecuencia' => [
                'nullable',
                'string',
                Rule::in([
                    'Diaria',
                    'Semanal',
                    'Quincenal',
                    'Mensual',
                    'Bimestral',
                    'Trimestral',
                    'Semestral',
                    'Anual',
                    'Esporádico',
                    'Eventual'
                ])
            ],

            // Responsable y asignaciones
            'responsable_id' => [
                'required',
                'integer',
                'min:1'
            ],
            'supervisor_id' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'equipo_trabajo' => [
                'nullable',
                'array'
            ],
            'equipo_trabajo.*' => [
                'integer',
                'min:1'
            ],

            // Información del área/centro de costo
            'area' => [
                'nullable',
                'string',
                'max:100'
            ],
            'centro_costo' => [
                'nullable',
                'string',
                'max:50'
            ],
            'sucursal' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Criterios y parámetros de validación
            'criterios_validacion' => [
                'required',
                'array',
                'min:1'
            ],
            'criterios_validacion.*.campo' => [
                'required',
                'string',
                'max:100'
            ],
            'criterios_validacion.*.tipo_validacion' => [
                'required',
                'string',
                Rule::in([
                    'Rango',
                    'Lista',
                    'Patrón',
                    'Existencia',
                    'Integridad',
                    'Consistencia',
                    'Completitud',
                    'Precisión',
                    'Vigencia',
                    'Autorización'
                ])
            ],
            'criterios_validacion.*.valor_minimo' => [
                'nullable',
                'string'
            ],
            'criterios_validacion.*.valor_maximo' => [
                'nullable',
                'string'
            ],
            'criterios_validacion.*.valores_permitidos' => [
                'nullable',
                'array'
            ],
            'criterios_validacion.*.patron_regex' => [
                'nullable',
                'string'
            ],
            'criterios_validacion.*.mensaje_error' => [
                'nullable',
                'string',
                'max:300'
            ],

            // Configuración de la validación
            'metodologia' => [
                'nullable',
                'string',
                'max:500'
            ],
            'herramientas' => [
                'nullable',
                'array'
            ],
            'herramientas.*' => [
                'string',
                'max:100'
            ],
            'muestra_porcentaje' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ],
            'muestra_minima' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'muestra_maxima' => [
                'nullable',
                'integer',
                'min:1'
            ],

            // Resultados y hallazgos
            'total_registros' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'registros_validados' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'errores_encontrados' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'advertencias' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'porcentaje_cumplimiento' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],

            // Detalles de errores y discrepancias
            'detalle_errores' => [
                'nullable',
                'array'
            ],
            'detalle_errores.*.registro_id' => [
                'required_with:detalle_errores',
                'string',
                'max:100'
            ],
            'detalle_errores.*.campo' => [
                'required_with:detalle_errores',
                'string',
                'max:100'
            ],
            'detalle_errores.*.valor_encontrado' => [
                'required_with:detalle_errores',
                'string',
                'max:500'
            ],
            'detalle_errores.*.valor_esperado' => [
                'nullable',
                'string',
                'max:500'
            ],
            'detalle_errores.*.tipo_error' => [
                'required_with:detalle_errores',
                'string',
                Rule::in([
                    'Campo Faltante',
                    'Valor Inválido',
                    'Formato Incorrecto',
                    'Rango Fuera de Límites',
                    'Inconsistencia',
                    'Duplicado',
                    'Referencia Inválida',
                    'Autorización Faltante',
                    'Vencido',
                    'Otros'
                ])
            ],
            'detalle_errores.*.severidad' => [
                'required_with:detalle_errores',
                'string',
                Rule::in(['Crítica', 'Alta', 'Media', 'Baja'])
            ],
            'detalle_errores.*.descripcion' => [
                'nullable',
                'string',
                'max:1000'
            ],

            // Acciones correctivas
            'acciones_correctivas' => [
                'nullable',
                'array'
            ],
            'acciones_correctivas.*.descripcion' => [
                'required_with:acciones_correctivas',
                'string',
                'max:500'
            ],
            'acciones_correctivas.*.responsable' => [
                'required_with:acciones_correctivas',
                'integer',
                'min:1'
            ],
            'acciones_correctivas.*.fecha_limite' => [
                'required_with:acciones_correctivas',
                'date',
                'date_format:Y-m-d'
            ],
            'acciones_correctivas.*.estado' => [
                'nullable',
                'string',
                Rule::in(['Pendiente', 'En Proceso', 'Completado', 'Cancelado'])
            ],
            'acciones_correctivas.*.costo_estimado' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            // Información de documentación
            'documentos_soporte' => [
                'nullable',
                'array'
            ],
            'documentos_soporte.*' => [
                'string',
                'max:500'
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'recomendaciones' => [
                'nullable',
                'string',
                'max:1000'
            ],

            // Aprobaciones y validaciones
            'aprobado_por' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'fecha_aprobacion' => [
                'nullable',
                'date',
                'date_format:Y-m-d H:i:s'
            ],
            'observaciones_aprobacion' => [
                'nullable',
                'string',
                'max:1000'
            ],

            // Configuraciones adicionales
            'generar_reporte' => [
                'boolean'
            ],
            'enviar_notificaciones' => [
                'boolean'
            ],
            'requiere_seguimiento' => [
                'boolean'
            ],
            'automatizar_proceso' => [
                'boolean'
            ],

            // Campos de auditoría
            'created_by' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'updated_by' => [
                'nullable',
                'integer',
                'min:1'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Mensajes para información básica
            'tipo_control.required' => 'El tipo de control es obligatorio',
            'tipo_control.in' => 'El tipo de control seleccionado no es válido',
            'codigo_control.required' => 'El código de control es obligatorio',
            'codigo_control.regex' => 'El código de control contiene caracteres no válidos',
            'nombre_control.required' => 'El nombre del control es obligatorio',

            // Mensajes para fechas
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'fecha_realizacion.date' => 'La fecha de realización debe ser una fecha válida',

            // Mensajes para estados y clasificación
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado seleccionado no es válido',
            'prioridad.required' => 'La prioridad es obligatoria',
            'prioridad.in' => 'La prioridad seleccionada no es válida',
            'frecuencia.in' => 'La frecuencia seleccionada no es válida',

            // Mensajes para responsables
            'responsable_id.required' => 'El responsable es obligatorio',
            'responsable_id.min' => 'El responsable debe ser un ID válido',
            'supervisor_id.min' => 'El supervisor debe ser un ID válido',
            'equipo_trabajo.*.min' => 'Los miembros del equipo deben tener IDs válidos',

            // Mensajes para criterios de validación
            'criterios_validacion.required' => 'Debe incluir al menos un criterio de validación',
            'criterios_validacion.*.campo.required' => 'El campo es obligatorio en todos los criterios',
            'criterios_validacion.*.tipo_validacion.required' => 'El tipo de validación es obligatorio',
            'criterios_validacion.*.tipo_validacion.in' => 'El tipo de validación seleccionado no es válido',

            // Mensajes para muestreo
            'muestra_porcentaje.min' => 'El porcentaje de muestra mínimo es 1%',
            'muestra_porcentaje.max' => 'El porcentaje de muestra máximo es 100%',
            'muestra_minima.min' => 'La muestra mínima debe ser mayor a 0',
            'muestra_maxima.min' => 'La muestra máxima debe ser mayor a 0',

            // Mensajes para resultados
            'total_registros.integer' => 'El total de registros debe ser un número entero',
            'registros_validados.integer' => 'Los registros validados deben ser un número entero',
            'errores_encontrados.integer' => 'Los errores encontrados deben ser un número entero',
            'advertencias.integer' => 'Las advertencias deben ser un número entero',
            'porcentaje_cumplimiento.min' => 'El porcentaje de cumplimiento mínimo es 0%',
            'porcentaje_cumplimiento.max' => 'El porcentaje de cumplimiento máximo es 100%',

            // Mensajes para errores detallados
            'detalle_errores.*.registro_id.required_with' => 'El ID del registro es obligatorio para errores',
            'detalle_errores.*.campo.required_with' => 'El campo es obligatorio para errores',
            'detalle_errores.*.valor_encontrado.required_with' => 'El valor encontrado es obligatorio para errores',
            'detalle_errores.*.tipo_error.required_with' => 'El tipo de error es obligatorio',
            'detalle_errores.*.tipo_error.in' => 'El tipo de error seleccionado no es válido',
            'detalle_errores.*.severidad.required_with' => 'La severidad es obligatoria',
            'detalle_errores.*.severidad.in' => 'La severidad seleccionada no es válida',

            // Mensajes para acciones correctivas
            'acciones_correctivas.*.descripcion.required_with' => 'La descripción es obligatoria para acciones',
            'acciones_correctivas.*.responsable.required_with' => 'El responsable es obligatorio para acciones',
            'acciones_correctivas.*.fecha_limite.required_with' => 'La fecha límite es obligatoria para acciones',
            'acciones_correctivas.*.fecha_limite.date' => 'La fecha límite debe ser una fecha válida',
            'acciones_correctivas.*.costo_estimado.min' => 'El costo estimado no puede ser negativo',
            'acciones_correctivas.*.estado.in' => 'El estado de la acción no es válido',

            // Mensajes para aprobaciones
            'aprobado_por.min' => 'El aprobador debe tener un ID válido',
            'fecha_aprobacion.date' => 'La fecha de aprobación debe ser una fecha válida',

            // Mensajes generales
            'required' => 'Este campo es obligatorio',
            'string' => 'Este campo debe ser texto',
            'integer' => 'Este campo debe ser un número entero',
            'numeric' => 'Este campo debe ser numérico',
            'array' => 'Este campo debe ser una lista',
            'max' => 'Este campo no puede exceder :max caracteres',
            'min' => 'Este campo debe ser mayor a :min',
            'boolean' => 'Este campo debe ser verdadero o falso',
            'required_with' => 'Este campo es obligatorio cuando :field está presente'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Formatear código de control
        if ($this->has('codigo_control')) {
            $this->merge([
                'codigo_control' => strtoupper(trim($this->codigo_control))
            ]);
        }

        // Valores por defecto
        $this->merge([
            'estado' => $this->estado ?: 'Programado',
            'prioridad' => $this->prioridad ?: 'Media',
            'frecuencia' => $this->frecuencia ?: 'Mensual',
            'generar_reporte' => $this->boolean('generar_reporte', true),
            'enviar_notificaciones' => $this->boolean('enviar_notificaciones', false),
            'requiere_seguimiento' => $this->boolean('requiere_seguimiento', true),
            'automatizar_proceso' => $this->boolean('automatizar_proceso', false)
        ]);

        // Auto-calcular porcentaje de cumplimiento
        if ($this->has('total_registros') && $this->has('registros_validados')) {
            if ($this->total_registros > 0) {
                $porcentaje = ($this->registros_validados / $this->total_registros) * 100;
                $this->merge([
                    'porcentaje_cumplimiento' => round($porcentaje, 2)
                ]);
            }
        }

        // Validar fechas lógicas
        if ($this->has('fecha_programada') && $this->has('fecha_inicio')) {
            $fechaProgramada = \Carbon\Carbon::parse($this->fecha_programada);
            $fechaInicio = \Carbon\Carbon::parse($this->fecha_inicio);
            
            if ($fechaProgramada->lessThan($fechaInicio)) {
                // Se registrará una advertencia en el método after()
            }
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tipo_control' => 'tipo de control',
            'codigo_control' => 'código de control',
            'nombre_control' => 'nombre del control',
            'descripcion' => 'descripción',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'fecha_programada' => 'fecha programada',
            'fecha_realizacion' => 'fecha de realización',
            'responsable_id' => 'responsable',
            'supervisor_id' => 'supervisor',
            'equipo_trabajo' => 'equipo de trabajo',
            'criterios_validacion' => 'criterios de validación',
            'muestra_porcentaje' => 'porcentaje de muestra',
            'muestra_minima' => 'muestra mínima',
            'muestra_maxima' => 'muestra máxima',
            'total_registros' => 'total de registros',
            'registros_validados' => 'registros validados',
            'errores_encontrados' => 'errores encontrados',
            'porcentaje_cumplimiento' => 'porcentaje de cumplimiento',
            'detalle_errores' => 'detalle de errores',
            'acciones_correctivas' => 'acciones correctivas',
            'documentos_soporte' => 'documentos de soporte',
            'observaciones' => 'observaciones',
            'recomendaciones' => 'recomendaciones',
            'aprobado_por' => 'aprobado por',
            'fecha_aprobacion' => 'fecha de aprobación'
        ];
    }

    /**
     * Perform additional validation after the main rules.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                // Validar consistencia de resultados
                if ($this->has('registros_validados') && $this->has('total_registros')) {
                    if ($this->registros_validados > $this->total_registros) {
                        $validator->errors()->add(
                            'registros_validados',
                            'Los registros validados no pueden ser mayores al total de registros'
                        );
                    }
                }

                // Validar que los errores encontrados sean consistentes
                if ($this->has('errores_encontrados') && $this->has('total_registros')) {
                    if ($this->errores_encontrados > $this->total_registros) {
                        $validator->errors()->add(
                            'errores_encontrados',
                            'Los errores encontrados no pueden ser mayores al total de registros'
                        );
                    }
                }

                // Validar fechas de acciones correctivas
                if ($this->has('acciones_correctivas')) {
                    foreach ($this->acciones_correctivas as $index => $accion) {
                        if (isset($accion['fecha_limite']) && isset($this->fecha_fin)) {
                            $fechaLimite = \Carbon\Carbon::parse($accion['fecha_limite']);
                            $fechaFin = \Carbon\Carbon::parse($this->fecha_fin);
                            
                            if ($fechaLimite->isAfter($fechaFin)) {
                                $validator->errors()->add(
                                    "acciones_correctivas.{$index}.fecha_limite",
                                    "La fecha límite de la acción {$index} no puede ser posterior a la fecha de fin del control"
                                );
                            }
                        }
                    }
                }

                // Validar que si hay errores críticos, la prioridad no sea baja
                if ($this->has('detalle_errores') && $this->has('prioridad')) {
                    $tieneCriticos = false;
                    foreach ($this->detalle_errores as $error) {
                        if (isset($error['severidad']) && $error['severidad'] === 'Crítica') {
                            $tieneCriticos = true;
                            break;
                        }
                    }

                    if ($tieneCriticos && $this->prioridad === 'Baja') {
                        $validator->errors()->add(
                            'prioridad',
                            'Cuando se encuentran errores críticos, la prioridad no puede ser baja'
                        );
                    }
                }

                // Validar coherencia de fechas
                if ($this->has('fecha_programada') && $this->has('fecha_inicio')) {
                    $fechaProgramada = \Carbon\Carbon::parse($this->fecha_programada);
                    $fechaInicio = \Carbon\Carbon::parse($this->fecha_inicio);
                    
                    if ($fechaProgramada->lessThan($fechaInicio)) {
                        $validator->errors()->add(
                            'fecha_programada',
                            'La fecha programada no puede ser anterior a la fecha de inicio'
                        );
                    }
                }

                // Validar que si está completado, tenga fecha de realización
                if ($this->estado === 'Completado' && !$this->has('fecha_realizacion')) {
                    $validator->errors()->add(
                        'fecha_realizacion',
                        'La fecha de realización es obligatoria cuando el estado es Completado'
                    );
                }

                // Validar que si está aprobado, tenga aprobador
                if ($this->estado === 'Aprobado' && !$this->has('aprobado_por')) {
                    $validator->errors()->add(
                        'aprobado_por',
                        'El aprobador es obligatorio cuando el estado es Aprobado'
                    );
                }
            }
        ];
    }
}