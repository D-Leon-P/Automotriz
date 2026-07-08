<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'prospecto_id',
        'vehiculo_id',
        'vendedor_id',
        'monto',
        'estado',
        'motivo_perdida',
    ];
}
