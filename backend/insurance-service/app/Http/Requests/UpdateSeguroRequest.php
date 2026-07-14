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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->route('seguro') ?: $this->route('id');
            $seguro = \App\Models\Seguro::find($id);
            if (!$seguro) return;

            $estado = $this->has('estado') ? $this->estado : $seguro->estado;
            $esperada = $this->has('prima_esperada') ? $this->prima_esperada : $seguro->prima_esperada;
            $real = $this->has('prima_real') ? $this->prima_real : $seguro->prima_real;

            if ($estado === 'vendido') {
                if ($real === null || $real === '') {
                    $validator->errors()->add('prima_real', 'La prima real es obligatoria si el seguro está vendido.');
                } else {
                    $min = $esperada * 0.70;
                    $max = $esperada * 1.30;
                    if ($real < $min || $real > $max) {
                        $validator->errors()->add('prima_real', "La prima real (S/ " . number_format($real, 2) . ") debe estar dentro del rango permitido del 70% al 130% de la prima esperada (rango permitido: S/ " . number_format($min, 2) . " a S/ " . number_format($max, 2) . ").");
                    }
                }
            }
        });
    }
}
