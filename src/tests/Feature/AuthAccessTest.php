<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Login funciona correctamente
     */
    public function test_login_works_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test: Usuario inactivo no puede hacer login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test: Admin es redirigido a /admin después del login
     */
    public function test_admin_redirected_to_admin_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test: Technician es redirigido a /technician después del login
     */
    public function test_technician_redirected_to_technician_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'tech@example.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'tech@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('technician.dashboard'));
    }

    /**
     * Test: Client es redirigido a /client después del login
     */
    public function test_client_redirected_to_client_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'client@example.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'client@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('client.dashboard'));
    }

    /**
     * Test: Admin puede acceder a /admin
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin');
    }

    /**
     * Test: Technician no puede acceder a /admin
     */
    public function test_technician_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('technician.dashboard'));
    }

    /**
     * Test: Client no puede acceder a /admin
     */
    public function test_client_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('client.dashboard'));
    }

    /**
     * Test: Technician puede acceder a /technician
     */
    public function test_technician_can_access_technician_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/technician');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.technician');
    }

    /**
     * Test: Client puede acceder a /client
     */
    public function test_client_can_access_client_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/client');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.client');
    }

    /**
     * Test: Usuario no autenticado no puede acceder a dashboards
     */
    public function test_unauthenticated_user_cannot_access_dashboards(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));

        $response = $this->get('/technician');
        $response->assertRedirect(route('login'));

        $response = $this->get('/client');
        $response->assertRedirect(route('login'));
    }
}
