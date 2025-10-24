<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFacturaRequest extends FormRequest
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
            // Datos del cliente
            'cliente_id' => [
                'required',
                'string',
                'exists:Clientes,Codigo'
            ],

            // Tipo de documento
            'tipo_documento' => [
                'required',
                'string',
                Rule::in([
                    'Factura', 
                    'Boleta', 
                    'Nota Credito', 
                    'Nota Debito',
                    'Recibo Honorarios',
                    'Guía Remisión'
                ])
            ],

            // Serie y número
            'serie' => [
                'required',
                'string',
                'max:4',
                'regex:/^[A-Z0-9]+$/'
            ],
            'numero' => [
                'required',
                'string',
                'max:8',
                'regex:/^[0-9]+$/'
            ],

            // Fechas
            'fecha_emision' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today'
            ],
            'fecha_vencimiento' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after:fecha_emision'
            ],
            'fecha_ref' => [
                'nullable',
                'date',
                'date_format:Y-m-d'
            ],

            // Referencias (para notas de crédito/débito)
            'documento_ref' => [
                'nullable',
                'string',
                'max:50'
            ],

            // Moneda y tipo de cambio
            'moneda' => [
                'required',
                'string',
                'size:3',
                Rule::in(['PEN', 'USD', 'EUR'])
            ],
            'tipo_cambio' => [
                'nullable',
                'numeric',
                'min:0.0001',
                'max:9999.9999'
            ],

            // Detalles del documento
            'detalles' => [
                'required',
                'array',
                'min:1',
                'max:200' // Máximo 200 líneas por documento
            ],
            'detalles.*.producto_id' => [
                'required',
                'string',
                'exists:Productos,Codigo'
            ],
            'detalles.*.descripcion' => [
                'required',
                'string',
                'max:500'
            ],
            'detalles.*.cantidad' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999.999'
            ],
            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999.99'
            ],
            'detalles.*.descuento' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'detalles.*.centro_costo' => [
                'nullable',
                'string',
                'max:50'
            ],
            'detalles.*.lote' => [
                'nullable',
                'string',
                'max:50'
            ],
            'detalles.*.fecha_vencimiento' => [
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
            'condiciones_pago' => [
                'nullable',
                'string',
                'max:500'
            ],
            'direccion_entrega' => [
                'nullable',
                'string',
                'max:500'
            ],

            // Campos contables
            'glosa' => [
                'nullable',
                'string',
                'max:500'
            ],
            'area' => [
                'nullable',
                'string',
                'max:50'
            ],
            'proyecto' => [
                'nullable',
                'string',
                'max:50'
            ],

            // Estados y controles
            'estado' => [
                'nullable',
                'string',
                Rule::in(['Borrador', 'Pendiente', 'Emitido', 'Anulado'])
            ],
            'generar_asiento' => [
                'boolean'
            ],
            'enviar_sunat' => [
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
            // Mensajes para cliente
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',

            // Mensajes para tipo de documento
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido',

            // Mensajes para serie y número
            'serie.required' => 'La serie es obligatoria',
            'serie.regex' => 'La serie debe contener solo letras y números',
            'numero.required' => 'El número es obligatorio',
            'numero.regex' => 'El número debe contener solo números',

            // Mensajes para fechas
            'fecha_emision.required' => 'La fecha de emisión es obligatoria',
            'fecha_emision.date' => 'La fecha de emisión debe ser una fecha válida',
            'fecha_emision.before_or_equal' => 'La fecha de emisión no puede ser futura',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a la fecha de emisión',

            // Mensajes para moneda
            'moneda.required' => 'La moneda es obligatoria',
            'moneda.in' => 'La moneda seleccionada no es válida',

            // Mensajes para detalles
            'detalles.required' => 'Debe incluir al menos un detalle',
            'detalles.min' => 'Debe incluir al menos un detalle',
            'detalles.max' => 'No puede incluir más de 200 líneas',
            'detalles.*.producto_id.required' => 'El producto es obligatorio en todos los detalles',
            'detalles.*.producto_id.exists' => 'El producto seleccionado no existe',
            'detalles.*.descripcion.required' => 'La descripción es obligatoria en todos los detalles',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en todos los detalles',
            'detalles.*.cantidad.min' => 'La cantidad mínima es 0.001',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'detalles.*.precio_unitario.min' => 'El precio unitario mínimo es 0.01',

            // Mensajes generales
            'required' => 'Este campo es obligatorio',
            'string' => 'Este campo debe ser texto',
            'numeric' => 'Este campo debe ser numérico',
            'max' => 'Este campo no puede exceder :max caracteres',
            'min' => 'Este campo debe ser mayor a :min'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y formatear datos antes de la validación
        if ($this->has('fecha_emision')) {
            $this->merge([
                'fecha_emision' => \Carbon\Carbon::parse($this->fecha_emision)->format('Y-m-d')
            ]);
        }

        if ($this->has('fecha_vencimiento')) {
            $this->merge([
                'fecha_vencimiento' => \Carbon\Carbon::parse($this->fecha_vencimiento)->format('Y-m-d')
            ]);
        }

        if ($this->has('numero')) {
            $this->merge([
                'numero' => str_pad($this->numero, 8, '0', STR_PAD_LEFT)
            ]);
        }

        // Valores por defecto
        $this->merge([
            'moneda' => $this->moneda ?: 'PEN',
            'tipo_cambio' => $this->tipo_cambio ?: 1.0000,
            'estado' => $this->estado ?: 'Pendiente',
            'generar_asiento' => $this->boolean('generar_asiento', true),
            'enviar_sunat' => $this->boolean('enviar_sunat', false)
        ]);
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
            'cliente_id' => 'cliente',
            'tipo_documento' => 'tipo de documento',
            'fecha_emision' => 'fecha de emisión',
            'fecha_vencimiento' => 'fecha de vencimiento',
            'detalles.*.producto_id' => 'producto',
            'detalles.*.cantidad' => 'cantidad',
            'detalles.*.precio_unitario' => 'precio unitario',
            'detalles.*.descripcion' => 'descripción'
        ];
    }
}