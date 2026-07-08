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
        $this->ventaService = $ventaService;
    }

    public function index()
    {
        $vendedorId = Auth::id();
        $ventas = $this->ventaService->getAllVentas($vendedorId);
        return response()->json($ventas);
    }

    public function show($id)
    {
        $vendedorId = Auth::id();
        $venta = $this->ventaService->getVentaById($id, $vendedorId);
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
            $vendedorId = Auth::id();
            $validated = $request->validated();
            
            // Forzar que el vendedor de la venta sea el vendedor autenticado
            $validated['vendedor_id'] = $vendedorId;
            
            $venta = $this->ventaService->createVenta($validated, $vendedorId);
            
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
            $vendedorId = Auth::id();
            $validated = $request->validated();
            
            // Evitar transferencia fraudulenta
            unset($validated['vendedor_id']);

            $venta = $this->ventaService->updateVenta($id, $validated, $vendedorId);
            
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
            $vendedorId = Auth::id();
            $deleted = $this->ventaService->deleteVenta($id, $vendedorId);
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
}
