<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeguroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'venta_id' => 'required|integer|exists:ventas,id',
            'tipo_seguro' => 'required|string|max:100',
            'prima_esperada' => 'required|numeric|min:0',
            'prima_real' => 'nullable|numeric|min:0',
            'estado' => 'required|string|in:prospectado,vendido',
        ];
    }
}
