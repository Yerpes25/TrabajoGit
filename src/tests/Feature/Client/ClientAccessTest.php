<?php

namespace Tests\Feature\Client;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Usuario no autenticado no puede acceder a rutas client
     */
    public function test_unauthenticated_user_cannot_access_client_routes(): void
    {
        $response = $this->get('/client');
        $response->assertRedirect(route('login'));

        $response = $this->get('/client/work-reports');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Usuario admin no puede acceder a rutas client
     */
    public function test_admin_cannot_access_client_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/client');
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($admin)->get('/client/work-reports');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test: Usuario technician no puede acceder a rutas client
     */
    public function test_technician_cannot_access_client_routes(): void
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->actingAs($technician)->get('/client');
        $response->assertRedirect(route('technician.dashboard'));

        $response = $this->actingAs($technician)->get('/client/work-reports');
        $response->assertRedirect(route('technician.dashboard'));
    }

    /**
     * Test: Client puede acceder a rutas client
     */
    public function test_client_can_access_client_routes(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->get('/client');
        $response->assertStatus(200);

        $response = $this->actingAs($client)->get('/client/work-reports');
        $response->assertStatus(200);
    }
}
