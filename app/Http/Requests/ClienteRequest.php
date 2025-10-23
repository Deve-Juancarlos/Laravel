<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ClienteRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        $rules = [
            'tipo_documento' => 'required|in:DNI,RUC',
            'numero_documento' => [
                'required',
                'numeric',
                'unique:Clientes_reniec,numero_documento,' . ($this->cliente ? $this->cliente->id : '')
            ],
            'nombres' => 'nullable|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'razon_social' => 'nullable|string|max:200',
            'direccion' => 'nullable|string|max:255',
            'ubigeo' => 'nullable|string|size:6',
            'departamento' => 'nullable|string|max:50',
            'provincia' => 'nullable|string|max:50',
            'distrito' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'estado' => 'boolean'
        ];

        // Validaciones adicionales según tipo de documento
        if ($this->tipo_documento === 'DNI') {
            $rules['numero_documento'] = array_merge($rules['numero_documento'], ['digits:8']);
        } elseif ($this->tipo_documento === 'RUC') {
            $rules['numero_documento'] = array_merge($rules['numero_documento'], ['digits:11']);
            $rules['razon_social'] = 'required|string|max:200';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI o RUC',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.unique' => 'Este documento ya está registrado',
            'numero_documento.digits' => function($attribute, $value, $fail) {
                if ($this->tipo_documento === 'DNI') {
                    $fail('El DNI debe tener 8 dígitos');
                } else {
                    $fail('El RUC debe tener 11 dígitos');
                }
            },
            'razon_social.required' => 'La razón social es obligatoria para RUC',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
        ];
    }
}