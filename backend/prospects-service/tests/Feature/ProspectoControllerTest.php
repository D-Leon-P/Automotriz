<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Vehiculo;
use App\Models\Prospecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\NotifyN8nJob;

class ProspectoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor1;
    protected $vendedor2;
    protected $adminToken;
    protected $vendedor1Token;
    protected $vendedor2Token;
    protected $vehiculo;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerTodos = Permiso::create(['nombre' => 'ver_prospectos_todos']);
        $permVerPropios = Permiso::create(['nombre' => 'ver_prospectos_propios']);
        $permGestTodos = Permiso::create(['nombre' => 'gestionar_prospectos_todos']);
        $permGestPropios = Permiso::create(['nombre' => 'gestionar_prospectos_propios']);

        $roleAdmin->permisos()->attach([$permVerTodos->id, $permGestTodos->id]);
        $roleVendedor->permisos()->attach([$permVerPropios->id, $permGestPropios->id]);

        $this->admin = Empleado::create([
            'nombre' => 'Admin User',
            'email' => 'admin@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 1
        ]);

        $this->vendedor1 = Empleado::create([
            'nombre' => 'Vendedor 1',
            'email' => 'vendedor1@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->vendedor2 = Empleado::create([
            'nombre' => 'Vendedor 2',
            'email' => 'vendedor2@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->vendedor1Token = JWTAuth::fromUser($this->vendedor1);
        $this->vendedor2Token = JWTAuth::fromUser($this->vendedor2);

        $this->vehiculo = Vehiculo::create([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'anio' => 2026,
            'precio' => 25000.00,
            'stock' => 5
        ]);
    }

    public function test_admin_puede_crear_prospecto_asignado_a_cualquier_vendedor()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->postJson('/api/prospectos', [
            'nombre' => 'Nuevo Prospecto',
            'email' => 'prospecto@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('prospectos', [
            'nombre' => 'Nuevo Prospecto',
            'empleado_id' => $this->vendedor1->id
        ]);

        Queue::assertPushed(NotifyN8nJob::class);
    }

    public function test_vendedor_solo_puede_crear_prospectos_asociados_a_si_mismo()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson('/api/prospectos', [
            'nombre' => 'Mi Prospecto',
            'email' => 'miprospecto@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor2->id // Intenta asignarlo a otro
        ]);

        $response->assertStatus(201);
        
        // Debe guardarse asociado a vendedor1 (sobrescrito)
        $this->assertDatabaseHas('prospectos', [
            'nombre' => 'Mi Prospecto',
            'empleado_id' => $this->vendedor1->id
        ]);
    }

    public function test_vendedor_puede_actualizar_etapa_de_su_prospecto()
    {
        Queue::fake();

        $prospecto = Prospecto::create([
            'nombre' => 'Prospecto Juan',
            'email' => 'juan@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/prospectos/{$prospecto->id}", [
            'nombre' => 'Prospecto Juan Modificado',
            'email' => 'juan@example.com',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'calificacion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('prospectos', [
            'id' => $prospecto->id,
            'etapa' => 'calificacion',
            'nombre' => 'Prospecto Juan Modificado'
        ]);

        Queue::assertPushed(NotifyN8nJob::class);
    }

    public function test_vendedor_no_puede_actualizar_prospecto_de_otro()
    {
        $prospecto = Prospecto::create([
            'nombre' => 'Prospecto Ajeno',
            'email' => 'ajeno@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor2->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->putJson("/api/prospectos/{$prospecto->id}", [
            'nombre' => 'Prospecto Hackeado',
            'email' => 'ajeno@example.com',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'calificacion',
            'empleado_id' => $this->vendedor2->id
        ]);

        $response->assertStatus(403);
    }

    public function test_vendedor_puede_eliminar_su_prospecto()
    {
        $prospecto = Prospecto::create([
            'nombre' => 'Prospecto a Borrar',
            'email' => 'borrar@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->deleteJson("/api/prospectos/{$prospecto->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('prospectos', ['id' => $prospecto->id]);
    }

    public function test_restaurar_prospecto_exitoso()
    {
        Queue::fake();

        $prospecto = Prospecto::create([
            'nombre' => 'Prospecto a Restaurar',
            'email' => 'restaurar@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $prospecto->delete();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson("/api/prospectos/{$prospecto->id}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('prospectos', [
            'id' => $prospecto->id,
            'deleted_at' => null
        ]);

        Queue::assertPushed(NotifyN8nJob::class);
    }

    public function test_restaurar_prospecto_falla_si_no_hay_stock()
    {
        $this->vehiculo->update(['stock' => 0]);

        $prospecto = Prospecto::create([
            'nombre' => 'Prospecto Sin Stock',
            'email' => 'sinstock@example.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor1->id
        ]);

        $prospecto->delete();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->postJson("/api/prospectos/{$prospecto->id}/restore");

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'message']);
    }
}
