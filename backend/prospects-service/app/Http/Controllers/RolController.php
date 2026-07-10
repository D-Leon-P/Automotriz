<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:ver_roles')->only(['index', 'show', 'getAllPermisos']);
        $this->middleware('permission:gestionar_roles')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $roles = Rol::with('permisos')->get();
        return response()->json($roles);
    }

    public function getAllPermisos()
    {
        $permisos = Permiso::get();
        return response()->json($permisos);
    }

    public function show($id)
    {
        $rol = Rol::with('permisos')->find($id);
        if (!$rol) {
            return response()->json(['status' => 'error', 'message' => 'Rol no encontrado.'], 404);
        }
        return response()->json($rol);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre',
            'permisos' => 'required|array|min:1',
            'permisos.*' => 'integer|exists:permisos,id'
        ]);

        return DB::transaction(function () use ($request) {
            $rol = Rol::create([
                'nombre' => strtolower($request->nombre)
            ]);
            
            $rol->permisos()->sync($request->permisos);
            return response()->json([
                'status' => 'success',
                'message' => 'Rol creado exitosamente.',
                'data' => $rol->load('permisos')
            ], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);
        
        // No permitir editar el rol administrador por seguridad
        if ($rol->nombre === 'administrador') {
            return response()->json([
                'status' => 'error',
                'message' => 'No está permitido editar el rol administrador.'
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $id,
            'permisos' => 'required|array|min:1',
            'permisos.*' => 'integer|exists:permisos,id'
        ]);

        return DB::transaction(function () use ($request, $rol) {
            $rol->update([
                'nombre' => strtolower($request->nombre)
            ]);
            
            $rol->permisos()->sync($request->permisos);
            return response()->json([
                'status' => 'success',
                'message' => 'Rol actualizado exitosamente.',
                'data' => $rol->load('permisos')
            ]);
        });
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        
        if ($rol->nombre === 'administrador' || $rol->nombre === 'vendedor') {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pueden eliminar los roles por defecto del sistema (administrador/vendedor).'
            ], 403);
        }

        // Verificar si hay empleados asignados a este rol antes de eliminar
        if (Empleado::where('rol_id', $rol->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar el rol porque tiene colaboradores asociados.'
            ], 400);
        }

        $rol->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Rol eliminado exitosamente.'
        ]);
    }
}
