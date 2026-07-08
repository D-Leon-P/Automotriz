<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = 'prospectos';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'vehiculo_id',
        'etapa',
        'vendedor_id',
    ];
}
