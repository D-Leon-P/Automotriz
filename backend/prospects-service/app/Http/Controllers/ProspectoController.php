<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProspectoRequest;
use App\Http\Requests\UpdateProspectoRequest;
use App\Services\ProspectoService;
use Illuminate\Support\Facades\Auth;

class ProspectoController extends Controller
{
    protected $prospectoService;

    public function __construct(ProspectoService $prospectoService)
    {
        $this->middleware('auth:api');
        $this->prospectoService = $prospectoService;
    }

    public function index()
    {
        $vendedorId = Auth::id();
        $prospectos = $this->prospectoService->getAllProspectos($vendedorId);
        return response()->json($prospectos);
    }

    public function show($id)
    {
        $vendedorId = Auth::id();
        $prospecto = $this->prospectoService->getProspectoById($id, $vendedorId);
        if (!$prospecto) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prospecto no encontrado o no autorizado.'
            ], 403);
        }
        return response()->json($prospecto);
    }

    public function store(StoreProspectoRequest $request)
    {
        $validated = $request->validated();
        
        // Forzar que el prospecto pertenezca al vendedor autenticado (Evita Broken Access Control)
        $validated['vendedor_id'] = Auth::id();
        
        $prospecto = $this->prospectoService->createProspecto($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Prospecto registrado exitosamente.',
            'data' => $prospecto
        ], 201);
    }

    public function update(UpdateProspectoRequest $request, $id)
    {
        $vendedorId = Auth::id();
        $validated = $request->validated();
        
        // Evitar que se transfiera el prospecto a otro vendedor de forma fraudulenta
        unset($validated['vendedor_id']);

        try {
            $prospecto = $this->prospectoService->updateProspecto($id, $validated, $vendedorId);
            return response()->json([
                'status' => 'success',
                'message' => 'Prospecto actualizado exitosamente.',
                'data' => $prospecto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para actualizar este prospecto o no existe.'
            ], 403);
        }
    }

    public function destroy($id)
    {
        $vendedorId = Auth::id();
        $deleted = $this->prospectoService->deleteProspecto($id, $vendedorId);
        
        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para eliminar este prospecto o no existe.'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Prospecto eliminado exitosamente.'
        ]);
    }
}
