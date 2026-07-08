<?php

namespace App\Http\Controllers;

use App\Services\VehiculoService;

class VehiculoController extends Controller
{
    protected $vehiculoService;

    public function __construct(VehiculoService $vehiculoService)
    {
        $this->middleware('auth:api');
        $this->vehiculoService = $vehiculoService;
    }

    public function index()
    {
        $vehiculos = $this->vehiculoService->getAllVehiculos();
        return response()->json($vehiculos);
    }

    public function show($id)
    {
        $vehiculo = $this->vehiculoService->getVehiculoById($id);
        if (!$vehiculo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehículo no encontrado'
            ], 404);
        }
        return response()->json($vehiculo);
    }
}
