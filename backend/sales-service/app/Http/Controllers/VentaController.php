<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Services\VentaService;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->middleware('auth:api');
        $this->middleware('permission:ver_ventas_propias,ver_ventas_todas')->only(['index', 'show']);
        $this->middleware('permission:gestionar_ventas_propias,gestionar_ventas_todas')->only(['store', 'update']);
        $this->middleware('permission:gestionar_ventas_todas')->only(['destroy', 'restore']);
        $this->ventaService = $ventaService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_ventas_todas') ? null : $user->id;
        $showDeleted = $request->query('show_deleted') === 'true';
        $ventas = $this->ventaService->getAllVentas($empleadoId, $showDeleted);
        return response()->json($ventas);
    }

    public function show($id)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_ventas_todas') ? null : $user->id;
        $venta = $this->ventaService->getVentaById($id, $empleadoId);
        if (!$venta) {
            return response()->json([
                'status' => 'error',
                'message' => 'Venta no encontrada o no autorizada.'
            ], 403);
        }
        return response()->json($venta);
    }

    public function store(StoreVentaRequest $request)
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();
            
            // Si no se especifica empleado_id o no es administrador, forzar el empleado autenticado
            if (!$user->hasPermission('gestionar_ventas_todas') || !isset($validated['empleado_id'])) {
                $validated['empleado_id'] = $user->id;
            }
            
            $empleadoId = $user->hasPermission('gestionar_ventas_todas') ? null : $user->id;
            $venta = $this->ventaService->createVenta($validated, $empleadoId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Venta registrada exitosamente.',
                'data' => $venta
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(UpdateVentaRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();
            
            // Evitar transferencia fraudulenta a otro empleado si no es administrador
            if (!$user->hasPermission('gestionar_ventas_todas')) {
                unset($validated['empleado_id']);
            }
            
            $empleadoId = $user->hasPermission('gestionar_ventas_todas') ? null : $user->id;
            $venta = $this->ventaService->updateVenta($id, $validated, $empleadoId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Venta actualizada exitosamente.',
                'data' => $venta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $empleadoId = $user->hasPermission('gestionar_ventas_todas') ? null : $user->id;
            $deleted = $this->ventaService->deleteVenta($id, $empleadoId);
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Venta no encontrada o no autorizada para ser eliminada.'
                ], 403);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Venta eliminada exitosamente y stock devuelto si correspondía.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function restore($id)
    {
        try {
            $user = Auth::user();
            $empleadoId = $user->hasPermission('gestionar_ventas_todas') ? null : $user->id;
            $venta = $this->ventaService->restoreVenta($id, $empleadoId);
            return response()->json([
                'status' => 'success',
                'message' => 'Venta reintegrada exitosamente y stock ajustado si correspondía.',
                'data' => $venta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
