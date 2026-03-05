<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BalanceService;
use App\Services\UserService;
use App\Services\WorkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->userService = new UserService();
    }

    /**
     * Test: Admin puede crear un cliente completo (User + Client + ClientProfile).
     */
    public function test_admin_can_create_client(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.clients.store'), [
            'name' => 'Usuario Cliente',
            'email' => 'cliente@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'client_name' => 'Cliente Test',
            'legal_name' => 'Cliente Test S.L.',
            'tax_id' => 'B12345678',
            'client_email' => 'cliente@empresa.com',
            'phone' => '123456789',
            'address' => 'Calle Test 123',
            'notes' => 'Notas del cliente',
        ]);

        $response->assertRedirect(route('admin.clients.show', Client::where('name', 'Cliente Test')->first()));
        $response->assertSessionHas('success');

        // Verificar que se creó el User
        $user = User::where('email', 'cliente@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('client', $user->role);
        $this->assertEquals('Usuario Cliente', $user->name);
        $this->assertTrue($user->is_active);

        // Verificar que se creó el Client
        $client = Client::where('name', 'Cliente Test')->first();
        $this->assertNotNull($client);
        $this->assertEquals($user->id, $client->user_id);
        $this->assertEquals('Cliente Test S.L.', $client->legal_name);
        $this->assertEquals('B12345678', $client->tax_id);

        // Verificar que se creó el ClientProfile
        $profile = ClientProfile::where('client_id', $client->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals(0, $profile->balance_seconds);
    }

    /**
     * Test: Admin puede actualizar un cliente (User + Client).
     */
    public function test_admin_can_update_client(): void
    {
        $this->actingAs($this->admin);

        // Crear cliente
        $client = $this->userService->createClient(
            [
                'name' => 'Usuario Original',
                'email' => 'original@test.com',
                'password' => 'password123',
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Original',
                'phone' => '111111111',
            ]
        );

        $response = $this->put(route('admin.clients.update', $client), [
            'name' => 'Usuario Actualizado',
            'email' => 'actualizado@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'is_active' => true,
            'client_name' => 'Cliente Actualizado',
            'phone' => '999999999',
        ]);

        $response->assertRedirect(route('admin.clients.show', $client));
        $response->assertSessionHas('success');

        // Verificar actualización
        $client->refresh();
        $user = $client->user;
        $this->assertEquals('Usuario Actualizado', $user->name);
        $this->assertEquals('actualizado@test.com', $user->email);
        $this->assertEquals('Cliente Actualizado', $client->name);
        $this->assertEquals('999999999', $client->phone);
    }

    /**
     * Test: Admin puede "eliminar" un cliente sin actividad (eliminación física).
     */
    public function test_admin_can_delete_client_without_activity(): void
    {
        $this->actingAs($this->admin);

        // Crear cliente sin actividad
        $client = $this->userService->createClient(
            [
                'name' => 'Usuario Sin Actividad',
                'email' => 'sinactividad@test.com',
                'password' => 'password123',
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Sin Actividad',
            ]
        );

        $userId = $client->user_id;
        $clientId = $client->id;
        $profileId = $client->profile->id;

        $response = $this->delete(route('admin.clients.destroy', $client));

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('success');

        // Verificar eliminación física
        $this->assertNull(Client::find($clientId));
        $this->assertNull(User::find($userId));
        $this->assertNull(ClientProfile::find($profileId));
    }

    /**
     * Test: Admin "elimina" un cliente con actividad (desactivación).
     */
    public function test_admin_cannot_delete_client_with_activity(): void
    {
        $this->actingAs($this->admin);

        // Crear cliente con actividad (work report)
        $client = $this->userService->createClient(
            [
                'name' => 'Usuario Con Actividad',
                'email' => 'conactividad@test.com',
                'password' => 'password123',
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Con Actividad',
            ]
        );

        // Crear un parte de trabajo para el cliente (actividad)
        $technician = User::factory()->create(['role' => 'technician', 'is_active' => true]);
        $workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
        $workReportService->create($client, $technician, 'Test', 'Test');

        $userId = $client->user_id;
        $clientId = $client->id;

        $response = $this->delete(route('admin.clients.destroy', $client));

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('success');

        // Verificar que NO se eliminó físicamente, solo se desactivó
        $this->assertNotNull(Client::find($clientId));
        $this->assertNotNull(User::find($userId));
        $user = User::find($userId);
        $this->assertFalse($user->is_active);
    }

    /**
     * Test: Admin puede ver la lista de clientes.
     */
    public function test_admin_can_view_clients_index(): void
    {
        $this->actingAs($this->admin);

        // Crear algunos clientes
        $client1 = $this->userService->createClient(
            ['name' => 'User1', 'email' => 'user1@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client1']
        );
        $client2 = $this->userService->createClient(
            ['name' => 'User2', 'email' => 'user2@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client2']
        );

        $response = $this->get(route('admin.clients.index'));

        $response->assertStatus(200);
        $response->assertSee('Client1');
        $response->assertSee('Client2');
    }

    /**
     * Test: Admin puede ver el detalle de un cliente.
     */
    public function test_admin_can_view_client_detail(): void
    {
        $this->actingAs($this->admin);

        $client = $this->userService->createClient(
            ['name' => 'User', 'email' => 'user@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Detail']
        );

        $response = $this->get(route('admin.clients.show', $client));

        $response->assertStatus(200);
        $response->assertSee('Client Detail');
    }
}
