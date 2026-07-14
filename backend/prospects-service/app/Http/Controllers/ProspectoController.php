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
        $this->middleware('permission:ver_prospectos_propios,ver_prospectos_todos')->only(['index', 'show']);
        $this->middleware('permission:gestionar_prospectos_propios,gestionar_prospectos_todos')->only(['store', 'update', 'destroy', 'restore']);
        $this->prospectoService = $prospectoService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_prospectos_todos') ? null : $user->id;
        $showDeleted = $request->query('show_deleted') === 'true';
        $prospectos = $this->prospectoService->getAllProspectos($empleadoId, $showDeleted);
        return response()->json($prospectos);
    }

    public function show($id)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('ver_prospectos_todos') ? null : $user->id;
        $prospecto = $this->prospectoService->getProspectoById($id, $empleadoId);
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
        $user = Auth::user();
        
        // Si no se provee empleado_id o no es administrador, forzar el empleado_id al autenticado
        if (!$user->hasPermission('gestionar_prospectos_todos') || !isset($validated['empleado_id'])) {
            $validated['empleado_id'] = $user->id;
        }
        
        $prospecto = $this->prospectoService->createProspecto($validated);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Prospecto registrado exitosamente.',
            'data' => $prospecto
        ], 201);
    }

    public function update(UpdateProspectoRequest $request, $id)
    {
        $user = Auth::user();
        $validated = $request->validated();
        
        // Evitar que se transfiera el prospecto de forma fraudulenta si no es administrador
        if (!$user->hasPermission('gestionar_prospectos_todos')) {
            unset($validated['empleado_id']);
        }
        
        $empleadoId = $user->hasPermission('gestionar_prospectos_todos') ? null : $user->id;

        // Impedir modificación si el prospecto ya está en etapa final de cierre (Venta Efectiva/Fallida)
        $prospectoExistente = $this->prospectoService->getProspectoById($id, $empleadoId);
        if ($prospectoExistente && $prospectoExistente->etapa === 'cierre') {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede modificar un prospecto que ya se encuentra en la etapa de Cierre.'
            ], 422);
        }

        try {
            $prospecto = $this->prospectoService->updateProspecto($id, $validated, $empleadoId);
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
        $user = Auth::user();
        $empleadoId = $user->hasPermission('gestionar_prospectos_todos') ? null : $user->id;

        // Impedir eliminación si el prospecto ya está en etapa final de cierre (Venta Efectiva/Fallida)
        $prospectoExistente = $this->prospectoService->getProspectoById($id, $empleadoId);
        if ($prospectoExistente && $prospectoExistente->etapa === 'cierre') {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar un prospecto que ya se encuentra en la etapa de Cierre.'
            ], 422);
        }
        
        $deleted = $this->prospectoService->deleteProspecto($id, $empleadoId);
        
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

    public function restore($id)
    {
        $user = Auth::user();
        $empleadoId = $user->hasPermission('gestionar_prospectos_todos') ? null : $user->id;
        try {
            $restored = $this->prospectoService->restoreProspecto($id, $empleadoId);
            return response()->json([
                'status' => 'success',
                'message' => 'Prospecto reintegrado exitosamente.',
                'data' => $restored
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
