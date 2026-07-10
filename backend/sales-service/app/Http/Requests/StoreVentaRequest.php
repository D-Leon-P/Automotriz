<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'prospecto_id' => 'required|integer|exists:prospectos,id',
            'vehiculo_id' => 'required|integer|exists:vehiculos,id',
            'empleado_id' => 'sometimes|required|integer|exists:empleados,id',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|string|in:efectiva,fallida',
            'motivo_perdida' => 'required_if:estado,fallida|nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'motivo_perdida.required_if' => 'El motivo de pérdida es obligatorio cuando la venta es fallida.',
        ];
    }
}
