<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeguroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'venta_id' => 'sometimes|required|integer|exists:ventas,id',
            'tipo_seguro' => 'sometimes|required|string|max:100',
            'prima_esperada' => 'sometimes|required|numeric|min:0',
            'prima_real' => 'nullable|numeric|min:0',
            'estado' => 'sometimes|required|string|in:prospectado,vendido',
        ];
    }
}
