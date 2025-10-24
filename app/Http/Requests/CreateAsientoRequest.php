<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAsientoRequest extends FormRequest
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
            // Información básica del asiento
            'numero_asiento' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-_]+$/'
            ],
            'fecha_asiento' => [
                'required',
                'date',
                'date_format:Y-m-d'
            ],
            'periodo_contable' => [
                'required',
                'string',
                'max:7',
                'regex:/^\d{4}\-\d{2}$/'
            ],

            // Tipo y categoría del asiento
            'tipo_asiento' => [
                'required',
                'string',
                Rule::in([
                    'Diario',
                    'Ingresos',
                    'Egresos',
                    'Bancos',
                    'Compras',
                    'Ventas',
                    'Costos',
                    'Ajustes',
                    'Cierre',
                    'Apertura',
                    'Provisiones',
                    'Depreciaciones'
                ])
            ],
            'categoria' => [
                'nullable',
                'string',
                Rule::in([
                    'Operativo',
                    'Financiero',
                    'Tributario',
                    'Administrativo',
                    'Extraordinario',
                    'Auditoria',
                    'Corrección'
                ])
            ],
            'subcategoria' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Descripción y glosa
            'glosa' => [
                'required',
                'string',
                'max:500'
            ],
            'detalle' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'referencia' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Vínculos con documentos
            'documento_tipo' => [
                'nullable',
                'string',
                Rule::in([
                    'Factura',
                    'Boleta',
                    'Recibo Honorarios',
                    'Guía Remisión',
                    'Nota Credito',
                    'Nota Debito',
                    'Carta Porte',
                    'Ticket',
                    'Contrato',
                    'Orden Compra',
                    'Otros'
                ])
            ],
            'documento_numero' => [
                'nullable',
                'string',
                'max:50'
            ],
            'documento_fecha' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            // Información del proveedor/cliente
            'proveedor_id' => [
                'nullable',
                'string',
                'exists:Proveedores,Codigo'
            ],
            'cliente_id' => [
                'nullable',
                'string',
                'exists:Clientes,Codigo'
            ],

            // Información contable y financiera
            'moneda' => [
                'required',
                'string',
                'size:3',
                Rule::in(['PEN', 'USD', 'EUR'])
            ],
            'tipo_cambio' => [
                'required',
                'numeric',
                'min:0.0001',
                'max:9999.9999'
            ],
            'total_debe' => [
                'required',
                'numeric',
                'min:0.01'
            ],
            'total_haber' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            // Detalles del asiento (líneas contables)
            'detalles' => [
                'required',
                'array',
                'min:2',
                'max:100'
            ],
            'detalles.*.cuenta_contable' => [
                'required',
                'string',
                'exists:PlanCuentas,Codigo'
            ],
            'detalles.*.descripcion' => [
                'required',
                'string',
                'max:200'
            ],
            'detalles.*.debe' => [
                'required',
                'numeric',
                'min:0'
            ],
            'detalles.*.haber' => [
                'required',
                'numeric',
                'min:0'
            ],
            'detalles.*.tipo_cambio' => [
                'nullable',
                'numeric',
                'min:0.0001',
                'max:9999.9999'
            ],
            'detalles.*.debe_usd' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'detalles.*.haber_usd' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            // Información adicional por detalle
            'detalles.*.centro_costo' => [
                'nullable',
                'string',
                'max:50'
            ],
            'detalles.*.area' => [
                'nullable',
                'string',
                'max:50'
            ],
            'detalles.*.proyecto' => [
                'nullable',
                'string',
                'max:50'
            ],
            'detalles.*.analitico' => [
                'nullable',
                'string',
                'max:100'
            ],
            'detalles.*.base_imponible' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'detalles.*.igv' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'detalles.*.total' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            // Información de productos/servicios
            'detalles.*.producto_id' => [
                'nullable',
                'string',
                'exists:Productos,Codigo'
            ],
            'detalles.*.cantidad' => [
                'nullable',
                'numeric',
                'min:0.001'
            ],
            'detalles.*.precio_unitario' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            // Información del usuario y fechas
            'usuario_responsable' => [
                'nullable',
                'string',
                'max:50'
            ],
            'fecha_creacion' => [
                'nullable',
                'date',
                'date_format:Y-m-d H:i:s'
            ],
            'fecha_aprobacion' => [
                'nullable',
                'date',
                'date_format:Y-m-d H:i:s'
            ],

            // Estados y validaciones
            'estado' => [
                'nullable',
                'string',
                Rule::in(['Borrador', 'Pendiente', 'Aprobado', 'Rechazado', 'Contabilizado', 'Anulado'])
            ],
            'nivel_aprobacion' => [
                'nullable',
                'integer',
                'min:1',
                'max:5'
            ],
            'requiere_aprobacion' => [
                'boolean'
            ],

            // Información adicional
            'observaciones' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'adjuntos' => [
                'nullable',
                'array'
            ],
            'adjuntos.*' => [
                'string',
                'max:500'
            ],

            // Campos de auditoría
            'created_by' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'approved_by' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'updated_by' => [
                'nullable',
                'integer',
                'min:1'
            ],

            // Configuraciones
            'generar_reporte' => [
                'boolean'
            ],
            'incluir_auxiliar' => [
                'boolean'
            ],
            'mostrar_detalle' => [
                'boolean'
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
            'numero_asiento.required' => 'El número de asiento es obligatorio',
            'numero_asiento.regex' => 'El número de asiento contiene caracteres no válidos',
            'fecha_asiento.required' => 'La fecha del asiento es obligatoria',
            'fecha_asiento.date' => 'La fecha del asiento debe ser una fecha válida',
            'periodo_contable.required' => 'El período contable es obligatorio',
            'periodo_contable.regex' => 'El formato del período contable debe ser YYYY-MM',

            // Mensajes para tipo y categoría
            'tipo_asiento.required' => 'El tipo de asiento es obligatorio',
            'tipo_asiento.in' => 'El tipo de asiento seleccionado no es válido',
            'categoria.in' => 'La categoría seleccionada no es válida',
            'glosa.required' => 'La glosa es obligatoria',

            // Mensajes para documentos
            'documento_tipo.in' => 'El tipo de documento seleccionado no es válido',
            'documento_numero.required' => 'El número de documento es obligatorio cuando se especifica el tipo',
            'documento_fecha.date' => 'La fecha del documento debe ser una fecha válida',

            // Mensajes para vínculos
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'cliente_id.exists' => 'El cliente seleccionado no existe',

            // Mensajes para información financiera
            'moneda.required' => 'La moneda es obligatoria',
            'moneda.in' => 'La moneda seleccionada no es válida',
            'tipo_cambio.required' => 'El tipo de cambio es obligatorio',
            'tipo_cambio.min' => 'El tipo de cambio debe ser mayor a 0',
            'total_debe.required' => 'El total debe es obligatorio',
            'total_haber.required' => 'El total haber es obligatorio',
            'total_debe.numeric' => 'El total debe debe ser un número',
            'total_haber.numeric' => 'El total haber debe ser un número',

            // Mensajes para detalles
            'detalles.required' => 'Debe incluir al menos un detalle',
            'detalles.min' => 'Debe incluir al menos 2 detalles',
            'detalles.max' => 'No puede incluir más de 100 detalles',
            'detalles.*.cuenta_contable.required' => 'La cuenta contable es obligatoria en todos los detalles',
            'detalles.*.cuenta_contable.exists' => 'La cuenta contable seleccionada no existe',
            'detalles.*.descripcion.required' => 'La descripción es obligatoria en todos los detalles',
            'detalles.*.debe.required' => 'El valor debe es obligatorio en todos los detalles',
            'detalles.*.haber.required' => 'El valor haber es obligatorio en todos los detalles',
            'detalles.*.debe.min' => 'El valor debe no puede ser negativo',
            'detalles.*.haber.min' => 'El valor haber no puede ser negativo',

            // Mensajes para estados
            'estado.in' => 'El estado seleccionado no es válido',
            'nivel_aprobacion.min' => 'El nivel de aprobación mínimo es 1',
            'nivel_aprobacion.max' => 'El nivel de aprobación máximo es 5',

            // Mensajes para campos adicionales
            'producto_id.exists' => 'El producto seleccionado no existe',
            'cantidad.numeric' => 'La cantidad debe ser un número',
            'cantidad.min' => 'La cantidad mínima es 0.001',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'base_imponible.numeric' => 'La base imponible debe ser un número',
            'igv.numeric' => 'El IGV debe ser un número',
            'total.numeric' => 'El total debe ser un número',

            // Mensajes generales
            'required' => 'Este campo es obligatorio',
            'string' => 'Este campo debe ser texto',
            'numeric' => 'Este campo debe ser numérico',
            'integer' => 'Este campo debe ser un número entero',
            'max' => 'Este campo no puede exceder :max caracteres',
            'min' => 'Este campo debe ser mayor a :min',
            'boolean' => 'Este campo debe ser verdadero o falso',
            'date_format' => 'El formato de fecha debe ser YYYY-MM-DD'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Formatear período contable
        if ($this->has('fecha_asiento')) {
            $fecha = \Carbon\Carbon::parse($this->fecha_asiento);
            $periodo = $fecha->format('Y-m');
            
            $this->merge([
                'periodo_contable' => $periodo
            ]);
        }

        // Valores por defecto
        $this->merge([
            'moneda' => $this->moneda ?: 'PEN',
            'tipo_cambio' => $this->tipo_cambio ?: 1.0000,
            'estado' => $this->estado ?: 'Borrador',
            'requiere_aprobacion' => $this->boolean('requiere_aprobacion', false),
            'generar_reporte' => $this->boolean('generar_reporte', true),
            'incluir_auxiliar' => $this->boolean('incluir_auxiliar', false),
            'mostrar_detalle' => $this->boolean('mostrar_detalle', true),
            'fecha_creacion' => $this->fecha_creacion ?: now()->format('Y-m-d H:i:s')
        ]);

        // Auto-calcular totales si no se proporcionan
        if ($this->has('detalles')) {
            $debeTotal = 0;
            $haberTotal = 0;

            foreach ($this->detalles as $detalle) {
                if (isset($detalle['debe']) && is_numeric($detalle['debe'])) {
                    $debeTotal += (float)$detalle['debe'];
                }
                if (isset($detalle['haber']) && is_numeric($detalle['haber'])) {
                    $haberTotal += (float)$detalle['haber'];
                }
            }

            $this->merge([
                'total_debe' => $debeTotal,
                'total_haber' => $haberTotal
            ]);
        }

        // Validar balance básico
        if ($this->has('total_debe') && $this->has('total_haber')) {
            $diferencia = abs($this->total_debe - $this->total_haber);
            if ($diferencia > 0.01) {
                // Esta validación se hará en el método after()
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
            'numero_asiento' => 'número de asiento',
            'fecha_asiento' => 'fecha del asiento',
            'periodo_contable' => 'período contable',
            'tipo_asiento' => 'tipo de asiento',
            'glosa' => 'glosa',
            'documento_numero' => 'número de documento',
            'documento_fecha' => 'fecha del documento',
            'moneda' => 'moneda',
            'tipo_cambio' => 'tipo de cambio',
            'total_debe' => 'total debe',
            'total_haber' => 'total haber',
            'detalles.*.cuenta_contable' => 'cuenta contable',
            'detalles.*.descripcion' => 'descripción',
            'detalles.*.debe' => 'valor debe',
            'detalles.*.haber' => 'valor haber',
            'detalles.*.centro_costo' => 'centro de costo',
            'detalles.*.area' => 'área',
            'detalles.*.proyecto' => 'proyecto',
            'detalles.*.producto_id' => 'producto',
            'detalles.*.cantidad' => 'cantidad',
            'detalles.*.precio_unitario' => 'precio unitario'
        ];
    }

    /**
     * Perform additional validation after the main rules.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                // Validar balance contable (Debe = Haber)
                if ($this->has('total_debe') && $this->has('total_haber')) {
                    $diferencia = abs($this->total_debe - $this->total_haber);
                    if ($diferencia > 0.01) {
                        $validator->errors()->add(
                            'balance', 
                            'El asiento no está balanceado. Diferencia: ' . number_format($diferencia, 2)
                        );
                    }
                }

                // Validar que al menos una línea tenga debe > 0 y otra haber > 0
                if ($this->has('detalles')) {
                    $tieneDebe = false;
                    $tieneHaber = false;

                    foreach ($this->detalles as $detalle) {
                        if (isset($detalle['debe']) && $detalle['debe'] > 0) {
                            $tieneDebe = true;
                        }
                        if (isset($detalle['haber']) && $detalle['haber'] > 0) {
                            $tieneHaber = true;
                        }
                    }

                    if (!$tieneDebe || !$tieneHaber) {
                        $validator->errors()->add(
                            'detalles',
                            'El asiento debe tener al menos una línea con debe > 0 y una con haber > 0'
                        );
                    }
                }

                // Validar fechas lógicas
                if ($this->has('fecha_asiento') && $this->has('documento_fecha')) {
                    $fechaAsiento = \Carbon\Carbon::parse($this->fecha_asiento);
                    $fechaDocumento = \Carbon\Carbon::parse($this->documento_fecha);

                    if ($fechaDocumento->greaterThan($fechaAsiento)) {
                        $validator->errors()->add(
                            'documento_fecha',
                            'La fecha del documento no puede ser posterior a la fecha del asiento'
                        );
                    }
                }

                // Validar que si hay proveedor o cliente, esté relacionado con el documento
                if ($this->has('proveedor_id') && !$this->has('documento_numero')) {
                    $validator->errors()->add(
                        'documento_numero',
                        'El número de documento es obligatorio cuando se especifica un proveedor'
                    );
                }

                if ($this->has('cliente_id') && !$this->has('documento_numero')) {
                    $validator->errors()->add(
                        'documento_numero',
                        'El número de documento es obligatorio cuando se especifica un cliente'
                    );
                }
            }
        ];
    }
}