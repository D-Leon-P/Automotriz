<?php

namespace App\Repositories;

use App\Models\Prospecto;
use App\Models\Venta;
use App\Models\Seguro;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getProspectsCount($empleadoId)
    {
        $query = Prospecto::query();
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->count();
    }

    public function getProspectsInProcessCount($empleadoId)
    {
        $query = Prospecto::query()->whereIn('etapa', ['prospeccion', 'calificacion', 'negociacion']);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->count();
    }

    public function getProspectsByStage($empleadoId)
    {
        $query = Prospecto::select('etapa', DB::raw('count(*) as total'));
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->groupBy('etapa')->get();
    }

    public function getSalesSummary($empleadoId)
    {
        $query = Venta::select('estado', DB::raw('count(*) as total'), DB::raw('SUM(monto) as total_monto'));
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->groupBy('estado')->get();
    }

    public function getInsurancesSummary($empleadoId)
    {
        $query = Seguro::select('seguros.estado', DB::raw('count(*) as total'), DB::raw('SUM(seguros.prima_real) as total_prima_real'), DB::raw('SUM(seguros.prima_esperada) as total_prima_esperada'))
            ->join('ventas', 'seguros.venta_id', '=', 'ventas.id');
        
        if ($empleadoId !== null) {
            $query->where('ventas.empleado_id', $empleadoId);
        }
        
        return $query->groupBy('seguros.estado')->get();
    }

    public function getConversionForSeller($empleadoId)
    {
        $query = DB::table('empleados')
            ->leftJoin('prospectos', 'empleados.id', '=', 'prospectos.empleado_id')
            ->leftJoin('ventas', function($join) {
                $join->on('prospectos.id', '=', 'ventas.prospecto_id')
                     ->where('ventas.estado', '=', 'efectiva');
            })
            ->select(
                'empleados.id',
                'empleados.nombre',
                DB::raw('COUNT(DISTINCT prospectos.id) as total_prospectos'),
                DB::raw('COUNT(DISTINCT ventas.id) as ventas_efectivas'),
                DB::raw('CASE WHEN COUNT(DISTINCT prospectos.id) > 0 THEN (COUNT(DISTINCT ventas.id) * 100.0 / COUNT(DISTINCT prospectos.id)) ELSE 0 END as tasa_conversion')
            );
            
        if ($empleadoId !== null) {
            $query->where('empleados.id', $empleadoId);
        }
        
        return $query->groupBy('empleados.id', 'empleados.nombre')
            ->get();
    }
}
