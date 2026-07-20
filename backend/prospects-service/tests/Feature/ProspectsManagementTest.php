<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Vehiculo;
use App\Models\Prospecto;
use App\Jobs\NotifyN8nJob;
use App\Jobs\NotifyWebSocketJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ProspectsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor;
    protected $adminToken;
    protected $vendedorToken;
    protected $vehiculo;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerEmp = Permiso::create(['nombre' => 'ver_empleados']);
        $permGestEmp = Permiso::create(['nombre' => 'gestionar_empleados']);
        $permVerRol = Permiso::create(['nombre' => 'ver_roles']);
        $permGestRol = Permiso::create(['nombre' => 'gestionar_roles']);

        $roleAdmin->permisos()->attach([$permVerEmp->id, $permGestEmp->id, $permVerRol->id, $permGestRol->id]);

        $this->admin = Empleado::create([
            'nombre' => 'Admin User',
            'email' => 'admin@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 1
        ]);

        $this->vendedor = Empleado::create([
            'nombre' => 'Vendedor User',
            'email' => 'vendedor@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->vendedorToken = JWTAuth::fromUser($this->vendedor);

        $this->vehiculo = Vehiculo::create([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'anio' => 2026,
            'precio' => 25000.00,
            'stock' => 10
        ]);
    }

    // --- PRUEBAS DE EMPLEADO ---
    public function test_admin_puede_listar_empleados()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/empleados');

        $response->assertStatus(200);
    }

    public function test_vendedor_no_puede_listar_empleados()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson('/api/empleados');

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_y_editar_empleado()
    {
        Queue::fake();

        $responseStore = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->postJson('/api/empleados', [
            'nombre' => 'Nuevo Empleado',
            'email' => 'nuevo@automotriz.com',
            'password' => 'password123',
            'rol_id' => 2
        ]);

        $responseStore->assertStatus(201);
        $this->assertDatabaseHas('empleados', ['email' => 'nuevo@automotriz.com']);

        $empleadoId = $responseStore->json('data.id');

        $responseUpdate = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->putJson("/api/empleados/{$empleadoId}", [
            'nombre' => 'Empleado Editado',
            'email' => 'nuevo@automotriz.com',
            'rol_id' => 2
        ]);

        $responseUpdate->assertStatus(200);
        $this->assertDatabaseHas('empleados', ['nombre' => 'Empleado Editado']);
        
        // Verificar que se despachó el job de websocket
        Queue::assertPushed(NotifyWebSocketJob::class);
    }

    public function test_admin_puede_eliminar_empleado()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->deleteJson("/api/empleados/{$this->vendedor->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('empleados', ['id' => $this->vendedor->id]);
    }

    // --- PRUEBAS DE ROLES ---
    public function test_admin_puede_ver_roles_y_permisos()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/roles');

        $response->assertStatus(200);

        $responsePermisos = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/permisos');

        $responsePermisos->assertStatus(200);
    }

    public function test_admin_puede_actualizar_permisos_de_rol()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->putJson('/api/roles/2', [
            'nombre' => 'vendedor',
            'permisos' => [1, 2]
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(NotifyWebSocketJob::class);
    }

    public function test_admin_puede_crear_rol()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->postJson('/api/roles', [
            'nombre' => 'supervisor',
            'permisos' => [1, 2]
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', ['nombre' => 'supervisor']);
    }

    public function test_ver_rol_y_error()
    {
        $responseExist = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/roles/2');
        $responseExist->assertStatus(200);

        $responseNotExist = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/roles/999');
        $responseNotExist->assertStatus(404);
    }

    public function test_eliminar_rol_validacion()
    {
        // 1. Eliminar rol administrador por defecto falla
        $responseAdmin = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->deleteJson('/api/roles/1');
        $responseAdmin->assertStatus(403);

        // 2. Eliminar rol con empleados falla
        $customRolWithEmp = Rol::create(['nombre' => 'custom_con_emp']);
        Empleado::create([
            'nombre' => 'Colaborador Temporal',
            'email' => 'temporal@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => $customRolWithEmp->id
        ]);

        $responseVendedor = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->deleteJson("/api/roles/{$customRolWithEmp->id}");
        $responseVendedor->assertStatus(400);

        // 3. Eliminar rol personalizado funciona
        $customRol = Rol::create(['nombre' => 'rol_vacio']);
        $responseCustom = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->deleteJson("/api/roles/{$customRol->id}");
        $responseCustom->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['id' => $customRol->id]);
    }

    // --- PRUEBAS DE VEHICULOS ---
    public function test_listar_y_ver_vehiculo()
    {
        $responseList = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson('/api/vehiculos');
        $responseList->assertStatus(200);

        $responseShow = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson("/api/vehiculos/{$this->vehiculo->id}");
        $responseShow->assertStatus(200);
    }

    // --- PRUEBAS DE JOBS ---
    public function test_notify_n8n_job_envia_http_post()
    {
        Http::fake();

        $job = new NotifyN8nJob('created', ['id' => 123]);
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() == env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/prospectos')
                && $request['action'] == 'created'
                && $request['prospecto']['id'] == 123;
        });
    }

    public function test_notify_websocket_job_envia_http_post()
    {
        Http::fake();

        $job = new NotifyWebSocketJob('test.event', ['key' => 'value']);
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() == 'http://websocket-service:6001/publish'
                && $request['event'] == 'test.event'
                && $request['data']['key'] == 'value';
        });
    }
}
