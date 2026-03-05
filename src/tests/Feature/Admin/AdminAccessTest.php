<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Usuario no autenticado no puede acceder a rutas admin
     */
    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));

        $response = $this->get('/admin/users');
        $response->assertRedirect(route('login'));

        $response = $this->get('/admin/clients');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Usuario technician no puede acceder a rutas admin
     */
    public function test_technician_cannot_access_admin_routes(): void
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->actingAs($technician)->get('/admin');
        $response->assertRedirect(route('technician.dashboard'));

        $response = $this->actingAs($technician)->get('/admin/users');
        $response->assertRedirect(route('technician.dashboard'));
    }

    /**
     * Test: Usuario client no puede acceder a rutas admin
     */
    public function test_client_cannot_access_admin_routes(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->get('/admin');
        $response->assertRedirect(route('client.dashboard'));

        $response = $this->actingAs($client)->get('/admin/users');
        $response->assertRedirect(route('client.dashboard'));
    }

    /**
     * Test: Admin puede acceder a rutas admin
     */
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/clients');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/work-reports');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/audit-logs');
        $response->assertStatus(200);
    }
}
