<?php

namespace App\Repositories;

use App\Models\Prospecto;
use App\Models\Venta;
use App\Models\Seguro;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getProspectsCount($vendedorId)
    {
        return Prospecto::where('vendedor_id', $vendedorId)->count();
    }

    public function getProspectsInProcessCount($vendedorId)
    {
        return Prospecto::where('vendedor_id', $vendedorId)
            ->whereIn('etapa', ['prospeccion', 'calificacion', 'negociacion'])
            ->count();
    }

    public function getProspectsByStage($vendedorId)
    {
        return Prospecto::select('etapa', DB::raw('count(*) as total'))
            ->where('vendedor_id', $vendedorId)
            ->groupBy('etapa')
            ->get();
    }

    public function getSalesSummary($vendedorId)
    {
        return Venta::select('estado', DB::raw('count(*) as total'), DB::raw('SUM(monto) as total_monto'))
            ->where('vendedor_id', $vendedorId)
            ->groupBy('estado')
            ->get();
    }

    public function getInsurancesSummary($vendedorId)
    {
        return Seguro::select('seguros.estado', DB::raw('count(*) as total'), DB::raw('SUM(seguros.prima_real) as total_prima_real'), DB::raw('SUM(seguros.prima_esperada) as total_prima_esperada'))
            ->join('ventas', 'seguros.venta_id', '=', 'ventas.id')
            ->where('ventas.vendedor_id', $vendedorId)
            ->groupBy('seguros.estado')
            ->get();
    }

    public function getConversionForSeller($vendedorId)
    {
        return DB::table('vendedores')
            ->leftJoin('prospectos', 'vendedores.id', '=', 'prospectos.vendedor_id')
            ->leftJoin('ventas', function($join) {
                $join->on('prospectos.id', '=', 'ventas.prospecto_id')
                     ->where('ventas.estado', '=', 'efectiva');
            })
            ->select(
                'vendedores.id',
                'vendedores.nombre',
                DB::raw('COUNT(DISTINCT prospectos.id) as total_prospectos'),
                DB::raw('COUNT(DISTINCT ventas.id) as ventas_efectivas'),
                DB::raw('IF(COUNT(DISTINCT prospectos.id) > 0, (COUNT(DISTINCT ventas.id) / COUNT(DISTINCT prospectos.id)) * 100, 0) as tasa_conversion')
            )
            ->where('vendedores.id', $vendedorId)
            ->groupBy('vendedores.id', 'vendedores.nombre')
            ->get();
    }
}
