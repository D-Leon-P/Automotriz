<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Vehiculo;
use App\Models\Prospecto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor;
    protected $adminToken;
    protected $vendedorToken;
    protected $vehiculo;
    protected $prospecto;
    protected $venta;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerTodos = Permiso::create(['nombre' => 'ver_ventas_todas']);
        $permVerPropios = Permiso::create(['nombre' => 'ver_ventas_propias']);
        $permGestTodos = Permiso::create(['nombre' => 'gestionar_ventas_todas']);
        $permGestPropios = Permiso::create(['nombre' => 'gestionar_ventas_propias']);

        $roleAdmin->permisos()->attach([$permVerTodos->id, $permGestTodos->id]);
        $roleVendedor->permisos()->attach([$permVerPropios->id, $permGestPropios->id]);

        $this->admin = Empleado::create([
            'id' => 1,
            'nombre' => 'Admin User',
            'email' => 'admin@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 1
        ]);

        $this->vendedor = Empleado::create([
            'id' => 2,
            'nombre' => 'Vendedor',
            'email' => 'vendedor@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->vendedorToken = JWTAuth::fromUser($this->vendedor);

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
            'empleado_id' => $this->vendedor->id
        ]);

        $this->venta = Venta::create([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 31000.00,
            'estado' => 'efectiva'
        ]);
    }

    public function test_index_listar_ventas()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson('/api/ventas');

        $response->assertStatus(200)
            ->assertJsonCount(1);
            
        $responseDeleted = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson('/api/ventas?show_deleted=true');
        
        $responseDeleted->assertStatus(200);
    }

    public function test_show_venta_error()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->getJson('/api/ventas/999');

        $response->assertStatus(403);
    }

    public function test_store_venta_validacion_falla()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->postJson('/api/ventas', [
            'prospecto_id' => $this->prospecto->id
        ]);

        $response->assertStatus(422);
    }

    public function test_update_venta_exitoso()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->putJson("/api/ventas/{$this->venta->id}", [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => 29000.00,
            'estado' => 'efectiva'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ventas', [
            'id' => $this->venta->id,
            'monto' => 29000.00
        ]);
    }

    public function test_update_venta_error_procedimiento()
    {
        // Forzar error enviando estado inválido en la ruta
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedorToken}"
        ])->putJson("/api/ventas/{$this->venta->id}", [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => -100, // Invalid monto (will fail validation)
            'estado' => 'efectiva'
        ]);

        $response->assertStatus(422);
    }

    public function test_eliminar_y_restaurar_venta()
    {
        Queue::fake();

        // 1. Eliminar
        $responseDelete = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->deleteJson("/api/ventas/{$this->venta->id}");

        $responseDelete->assertStatus(200);
        $this->assertSoftDeleted('ventas', ['id' => $this->venta->id]);

        // 2. Restaurar
        $responseRestore = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->postJson("/api/ventas/{$this->venta->id}/restore");

        $responseRestore->assertStatus(200);
        $this->assertDatabaseHas('ventas', [
            'id' => $this->venta->id,
            'deleted_at' => null
        ]);
    }
}
