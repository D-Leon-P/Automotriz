<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Services\ClienteService;
use App\Repositories\ClienteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $clienteService;

    protected function setUp(): void
    {
        parent::setUp();
        $clienteRepository = new ClienteRepository();
        $this->clienteService = new ClienteService($clienteRepository);
    }

    /** @test */
    public function crear_cliente_nuevo_correctamente()
    {
        $data = [
            'tipo_documento' => 'DNI',
            'documento' => '73060466',
            'nombre' => 'Juan Jeferson',
            'apellido' => 'Palomino Ortiz',
            'fecha_nacimiento' => '2002-03-13',
            'email' => 'jefersonpalomino026@gmail.com',
            'telefono' => '919466739',
            'direccion' => 'Las Magnolias'
        ];

        $cliente = $this->clienteService->createCliente($data);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'documento' => '73060466',
            'email' => 'jefersonpalomino026@gmail.com',
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function crear_cliente_con_documento_de_cliente_eliminado_restaura_y_actualiza()
    {
        $data = [
            'tipo_documento' => 'DNI',
            'documento' => '73060466',
            'nombre' => 'Juan Jeferson',
            'apellido' => 'Palomino Ortiz',
            'fecha_nacimiento' => '2002-03-13',
            'email' => 'jefersonpalomino026@gmail.com',
            'telefono' => '919466739',
            'direccion' => 'Las Magnolias'
        ];

        // 1. Crear el cliente
        $cliente = $this->clienteService->createCliente($data);

        // 2. Eliminar (soft-delete) el cliente
        $this->clienteService->deleteCliente($cliente->id);

        $this->assertSoftDeleted('clientes', [
            'id' => $cliente->id
        ]);

        // 3. Crear nuevamente con el mismo documento pero diferentes datos (ej. telefono y direccion distintos)
        $newData = $data;
        $newData['telefono'] = '911111111';
        $newData['direccion'] = 'Nueva Direccion';

        $restoredCliente = $this->clienteService->createCliente($newData);

        // 4. Verificar que es el mismo id, está restaurado y los campos cambiaron
        $this->assertEquals($cliente->id, $restoredCliente->id);
        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'telefono' => '911111111',
            'direccion' => 'Nueva Direccion',
            'deleted_at' => null
        ]);

        // Verificar que no se creó otro registro
        $this->assertEquals(1, Cliente::withTrashed()->count());
    }
}
