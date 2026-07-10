<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seguro extends Model
{
    use SoftDeletes;

    protected $table = 'seguros';

    protected $fillable = [
        'venta_id',
        'tipo_seguro',
        'prima_esperada',
        'prima_real',
        'estado',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
