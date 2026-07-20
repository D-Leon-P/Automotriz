<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $vendedor;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::create(['id' => 1, 'nombre' => 'administrador']);
        Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $this->vendedor = Empleado::create([
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->token = JWTAuth::fromUser($this->vendedor);
    }

    public function test_login_exitoso()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'juan.perez@automotriz.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'expires_in' => 3600,
            ])
            ->assertJsonStructure([
                'status',
                'expires_in',
                'user' => [
                    'id', 'nombre', 'email', 'rol_id'
                ]
            ]);

        $response->assertCookie('auth_token');
    }

    public function test_login_credenciales_invalidas()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'juan.perez@automotriz.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Credenciales inválidas. Por favor intente de nuevo.'
            ]);
    }

    public function test_login_validacion_incorrecta()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_logout_exitoso()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}"
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente.'
            ]);
    }

    public function test_refresh_token_exitoso()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}"
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'expires_in' => 3600
            ]);
    }

    public function test_me_retorna_usuario_autenticado()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}"
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->vendedor->id,
                'nombre' => 'Juan Pérez',
                'email' => 'juan.perez@automotriz.com',
            ]);
    }
}
