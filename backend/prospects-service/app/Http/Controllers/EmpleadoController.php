<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:ver_empleados')->only(['index', 'show']);
        $this->middleware('permission:gestionar_empleados')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $empleados = Empleado::with('rol')->get();
        return response()->json($empleados);
    }

    public function show($id)
    {
        $empleado = Empleado::with('rol')->find($id);
        if (!$empleado) {
            return response()->json(['status' => 'error', 'message' => 'Colaborador no encontrado.'], 404);
        }
        return response()->json($empleado);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:empleados,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|integer|exists:roles,id'
        ]);

        $empleado = Empleado::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Colaborador registrado exitosamente.',
            'data' => $empleado->load('rol')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:empleados,email,' . $id,
            'password' => 'nullable|string|min:6',
            'rol_id' => 'required|integer|exists:roles,id'
        ]);

        // Evitar que el administrador se quite su propio rol de administrador
        if (auth()->id() == $id && $empleado->rol_id != $request->rol_id && $empleado->rol->nombre === 'administrador') {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes cambiar tu propio rol de administrador.'
            ], 400);
        }

        $data = [
            'nombre' => $request->nombre,
            'email' => $request->email,
            'rol_id' => $request->rol_id
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $empleado->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Colaborador actualizado exitosamente.',
            'data' => $empleado->load('rol')
        ]);
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);

        // Evitar auto-eliminación
        if (auth()->id() == $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes eliminar tu propia cuenta de colaborador.'
            ], 400);
        }

        $empleado->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Colaborador eliminado exitosamente (soft delete).'
        ]);
    }
}
