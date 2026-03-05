<?php

namespace Tests\Feature\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Admin puede ver cualquier cliente
     */
    public function test_admin_can_view_any_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);

        $this->assertTrue($admin->can('view', $client));
        $this->assertTrue($admin->can('viewBalance', $client));
        $this->assertTrue($admin->can('viewWorkReports', $client));
    }

    /**
     * Test: Client puede ver solo su propio cliente
     */
    public function test_client_can_view_only_own_client(): void
    {
        $clientUser1 = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client1@test.com',
        ]);
        $client1 = Client::create([
            'name' => 'Cliente 1',
            'email' => 'client1@test.com',
            'user_id' => $clientUser1->id,
        ]);

        $clientUser2 = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client2@test.com',
        ]);
        $client2 = Client::create([
            'name' => 'Cliente 2',
            'email' => 'client2@test.com',
            'user_id' => $clientUser2->id,
        ]);

        // Cliente 1 puede ver su propio cliente
        $this->assertTrue($clientUser1->can('view', $client1));
        $this->assertTrue($clientUser1->can('viewBalance', $client1));
        $this->assertTrue($clientUser1->can('viewWorkReports', $client1));

        // Cliente 1 NO puede ver el cliente 2
        $this->assertFalse($clientUser1->can('view', $client2));
        $this->assertFalse($clientUser1->can('viewBalance', $client2));
        $this->assertFalse($clientUser1->can('viewWorkReports', $client2));
    }

    /**
     * Test: Client sin cliente asociado no puede ver nada
     */
    public function test_client_without_client_cannot_view(): void
    {
        $clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'client@test.com',
            'user_id' => null, // Sin asociación
        ]);

        $this->assertFalse($clientUser->can('view', $client));
        $this->assertFalse($clientUser->can('viewBalance', $client));
        $this->assertFalse($clientUser->can('viewWorkReports', $client));
    }

    /**
     * Test: Solo admin puede crear/actualizar/eliminar clientes
     */
    public function test_only_admin_can_manage_clients(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $technician = User::factory()->create(['role' => 'technician']);
        $clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);

        // Solo admin puede crear/actualizar/eliminar
        $this->assertTrue($admin->can('create', Client::class));
        $this->assertTrue($admin->can('update', $client));
        $this->assertTrue($admin->can('delete', $client));

        $this->assertFalse($technician->can('create', Client::class));
        $this->assertFalse($technician->can('update', $client));
        $this->assertFalse($technician->can('delete', $client));

        $this->assertFalse($clientUser->can('create', Client::class));
        $this->assertFalse($clientUser->can('update', $client));
        $this->assertFalse($clientUser->can('delete', $client));
    }
}
