<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Autenticación controlada por Middleware JWT
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'vehiculo_id' => 'required|integer|exists:vehiculos,id',
            'etapa' => 'nullable|string|in:prospeccion,calificacion,negociacion,cierre',
            'empleado_id' => 'sometimes|required|integer|exists:empleados,id',
        ];
    }
}
