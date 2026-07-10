<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tipo_documento' => 'required|in:DNI,RUC,CEX',
            'nombre' => 'nullable|string|max:50',
            'apellido' => 'nullable|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(90)->format('Y-m-d'),
            'email' => 'required_without:telefono|nullable|email|max:100|unique:clientes,email',
            'telefono' => 'required_without:email|nullable|string|max:20',
            'documento' => 'required|string|max:20|unique:clientes,documento',
            'direccion' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'email.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'telefono.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'fecha_nacimiento.before_or_equal' => 'El cliente debe ser mayor de edad (mínimo 18 años).',
            'fecha_nacimiento.after_or_equal' => 'El cliente no puede tener más de 90 años.'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tipo = $this->tipo_documento;
            $doc = $this->documento;

            if ($tipo === 'DNI') {
                if (!preg_match('/^[0-9]{8}$/', $doc)) {
                    $validator->errors()->add('documento', 'El DNI debe tener exactamente 8 dígitos numéricos.');
                }
            } elseif ($tipo === 'RUC') {
                if (!preg_match('/^[12][0-9]{10}$/', $doc)) {
                    $validator->errors()->add('documento', 'El RUC debe comenzar con 1 o 2 y tener exactamente 11 dígitos numéricos.');
                }
            } elseif ($tipo === 'CEX') {
                $doc = str_pad($doc, 9, '0', STR_PAD_LEFT);
                if (!preg_match('/^[a-zA-Z0-9]{9}$/', $doc)) {
                    $validator->errors()->add('documento', 'El CEX debe tener exactamente 9 caracteres alfanuméricos.');
                }
                $this->merge(['documento' => $doc]);
            }
        });
    }
}
