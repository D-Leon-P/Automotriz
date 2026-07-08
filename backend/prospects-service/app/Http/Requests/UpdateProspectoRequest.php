<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProspectoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'vehiculo_id' => 'sometimes|required|integer|exists:vehiculos,id',
            'etapa' => 'sometimes|required|string|in:prospeccion,calificacion,negociacion,cierre',
            'vendedor_id' => 'sometimes|required|integer|exists:vendedores,id',
        ];
    }
}
