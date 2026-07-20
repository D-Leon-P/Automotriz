<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Vehiculo;
use App\Models\Prospecto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Jobs\NotifyN8nJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;

class SalesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor1;
    protected $vendedor2;
    protected $adminToken;
    protected $vendedor1Token;
    protected $vendedor2Token;
    protected $vehiculo;
    protected $prospecto;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerTodos = Permiso::create(['nombre' => 'ver_ventas_todas']);
        $permVerPropios = Permiso::create(['nombre' => 'ver_ventas_propias']);
        $permGestTodos = Permiso::create(['nombre' => 'gestionar_ventas_todas']);
        $permGestPropios = Permiso::create(['nombre' => 'gestionar_ventas_propias']);
        
        $permVerClientes = Permiso::create(['nombre' => 'ver_clientes']);
        $permGestClientes = Permiso::create(['nombre' => 'gestionar_clientes']);

        $roleAdmin->permisos()->attach([
            $permVerTodos->id, $permGestTodos->id, $permVerClientes->id, $permGestClientes->id
        ]);
        $roleVendedor->permisos()->attach([
            $permVerPropios->id, $permGestPropios->id, $permVerClientes->id, $permGestClientes->id
        ]);

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

        $this->vehiculo = Vehiculo::create([
            'id' => 2,
            'marca' => 'Hyundai',
            'modelo' => 'Tucson',
            'anio' => 2026,
            'precio' => 31000.00,
            'stock' => 5
        ]);

        $this->prospecto = Prospecto::create([
            'id' => 1,
            'nombre' => 'Alejandro Sanz',
            'email' => 'alejandro@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);
    }

    // --- PRUEBAS DE VENTAS CONTROLLER ---
    public function test_vendedor_puede_registrar_venta_efectiva()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/ventas', [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Venta registrada exitosamente.'
            ]);

        $this->assertDatabaseHas('ventas', [
            'prospecto_id' => $this->prospecto->id,
            'estado' => 'efectiva',
            'empleado_id' => $this->vendedor1->id
        ]);

        $this->assertEquals(4, $this->vehiculo->fresh()->stock);

        Queue::assertPushed(NotifyN8nJob::class);
    }

    public function test_vendedor_no_puede_registrar_venta_duplicada_para_el_mismo_prospecto()
    {
        // 1. Registrar una venta para el prospecto
        Venta::create([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor1->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);
        
        // 2. Intentar registrar otra venta para el mismo prospecto via controller
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/ventas', [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);

        // Debe fallar con error de servidor/procedimiento (400)
        $response->assertStatus(400);
    }

    public function test_vendedor_no_puede_ver_ventas_de_otro()
    {
        $ventaAjena = Venta::create([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor2->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson("/api/ventas/{$ventaAjena->id}");

        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_cualquier_venta()
    {
        $ventaAjena = Venta::create([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor2->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson("/api/ventas/{$ventaAjena->id}");

        $response->assertStatus(200);
    }

    // --- PRUEBAS DE CLIENTE CONTROLLER ---
    public function test_vendedor_puede_crud_de_clientes()
    {
        // 1. Crear
        $responseCreate = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/clientes', [
            'tipo_documento' => 'DNI',
            'documento' => '99998888',
            'nombre' => 'Carlos',
            'apellido' => 'Vives',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'carlos@example.com',
            'telefono' => '987654321',
            'direccion' => 'Calle Principal'
        ]);

        $responseCreate->assertStatus(201);
        $this->assertDatabaseHas('clientes', ['documento' => '99998888']);
        $clienteId = $responseCreate->json('data.id');

        // 2. Listar
        $responseList = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/clientes');
        $responseList->assertStatus(200);

        // 3. Ver por documento
        $responseDoc = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/clientes?documento=99998888');
        $responseDoc->assertStatus(200)
            ->assertJson(['documento' => '99998888']);

        // 4. Ver inexistente
        $responseInexistente = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/clientes?documento=00000000');
        $responseInexistente->assertStatus(404);

        // 5. Editar
        $responseUpdate = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/clientes/{$clienteId}", [
            'tipo_documento' => 'DNI',
            'documento' => '99998888',
            'nombre' => 'Carlos Modificado',
            'apellido' => 'Vives',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'carlos.mod@example.com',
            'telefono' => '987654321',
            'direccion' => 'Calle Principal'
        ]);
        $responseUpdate->assertStatus(200);

        // 6. Eliminar
        $responseDelete = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->deleteJson("/api/clientes/{$clienteId}");
        $responseDelete->assertStatus(200);
        $this->assertSoftDeleted('clientes', ['id' => $clienteId]);
    }

    public function test_ver_cliente_no_encontrado()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/clientes/999');

        $response->assertStatus(404);
    }

    public function test_crear_cliente_validation_falla()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/clientes', [
            'nombre' => 'Solo Nombre' // Faltan campos obligatorios
        ]);

        $response->assertStatus(422);
    }

    public function test_crear_cliente_exception()
    {
        $this->mock(\App\Services\ClienteService::class, function ($mock) {
            $mock->shouldReceive('createCliente')->andThrow(new \Exception('Error simulado'));
        });

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/clientes', [
            'tipo_documento' => 'DNI',
            'documento' => '99998888',
            'nombre' => 'Carlos',
            'apellido' => 'Vives',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'carlos@example.com',
            'telefono' => '987654321',
            'direccion' => 'Calle Principal'
        ]);

        $response->assertStatus(400);
    }

    public function test_editar_cliente_exception()
    {
        $this->mock(\App\Services\ClienteService::class, function ($mock) {
            $mock->shouldReceive('updateCliente')->andThrow(new \Exception('Error simulado'));
        });

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson('/api/clientes/1', [
            'tipo_documento' => 'DNI',
            'documento' => '99998888',
            'nombre' => 'Carlos',
            'apellido' => 'Vives',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'carlos@example.com',
            'telefono' => '987654321',
            'direccion' => 'Calle Principal'
        ]);

        $response->assertStatus(400);
    }

    public function test_editar_cliente_validation_falla()
    {
        $cliente = Cliente::create([
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'nombre' => 'Carlos',
            'apellido' => 'Vives',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'carlos@example.com',
            'telefono' => '987654321',
            'direccion' => 'Calle Principal'
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/clientes/{$cliente->id}", [
            'email' => 'not-an-email' // Invalid email
        ]);

        $response->assertStatus(422);
    }

    public function test_eliminar_cliente_no_encontrado()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->deleteJson('/api/clientes/999');

        $response->assertStatus(400);
    }

    // --- PRUEBA DE JOB ---
    public function test_notify_n8n_job_envia_webhook_ventas()
    {
        Http::fake();

        $job = new NotifyN8nJob('created', ['id' => 999]);
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() == env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/ventas')
                && $request['action'] == 'created'
                && $request['venta']['id'] == 999;
        });
    }
}
