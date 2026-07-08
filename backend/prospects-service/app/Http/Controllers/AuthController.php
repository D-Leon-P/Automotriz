<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas. Por favor intente de nuevo.'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        // Eliminar la cookie auth_token (expirándola)
        $cookie = cookie()->forget('auth_token');

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente.'
        ])->withCookie($cookie);
    }

    public function refresh()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $minutes = Auth::guard('api')->factory()->getTTL(); // 60 minutos por defecto

        // Crear la cookie de autenticación segura (HttpOnly, SameSite=Lax)
        $cookie = cookie(
            'auth_token',           // Nombre de la cookie
            $token,                 // Valor (token JWT)
            $minutes,               // Duración en minutos
            '/',                    // Path accesible en toda la app
            null,                   // Domain
            false,                  // Secure (debe ser false para desarrollo en localhost HTTP)
            true,                   // HttpOnly (Verdadero blindaje contra XSS)
            false,                  // Raw
            'Lax'                   // SameSite (Mitigación CSRF)
        );

        return response()->json([
            'status' => 'success',
            'expires_in' => $minutes * 60,
            'vendedor' => Auth::guard('api')->user()
        ])->withCookie($cookie);
    }
}
