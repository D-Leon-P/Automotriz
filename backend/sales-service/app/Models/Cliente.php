<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'nombre',
        'apellido',
        'razon_social',
        'fecha_nacimiento',
        'email',
        'telefono',
        'documento',
        'direccion',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];
}
