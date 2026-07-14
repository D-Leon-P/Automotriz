<?php

namespace App\Services;

use App\Repositories\VehiculoRepository;

class VehiculoService
{
    protected $vehiculoRepository;

    public function __construct(VehiculoRepository $vehiculoRepository)
    {
        $this->vehiculoRepository = $vehiculoRepository;
    }

    public function getAllVehiculos()
    {
        $vehiculos = $this->vehiculoRepository->all();
        foreach ($vehiculos as $vehiculo) {
            $activeCount = \App\Models\Prospecto::where('vehiculo_id', $vehiculo->id)
                ->where('etapa', '!=', 'cierre')
                ->count();
            $vehiculo->stock_disponible = max(0, $vehiculo->stock - $activeCount);
        }
        return $vehiculos;
    }

    public function getVehiculoById($id)
    {
        $vehiculo = $this->vehiculoRepository->find($id);
        if ($vehiculo) {
            $activeCount = \App\Models\Prospecto::where('vehiculo_id', $vehiculo->id)
                ->where('etapa', '!=', 'cierre')
                ->count();
            $vehiculo->stock_disponible = max(0, $vehiculo->stock - $activeCount);
        }
        return $vehiculo;
    }
}
