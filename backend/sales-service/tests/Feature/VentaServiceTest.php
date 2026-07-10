<?php

namespace Tests\Feature;

use App\Models\Prospecto;
use App\Models\Vehiculo;
use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Venta;
use App\Services\VentaService;
use App\Repositories\VentaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $ventaService;
    protected $vendedor;
    protected $vehiculo;
    protected $prospecto;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles y permisos
        $roleAdmin = Rol::create(['id' => 1, 'nombre' => 'administrador']);
        $roleVendedor = Rol::create(['id' => 2, 'nombre' => 'vendedor']);

        $permVerPropios = Permiso::create(['nombre' => 'ver_ventas_propias']);
        $permGestPropios = Permiso::create(['nombre' => 'gestionar_ventas_propias']);
        $roleVendedor->permisos()->attach([$permVerPropios->id, $permGestPropios->id]);

        // 1. Crear datos iniciales necesarios
        $this->vendedor = Empleado::create([
            'nombre' => 'Asesor Test',
            'email' => 'asesor.test@automotriz.com',
            'password' => bcrypt('password123'),
            'rol_id' => 2
        ]);

        $this->vehiculo = Vehiculo::create([
            'marca' => 'Toyota',
            'modelo' => 'Yaris',
            'anio' => 2026,
            'precio' => 20000.00,
            'stock' => 5
        ]);

        $this->prospecto = Prospecto::create([
            'nombre' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'telefono' => '999888777',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'negociacion',
            'empleado_id' => $this->vendedor->id
        ]);

        $ventaRepository = new VentaRepository();
        $this->ventaService = new VentaService($ventaRepository);
    }

    /** @test */
    public function registrar_venta_efectiva_correctamente_y_decrementa_stock()
    {
        $ventaData = [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 19500.00,
            'estado' => 'efectiva',
            'motivo_perdida' => null
        ];

        // Ejecutar
        $venta = $this->ventaService->createVenta($ventaData);

        // Aserciones
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'efectiva',
            'monto' => 19500.00
        ]);

        // Verificar que el stock se decrementó
        $this->assertEquals(4, $this->vehiculo->fresh()->stock);

        // Verificar que la etapa del prospecto cambió a cierre
        $this->assertEquals('cierre', $this->prospecto->fresh()->etapa);
    }

    /** @test */
    public function registrar_venta_efectiva_falla_si_no_hay_stock()
    {
        // Agotar stock
        $this->vehiculo->update(['stock' => 0]);

        $ventaData = [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 19500.00,
            'estado' => 'efectiva',
            'motivo_perdida' => null
        ];

        // Ejecutar y verificar que lanza excepción
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No hay stock suficiente del vehículo seleccionado.");

        $this->ventaService->createVenta($ventaData);
    }

    /** @test */
    public function registrar_venta_fallida_guarda_motivo_y_no_afecta_stock()
    {
        $ventaData = [
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 20000.00,
            'estado' => 'fallida',
            'motivo_perdida' => 'El cliente compró un auto usado en otro lugar.'
        ];

        // Ejecutar
        $venta = $this->ventaService->createVenta($ventaData);

        // Aserciones
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'fallida',
            'motivo_perdida' => 'El cliente compró un auto usado en otro lugar.'
        ]);

        // Verificar que el stock NO cambió
        $this->assertEquals(5, $this->vehiculo->fresh()->stock);

        // Verificar que la etapa del prospecto igualmente cambió a cierre (fin del ciclo)
        $this->assertEquals('cierre', $this->prospecto->fresh()->etapa);
    }
}
