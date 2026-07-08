<?php

namespace Tests\Feature;

use App\Models\Prospecto;
use App\Models\Vehiculo;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class SecuritySanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected $vendedor1;
    protected $vendedor2;
    protected $vehiculo;
    protected $token1;
    protected $token2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear Vendedores de Prueba
        $this->vendedor1 = Vendedor::create([
            'nombre' => 'Asesor Alfa',
            'email' => 'alfa@automotriz.com',
            'password' => bcrypt('password123')
        ]);

        $this->vendedor2 = Vendedor::create([
            'nombre' => 'Asesor Beta',
            'email' => 'beta@automotriz.com',
            'password' => bcrypt('password123')
        ]);

        // 2. Generar tokens JWT para autenticar
        $this->token1 = JWTAuth::fromUser($this->vendedor1);
        $this->token2 = JWTAuth::fromUser($this->vendedor2);

        $this->vehiculo = Vehiculo::create([
            'marca' => 'Toyota',
            'modelo' => 'Yaris',
            'anio' => 2026,
            'precio' => 20000.00,
            'stock' => 5
        ]);
    }

    /** @test */
    public function middleware_sanitiza_xss_e_inyecciones_de_script_en_inputs()
    {
        $payload = [
            'nombre' => '<script>alert("xss")</script>Juan Pérez', // Intento de XSS
            'email' => 'juan.perez@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'vendedor_id' => $this->vendedor1->id // Será sobrescrito por Auth::id() por seguridad
        ];

        // Ejecutar petición POST
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token1}"
        ])->postJson('/api/prospectos', $payload);

        // Aserciones
        $response->assertStatus(201);
        
        // Verificar que en la base de datos se guardó el nombre limpio sin etiquetas HTML
        $this->assertDatabaseMissing('prospectos', [
            'nombre' => '<script>alert("xss")</script>Juan Pérez'
        ]);

        $this->assertDatabaseHas('prospectos', [
            'nombre' => 'alert(&quot;xss&quot;)Juan Pérez' // strip_tags remueve <script> e htmlspecialchars convierte comillas
        ]);
    }

    /** @test */
    public function vendedor_no_puede_acceder_a_prospectos_de_otro_vendedor_bola_mitigation()
    {
        // Crear un prospecto perteneciente al Vendedor 1
        $prospectoVendedor1 = Prospecto::create([
            'nombre' => 'Prospecto Privado 1',
            'email' => 'privado@example.com',
            'telefono' => '123456789',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'vendedor_id' => $this->vendedor1->id
        ]);

        // Vendedor 2 intenta acceder al prospecto del Vendedor 1
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token2}"
        ])->getJson("/api/prospectos/{$prospectoVendedor1->id}");

        // Aserciones: Debe retornar 403 Forbidden y no revelar datos
        $response->assertStatus(403);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Prospecto no encontrado o no autorizado.'
        ]);
    }

    /** @test */
    public function vendedor_no_puede_editar_prospectos_de_otro_vendedor()
    {
        // Crear un prospecto perteneciente al Vendedor 1
        $prospectoVendedor1 = Prospecto::create([
            'nombre' => 'Prospecto Privado 1',
            'email' => 'privado@example.com',
            'telefono' => '123456789',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'vendedor_id' => $this->vendedor1->id
        ]);

        // Vendedor 2 intenta editar el prospecto del Vendedor 1
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token2}"
        ])->putJson("/api/prospectos/{$prospectoVendedor1->id}", [
            'nombre' => 'Intento Hacker'
        ]);

        // Aserciones: Debe retornar 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseMissing('prospectos', [
            'id' => $prospectoVendedor1->id,
            'nombre' => 'Intento Hacker'
        ]);
    }

    /** @test */
    public function login_retorna_cookie_httponly_con_jwt()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'alfa@automotriz.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertCookie('auth_token');
        
        // Verificar que no se expone el access_token en el cuerpo JSON por seguridad
        $response->assertJsonMissing(['access_token']);
    }
}
