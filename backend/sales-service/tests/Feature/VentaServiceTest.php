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
use Illuminate\Support\Facades\Queue;
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
        Queue::fake();

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

    /** @test */
    public function actualizar_venta_cambio_vehiculo_modifica_stocks()
    {
        // 1. Crear otro vehículo para el intercambio
        $otroVehiculo = Vehiculo::create([
            'marca' => 'Hyundai',
            'modelo' => 'Tucson',
            'anio' => 2026,
            'precio' => 30000.00,
            'stock' => 3
        ]);

        // 2. Registrar venta efectiva original (resta stock de $this->vehiculo: de 5 a 4)
        $venta = $this->ventaService->createVenta([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 19500.00,
            'estado' => 'efectiva'
        ]);

        $this->assertEquals(4, $this->vehiculo->fresh()->stock);
        $this->assertEquals(3, $otroVehiculo->fresh()->stock);

        // 3. Actualizar la venta para que sea del otro vehículo
        $this->ventaService->updateVenta($venta->id, [
            'vehiculo_id' => $otroVehiculo->id,
            'estado' => 'efectiva'
        ], $this->vendedor->id);

        // 4. Verificar que se devolvió stock al original y se quitó al nuevo
        $this->assertEquals(5, $this->vehiculo->fresh()->stock); // Devuelve
        $this->assertEquals(2, $otroVehiculo->fresh()->stock); // Descuenta
    }

    /** @test */
    public function actualizar_venta_prospecto_duplicado_falla()
    {
        // 1. Crear otro prospecto
        $otroProspecto = Prospecto::create([
            'nombre' => 'Otro Cliente',
            'email' => 'otro@cliente.com',
            'telefono' => '999111222',
            'vehiculo_id' => $this->vehiculo->id,
            'etapa' => 'prospeccion',
            'empleado_id' => $this->vendedor->id
        ]);

        // 2. Registrar venta para prospecto original
        $venta1 = $this->ventaService->createVenta([
            'prospecto_id' => $this->prospecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 19500.00,
            'estado' => 'efectiva'
        ]);

        // 3. Registrar venta para otroProspecto
        $venta2 = $this->ventaService->createVenta([
            'prospecto_id' => $otroProspecto->id,
            'vehiculo_id' => $this->vehiculo->id,
            'empleado_id' => $this->vendedor->id,
            'monto' => 19500.00,
            'estado' => 'efectiva'
        ]);

        // 4. Intentar actualizar venta1 para asignarla al otroProspecto (debe fallar)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("El nuevo prospecto seleccionado ya tiene una venta asociada.");

        $this->ventaService->updateVenta($venta1->id, [
            'prospecto_id' => $otroProspecto->id
        ], $this->vendedor->id);
    }
}
