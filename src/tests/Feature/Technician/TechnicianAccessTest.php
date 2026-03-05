<?php

namespace Tests\Feature\Technician;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Usuario no autenticado no puede acceder a rutas technician
     */
    public function test_unauthenticated_user_cannot_access_technician_routes(): void
    {
        $response = $this->get('/technician');
        $response->assertRedirect(route('login'));

        $response = $this->get('/technician/work-reports');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Usuario admin no puede acceder a rutas technician
     */
    public function test_admin_cannot_access_technician_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/technician');
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($admin)->get('/technician/work-reports');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test: Usuario client no puede acceder a rutas technician
     */
    public function test_client_cannot_access_technician_routes(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->get('/technician');
        $response->assertRedirect(route('client.dashboard'));

        $response = $this->actingAs($client)->get('/technician/work-reports');
        $response->assertRedirect(route('client.dashboard'));
    }

    /**
     * Test: Technician puede acceder a rutas technician
     */
    public function test_technician_can_access_technician_routes(): void
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->actingAs($technician)->get('/technician');
        $response->assertStatus(200);

        $response = $this->actingAs($technician)->get('/technician/work-reports');
        $response->assertStatus(200);

        $response = $this->actingAs($technician)->get('/technician/work-reports/create');
        $response->assertStatus(200);
    }
}
