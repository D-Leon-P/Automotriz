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
        return $this->vehiculoRepository->all();
    }

    public function getVehiculoById($id)
    {
        return $this->vehiculoRepository->find($id);
    }
}
