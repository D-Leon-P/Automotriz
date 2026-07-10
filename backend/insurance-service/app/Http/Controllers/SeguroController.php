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
        $this->middleware('permission:ver_seguros_propios,ver_seguros_todos')->only(['index', 'show']);
        $this->middleware('permission:gestionar_seguros_propios,gestionar_seguros_todos')->only(['store', 'update', 'destroy']);
        $this->seguroService = $seguroService;
    }

    public function index()
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_seguros_todos') ? null : $user->id;
        $seguros = $this->seguroService->getAllSeguros($empleadoId);
        return response()->json($seguros);
    }

    public function show($id)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_seguros_todos') ? null : $user->id;
        $seguro = $this->seguroService->getSeguroById($id, $empleadoId);
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
            $user = Auth::user();
            $validated = $request->validated();
            
            $empleadoId = $user->hasPermission('gestionar_seguros_todos') ? null : $user->id;
            $seguro = $this->seguroService->createSeguro($validated, $empleadoId);

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
            $user = Auth::user();
            $validated = $request->validated();
            
            $empleadoId = $user->hasPermission('gestionar_seguros_todos') ? null : $user->id;
            $seguro = $this->seguroService->updateSeguro($id, $validated, $empleadoId);

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
            $user = Auth::user();
            $empleadoId = $user->hasPermission('gestionar_seguros_todos') ? null : $user->id;
            $deleted = $this->seguroService->deleteSeguro($id, $empleadoId);
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
