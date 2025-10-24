<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClienteRequest extends FormRequest
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
        $clienteId = $this->route('cliente');

        return [
            // Información básica de identificación
            'codigo' => [
                'required',
                'string',
                'max:15',
                'regex:/^[A-Z0-9-_]+$/',
                Rule::unique('Clientes', 'Codigo')->ignore($clienteId)
            ],
            'tipo_documento' => [
                'required',
                'string',
                Rule::in([
                    'DNI', 
                    'RUC', 
                    'CE', 
                    'PASAPORTE', 
                    'OTROS'
                ])
            ],
            'numero_documento' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9A-Z]+$/'
            ],

            // Información comercial
            'razon_social' => [
                'required',
                'string',
                'max:500'
            ],
            'nombre_comercial' => [
                'nullable',
                'string',
                'max:500'
            ],

            // Información de contacto
            'direccion' => [
                'required',
                'string',
                'max:500'
            ],
            'distrito' => [
                'nullable',
                'string',
                'max:100'
            ],
            'provincia' => [
                'nullable',
                'string',
                'max:100'
            ],
            'departamento' => [
                'nullable',
                'string',
                'max:100'
            ],
            'pais' => [
                'nullable',
                'string',
                'max:100',
                Rule::in(['PERU', 'ECUADOR', 'COLOMBIA', 'CHILE', 'ARGENTINA', 'BOLIVIA', 'VENEZUELA', 'BRASIL', 'URUGUAY', 'PARAGUAY', 'OTROS'])
            ],

            // Información fiscal
            'condicion_agente_retencion' => [
                'boolean'
            ],
            'porcentaje_retencion' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'sujeto_retencion' => [
                'boolean'
            ],
            'agente_retencion' => [
                'boolean'
            ],

            // Información de contacto
            'telefono' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9+\-\s\(\)]+$/'
            ],
            'celular' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9+\-\s\(\)]+$/'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'pagina_web' => [
                'nullable',
                'url',
                'max:255'
            ],

            // Información comercial
            'lista_precios' => [
                'nullable',
                'string',
                'max:20'
            ],
            'credito_aprobado' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'credito_utilizado' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'dias_credito' => [
                'nullable',
                'integer',
                'min:0',
                'max:365'
            ],
            'forma_pago' => [
                'nullable',
                'string',
                Rule::in([
                    'Contado',
                    'Crédito',
                    'Transferencia',
                    'Cheque',
                    'Efectivo',
                    'Tarjeta Débito',
                    'Tarjeta Crédito',
                    'Mixto'
                ])
            ],

            // Categorización
            'tipo_cliente' => [
                'nullable',
                'string',
                Rule::in([
                    'Persona Natural',
                    'Persona Jurídica',
                    'Institución',
                    'Gobierno',
                    'ONG',
                    'Otros'
                ])
            ],
            'segmento' => [
                'nullable',
                'string',
                Rule::in([
                    'VIP',
                    'Preferente',
                    'Regular',
                    'Potencial',
                    'Inactivo'
                ])
            ],
            'canal_venta' => [
                'nullable',
                'string',
                Rule::in([
                    'Presencial',
                    'Telefónico',
                    'Online',
                    'Distribuidor',
                    'Mayorista',
                    'Retail'
                ])
            ],
            'zona_ventas' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Información bancaria
            'banco_cuenta' => [
                'nullable',
                'string',
                'max:100'
            ],
            'numero_cuenta' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9A-Z\-]+$/'
            ],
            'tipo_cuenta' => [
                'nullable',
                'string',
                Rule::in(['Ahorro', 'Corriente', 'CTS', 'Inversión'])
            ],
            'moneda_cuenta' => [
                'nullable',
                'string',
                Rule::in(['PEN', 'USD', 'EUR'])
            ],

            // Información del vendedor
            'vendedor_asignado' => [
                'nullable',
                'string',
                'max:50'
            ],
            'fecha_asignacion' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            // Estados y fechas
            'estado' => [
                'nullable',
                'string',
                Rule::in(['Activo', 'Inactivo', 'Suspendido', 'Bloqueado', 'Prospecto'])
            ],
            'fecha_registro' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],
            'fecha_ultima_compra' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],
            'fecha_ultima_visita' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            // Información adicional
            'observaciones' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'referencias' => [
                'nullable',
                'string',
                'max:500'
            ],
            'ubicacion_gps' => [
                'nullable',
                'string',
                'max:100'
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
            // Mensajes para identificación
            'codigo.required' => 'El código del cliente es obligatorio',
            'codigo.unique' => 'Este código de cliente ya existe',
            'codigo.regex' => 'El código solo puede contener letras, números, guiones y guiones bajos',
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.regex' => 'El número de documento contiene caracteres no válidos',

            // Mensajes para información comercial
            'razon_social.required' => 'La razón social es obligatoria',
            'direccion.required' => 'La dirección es obligatoria',

            // Mensajes para contacto
            'email.email' => 'El formato del email no es válido',
            'pagina_web.url' => 'La página web debe ser una URL válida',
            'telefono.regex' => 'El formato del teléfono no es válido',
            'celular.regex' => 'El formato del celular no es válido',

            // Mensajes para información comercial
            'credito_aprobado.numeric' => 'El crédito aprobado debe ser un número',
            'credito_aprobado.min' => 'El crédito aprobado no puede ser negativo',
            'credito_utilizado.numeric' => 'El crédito utilizado debe ser un número',
            'credito_utilizado.min' => 'El crédito utilizado no puede ser negativo',
            'dias_credito.integer' => 'Los días de crédito deben ser un número entero',
            'dias_credito.max' => 'Los días de crédito no pueden exceder 365',
            'porcentaje_retencion.numeric' => 'El porcentaje de retención debe ser un número',
            'porcentaje_retencion.min' => 'El porcentaje de retención no puede ser negativo',
            'porcentaje_retencion.max' => 'El porcentaje de retención no puede exceder 100',

            // Mensajes para categorización
            'tipo_cliente.in' => 'El tipo de cliente seleccionado no es válido',
            'segmento.in' => 'El segmento seleccionado no es válido',
            'canal_venta.in' => 'El canal de venta seleccionado no es válido',
            'forma_pago.in' => 'La forma de pago seleccionada no es válida',

            // Mensajes para información bancaria
            'numero_cuenta.regex' => 'El número de cuenta contiene caracteres no válidos',
            'tipo_cuenta.in' => 'El tipo de cuenta seleccionado no es válido',
            'moneda_cuenta.in' => 'La moneda de la cuenta seleccionada no es válida',

            // Mensajes para fechas
            'fecha_registro.date' => 'La fecha de registro debe ser una fecha válida',
            'fecha_ultima_compra.date' => 'La fecha de última compra debe ser una fecha válida',
            'fecha_ultima_visita.date' => 'La fecha de última visita debe ser una fecha válida',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser una fecha válida',

            // Mensajes para estados
            'estado.in' => 'El estado seleccionado no es válido',

            // Mensajes generales
            'required' => 'Este campo es obligatorio',
            'string' => 'Este campo debe ser texto',
            'numeric' => 'Este campo debe ser numérico',
            'integer' => 'Este campo debe ser un número entero',
            'max' => 'Este campo no puede exceder :max caracteres',
            'min' => 'Este campo debe ser mayor a :min',
            'boolean' => 'Este campo debe ser verdadero o falso'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear datos antes de la validación
        if ($this->has('numero_documento')) {
            $this->merge([
                'numero_documento' => strtoupper(trim($this->numero_documento))
            ]);
        }

        if ($this->has('razon_social')) {
            $this->merge([
                'razon_social' => trim($this->razon_social)
            ]);
        }

        if ($this->has('nombre_comercial')) {
            $this->merge([
                'nombre_comercial' => trim($this->nombre_comercial)
            ]);
        }

        if ($this->has('codigo')) {
            $this->merge([
                'codigo' => strtoupper(trim($this->codigo))
            ]);
        }

        // Valores por defecto
        $this->merge([
            'pais' => $this->pais ?: 'PERU',
            'condicion_agente_retencion' => $this->boolean('condicion_agente_retencion', false),
            'sujeto_retencion' => $this->boolean('sujeto_retencion', false),
            'agente_retencion' => $this->boolean('agente_retencion', false),
            'estado' => $this->estado ?: 'Activo',
            'tipo_cliente' => $this->tipo_cliente ?: 'Persona Natural',
            'segmento' => $this->segmento ?: 'Regular',
            'forma_pago' => $this->forma_pago ?: 'Contado',
            'fecha_registro' => $this->fecha_registro ?: now()->format('Y-m-d')
        ]);

        // Auto-calcular crédito utilizado si no se proporciona
        if ($this->has('credito_aprobado') && !$this->has('credito_utilizado')) {
            $this->merge([
                'credito_utilizado' => 0
            ]);
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
            'codigo' => 'código del cliente',
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'razon_social' => 'razón social',
            'nombre_comercial' => 'nombre comercial',
            'direccion' => 'dirección',
            'credito_aprobado' => 'crédito aprobado',
            'credito_utilizado' => 'crédito utilizado',
            'dias_credito' => 'días de crédito',
            'forma_pago' => 'forma de pago',
            'tipo_cliente' => 'tipo de cliente',
            'segmento' => 'segmento',
            'canal_venta' => 'canal de venta',
            'fecha_registro' => 'fecha de registro',
            'fecha_ultima_compra' => 'fecha de última compra',
            'fecha_ultima_visita' => 'fecha de última visita'
        ];
    }
}