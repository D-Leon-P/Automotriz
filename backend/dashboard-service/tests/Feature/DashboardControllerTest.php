<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Venta;
use App\Models\Prospecto;
use App\Models\Seguro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendedor1;
    protected $vendedor2;
    protected $adminToken;
    protected $vendedor1Token;
    protected $vendedor2Token;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerTodos = Permiso::create(['nombre' => 'ver_dashboard_todos']);
        $permVerPropio = Permiso::create(['nombre' => 'ver_dashboard_propio']);

        $roleAdmin->permisos()->attach([$permVerTodos->id]);
        $roleVendedor->permisos()->attach([$permVerPropio->id]);

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

        // Seed raw vehiculos (needed for foreign keys in prospectos/ventas)
        DB::table('vehiculos')->insert([
            ['id' => 1, 'marca' => 'Toyota', 'modelo' => 'Corolla', 'anio' => 2026, 'precio' => 25000.00, 'stock' => 10],
            ['id' => 2, 'marca' => 'Hyundai', 'modelo' => 'Tucson', 'anio' => 2026, 'precio' => 31000.00, 'stock' => 5],
        ]);

        // Seed prospectos
        DB::table('prospectos')->insert([
            ['id' => 1, 'nombre' => 'Prospecto 1', 'email' => 'p1@example.com', 'telefono' => '111', 'vehiculo_id' => 1, 'etapa' => 'prospeccion', 'empleado_id' => 2],
            ['id' => 2, 'nombre' => 'Prospecto 2', 'email' => 'p2@example.com', 'telefono' => '222', 'vehiculo_id' => 2, 'etapa' => 'cierre', 'empleado_id' => 2],
            ['id' => 3, 'nombre' => 'Prospecto 3', 'email' => 'p3@example.com', 'telefono' => '333', 'vehiculo_id' => 1, 'etapa' => 'prospeccion', 'empleado_id' => 3],
        ]);

        // Seed ventas
        DB::table('ventas')->insert([
            ['id' => 10, 'prospecto_id' => 2, 'vehiculo_id' => 2, 'empleado_id' => 2, 'monto' => 31000.00, 'estado' => 'efectiva'],
        ]);

        // Seed seguros
        DB::table('seguros')->insert([
            ['id' => 100, 'venta_id' => 10, 'tipo_seguro' => 'todo_riesgo', 'prima_esperada' => 1200.00, 'prima_real' => 1200.00, 'estado' => 'vendido'],
        ]);
    }

    public function test_vendedor_recibe_metricas_propias()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->vendedor1Token}"
        ])->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'kpis' => [
                    'total_prospectos' => 2,
                    'ventas_realizadas' => 1,
                    'tasa_conversion' => 50
                ]
            ]);
    }

    public function test_admin_recibe_metricas_globales()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->adminToken}"
        ])->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'kpis' => [
                    'total_prospectos' => 3,
                    'ventas_realizadas' => 1,
                    'tasa_conversion' => 33.33
                ]
            ]);
    }

    public function test_dashboard_falla_sin_token()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }
}
