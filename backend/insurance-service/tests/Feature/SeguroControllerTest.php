<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Venta;
use App\Models\Seguro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SeguroControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor1;
    protected $vendedor2;
    protected $adminToken;
    protected $vendedor1Token;
    protected $vendedor2Token;
    protected $venta1;
    protected $venta2;
    protected $seguro1;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerTodos = Permiso::create(['nombre' => 'ver_seguros_todos']);
        $permVerPropios = Permiso::create(['nombre' => 'ver_seguros_propios']);
        $permGestTodos = Permiso::create(['nombre' => 'gestionar_seguros_todos']);
        $permGestPropios = Permiso::create(['nombre' => 'gestionar_seguros_propios']);

        $roleAdmin->permisos()->attach([$permVerTodos->id, $permGestTodos->id]);
        $roleVendedor->permisos()->attach([$permVerPropios->id, $permGestPropios->id]);

        $this->admin = Empleado::create([
            'id' => 1,
            'nombre' => 'Admin User',
            'email' => 'admin@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 1
        ]);

        $this->vendedor1 = Empleado::create([
            'id' => 2,
            'nombre' => 'Vendedor 1',
            'email' => 'vendedor1@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->vendedor2 = Empleado::create([
            'id' => 3,
            'nombre' => 'Vendedor 2',
            'email' => 'vendedor2@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->vendedor1Token = JWTAuth::fromUser($this->vendedor1);
        $this->vendedor2Token = JWTAuth::fromUser($this->vendedor2);

        \Illuminate\Support\Facades\DB::table('vehiculos')->insert([
            ['id' => 1, 'marca' => 'Toyota', 'modelo' => 'Corolla', 'anio' => 2026, 'precio' => 25000.00, 'stock' => 10],
            ['id' => 2, 'marca' => 'Hyundai', 'modelo' => 'Tucson', 'anio' => 2026, 'precio' => 31000.00, 'stock' => 5],
        ]);

        \Illuminate\Support\Facades\DB::table('prospectos')->insert([
            ['id' => 1, 'nombre' => 'Prospecto 1', 'email' => 'p1@example.com', 'telefono' => '111', 'vehiculo_id' => 1, 'etapa' => 'prospeccion', 'empleado_id' => 2],
            ['id' => 2, 'nombre' => 'Prospecto 2', 'email' => 'p2@example.com', 'telefono' => '222', 'vehiculo_id' => 2, 'etapa' => 'prospeccion', 'empleado_id' => 3],
        ]);

        $this->venta1 = Venta::create([
            'id' => 10,
            'prospecto_id' => 1,
            'vehiculo_id' => 1,
            'empleado_id' => $this->vendedor1->id,
            'monto' => 30000.00,
            'estado' => 'efectiva'
        ]);

        $this->venta2 = Venta::create([
            'id' => 20,
            'prospecto_id' => 2,
            'vehiculo_id' => 2,
            'empleado_id' => $this->vendedor2->id,
            'monto' => 30000.00,
            'estado' => 'efectiva'
        ]);

        $this->venta3 = Venta::create([
            'id' => 30,
            'prospecto_id' => 1,
            'vehiculo_id' => 1,
            'empleado_id' => $this->vendedor1->id,
            'monto' => 30000.00,
            'estado' => 'efectiva'
        ]);

        $this->seguro1 = Seguro::create([
            'venta_id' => $this->venta1->id,
            'tipo_seguro' => 'todo_riesgo',
            'prima_esperada' => 1200.00,
            'prima_real' => 1200.00,
            'estado' => 'prospectado'
        ]);
    }

    public function test_listar_y_ver_seguros()
    {
        // Vendedor 1 ve el suyo
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/seguros');

        $response->assertStatus(200)
            ->assertJsonCount(1);

        // Vendedor 1 intenta ver el de Vendedor 2
        $seguro2 = Seguro::create([
            'venta_id' => $this->venta2->id,
            'tipo_seguro' => 'todo_riesgo',
            'prima_esperada' => 1500.00,
            'prima_real' => 1500.00,
            'estado' => 'prospectado'
        ]);

        $responseShow = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson("/api/seguros/{$seguro2->id}");

        $responseShow->assertStatus(403);
    }

    public function test_vendedor_puede_vincular_seguro_a_su_venta()
    {
        Http::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/seguros', [
            'venta_id' => $this->venta3->id,
            'tipo_seguro' => 'terceros',
            'prima_esperada' => 800.00,
            'prima_real' => 800.00,
            'estado' => 'prospectado'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('seguros', [
            'tipo_seguro' => 'terceros',
            'venta_id' => $this->venta3->id
        ]);
    }

    public function test_vendedor_no_puede_vincular_seguro_duplicado_a_la_misma_venta()
    {
        // venta1 ya tiene seguro1 asignado
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/seguros', [
            'venta_id' => $this->venta1->id,
            'tipo_seguro' => 'terceros',
            'prima_esperada' => 800.00,
            'prima_real' => 800.00,
            'estado' => 'prospectado'
        ]);

        $response->assertStatus(400); // Falla por Exception
    }

    public function test_vendedor_no_puede_vincular_seguro_a_venta_de_otro()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/seguros', [
            'venta_id' => $this->venta2->id, // Venta de vendedor 2
            'tipo_seguro' => 'terceros',
            'prima_esperada' => 800.00,
            'prima_real' => 800.00,
            'estado' => 'prospectado'
        ]);

        $response->assertStatus(400); // Exception caught by catch block
    }

    public function test_update_seguro_bola_y_excepcion()
    {
        Http::fake();

        // 1. Update exitoso
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/seguros/{$this->seguro1->id}", [
            'tipo_seguro' => 'todo_riesgo_modificado',
            'prima_esperada' => 1300.00,
            'prima_real' => 1300.00,
            'estado' => 'vendido'
        ]);

        $response->assertStatus(200);

        // 2. Falla BOLA al intentar asociar venta ajena en update
        $responseBola = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/seguros/{$this->seguro1->id}", [
            'venta_id' => $this->venta2->id // Venta ajena
        ]);

        $responseBola->assertStatus(400);
    }

    public function test_eliminar_seguro_exitoso()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->deleteJson("/api/seguros/{$this->seguro1->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('seguros', ['id' => $this->seguro1->id]);
    }

    public function test_xss_sanitize_middleware_insurance()
    {
        Http::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/seguros', [
            'venta_id' => $this->venta3->id,
            'tipo_seguro' => '<script>alert("hack")</script> Rímac',
            'prima_esperada' => 1200.00,
            'prima_real' => 1200.00,
            'estado' => 'prospectado'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('seguros', [
            'tipo_seguro' => 'alert(&quot;hack&quot;) Rímac' // Sanitized
        ]);
    }

    public function test_vendedor_no_puede_registrar_seguro_duplicado_para_la_misma_venta()
    {
        // Seguro1 ya existe para venta1 en el setUp()

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/seguros', [
            'venta_id' => $this->venta1->id, // Venta1 ya tiene seguro1
            'tipo_seguro' => 'segundo_seguro',
            'prima_esperada' => 1000.00,
            'prima_real' => 1000.00,
            'estado' => 'prospectado'
        ]);

        $response->assertStatus(400); // Bad Request (Exception caught)
    }

    public function test_vendedor_no_puede_cambiar_venta_a_una_que_ya_tiene_seguro_en_update()
    {
        // seguro1 está en venta1
        $ventaConSeguro = Venta::create([
            'id' => 40,
            'prospecto_id' => 1,
            'vehiculo_id' => 1,
            'empleado_id' => $this->vendedor1->id,
            'monto' => 30000.00,
            'estado' => 'efectiva'
        ]);

        // Crear seguro para esa venta
        Seguro::create([
            'venta_id' => $ventaConSeguro->id,
            'tipo_seguro' => 'terceros',
            'prima_esperada' => 800.00,
            'prima_real' => 800.00,
            'estado' => 'prospectado'
        ]);

        // Intentar actualizar seguro1 para que apunte a ventaConSeguro
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/seguros/{$this->seguro1->id}", [
            'venta_id' => $ventaConSeguro->id
        ]);

        $response->assertStatus(400); // Falla por Exception
    }
}
