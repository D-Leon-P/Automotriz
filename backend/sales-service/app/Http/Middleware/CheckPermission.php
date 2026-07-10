<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado.'
            ], 401);
        }

        // Check if user has ANY of the required permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado. Permiso requerido: ' . implode(' o ', $permissions)
            ], 403);
        }

        return $next($request);
    }
}
