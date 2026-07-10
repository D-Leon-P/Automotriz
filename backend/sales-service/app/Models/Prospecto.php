<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospecto extends Model
{
    use SoftDeletes;

    protected $table = 'prospectos';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'vehiculo_id',
        'etapa',
        'empleado_id',
    ];
}
