<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSeguroRequest;
use App\Http\Requests\UpdateSeguroRequest;
use App\Services\SeguroService;
use Illuminate\Support\Facades\Auth;

class SeguroController extends Controller
{
    protected $seguroService;

    public function __construct(SeguroService $seguroService)
    {
        $this->middleware('auth:api');
        $this->seguroService = $seguroService;
    }

    public function index()
    {
        $vendedorId = Auth::id();
        $seguros = $this->seguroService->getAllSeguros($vendedorId);
        return response()->json($seguros);
    }

    public function show($id)
    {
        $vendedorId = Auth::id();
        $seguro = $this->seguroService->getSeguroById($id, $vendedorId);
        if (!$seguro) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seguro no encontrado o no autorizado.'
            ], 403);
        }
        return response()->json($seguro);
    }

    public function store(StoreSeguroRequest $request)
    {
        try {
            $vendedorId = Auth::id();
            $validated = $request->validated();
            
            $seguro = $this->seguroService->createSeguro($validated, $vendedorId);

            return response()->json([
                'status' => 'success',
                'message' => 'Póliza de seguro vinculada exitosamente.',
                'data' => $seguro
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(UpdateSeguroRequest $request, $id)
    {
        try {
            $vendedorId = Auth::id();
            $validated = $request->validated();
            
            $seguro = $this->seguroService->updateSeguro($id, $validated, $vendedorId);

            return response()->json([
                'status' => 'success',
                'message' => 'Póliza de seguro actualizada exitosamente.',
                'data' => $seguro
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
            $deleted = $this->seguroService->deleteSeguro($id, $vendedorId);
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seguro no encontrado o no autorizado para ser eliminado.'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Póliza de seguro eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
