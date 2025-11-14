<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición
     */
    public function authorize()
    {
        // Por ahora devuelve true (todos los usuarios autenticados pueden)
        // Más adelante puedes agregar lógica de permisos aquí
        return auth()->check();
    }

    /**
     * Las reglas de validación
     */
    public function rules()
    {
        return [
            'cliente_id' => 'required|exists:sqlsrv.Clientes,Codclie',
            'tipo_doc' => 'required|in:1,3', // 1=Factura, 3=Boleta
            'condicion' => 'required|in:contado,credito',
            'fecha_venc' => 'required_if:condicion,credito|date|after_or_equal:today',
            'vendedor_id' => 'required|exists:sqlsrv.Empleados,Codemp',
            'moneda' => 'required|in:1,2',
        ];
    }

    /**
     * Mensajes personalizados de error
     */
    public function messages()
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'tipo_doc.required' => 'Debe seleccionar el tipo de documento',
            'tipo_doc.in' => 'Tipo de documento inválido',
            'condicion.required' => 'Debe seleccionar la condición de pago',
            'fecha_venc.required_if' => 'La fecha de vencimiento es obligatoria para ventas a crédito',
            'fecha_venc.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
            'vendedor_id.required' => 'Debe seleccionar un vendedor',
            'moneda.required' => 'Debe seleccionar la moneda',
        ];
    }
}
