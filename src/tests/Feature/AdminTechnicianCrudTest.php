<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuditService;
use App\Services\BalanceService;
use App\Services\UserService;
use App\Services\WorkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTechnicianCrudTest extends TestCase
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
     * Test: Admin puede crear un técnico.
     */
    public function test_admin_can_create_technician(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.technicians.store'), [
            'name' => 'Técnico Test',
            'email' => 'tecnico@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success');

        // Verificar que se creó el técnico
        $technician = User::where('email', 'tecnico@test.com')->first();
        $this->assertNotNull($technician);
        $this->assertEquals('technician', $technician->role);
        $this->assertEquals('Técnico Test', $technician->name);
        $this->assertTrue($technician->is_active);
    }

    /**
     * Test: Admin puede actualizar un técnico.
     */
    public function test_admin_can_update_technician(): void
    {
        $this->actingAs($this->admin);

        // Crear técnico
        $technician = $this->userService->createTechnician([
            'name' => 'Técnico Original',
            'email' => 'original@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response = $this->put(route('admin.technicians.update', $technician), [
            'name' => 'Técnico Actualizado',
            'email' => 'actualizado@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success');

        // Verificar actualización
        $technician->refresh();
        $this->assertEquals('Técnico Actualizado', $technician->name);
        $this->assertEquals('actualizado@test.com', $technician->email);
    }

    /**
     * Test: Admin puede "eliminar" un técnico sin actividad (eliminación física).
     */
    public function test_admin_can_delete_technician_without_activity(): void
    {
        $this->actingAs($this->admin);

        // Crear técnico sin actividad
        $technician = $this->userService->createTechnician([
            'name' => 'Técnico Sin Actividad',
            'email' => 'sinactividad@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $technicianId = $technician->id;

        $response = $this->delete(route('admin.technicians.destroy', $technician));

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success');

        // Verificar eliminación física
        $this->assertNull(User::find($technicianId));
    }

    /**
     * Test: Admin "elimina" un técnico con actividad (desactivación).
     */
    public function test_admin_cannot_delete_technician_with_activity(): void
    {
        $this->actingAs($this->admin);

        // Crear técnico con actividad (work report)
        $technician = $this->userService->createTechnician([
            'name' => 'Técnico Con Actividad',
            'email' => 'conactividad@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        // Crear un cliente y un parte de trabajo para el técnico (actividad)
        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );
        $workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
        $workReportService->create($client, $technician, 'Test', 'Test');

        $technicianId = $technician->id;

        $response = $this->delete(route('admin.technicians.destroy', $technician));

        $response->assertRedirect(route('admin.technicians.index'));
        $response->assertSessionHas('success');

        // Verificar que NO se eliminó físicamente, solo se desactivó
        $this->assertNotNull(User::find($technicianId));
        $technician->refresh();
        $this->assertFalse($technician->is_active);
    }

    /**
     * Test: Admin puede ver la lista de técnicos.
     */
    public function test_admin_can_view_technicians_index(): void
    {
        $this->actingAs($this->admin);

        // Crear algunos técnicos
        $technician1 = $this->userService->createTechnician([
            'name' => 'Técnico 1',
            'email' => 'tech1@test.com',
            'password' => 'pass',
            'is_active' => true,
        ]);
        $technician2 = $this->userService->createTechnician([
            'name' => 'Técnico 2',
            'email' => 'tech2@test.com',
            'password' => 'pass',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.technicians.index'));

        $response->assertStatus(200);
        $response->assertSee('Técnico 1');
        $response->assertSee('Técnico 2');
    }

    /**
     * Test: Admin puede ver el detalle de un técnico.
     */
    public function test_admin_can_view_technician_detail(): void
    {
        $this->actingAs($this->admin);

        $technician = $this->userService->createTechnician([
            'name' => 'Técnico Detail',
            'email' => 'detail@test.com',
            'password' => 'pass',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.technicians.show', $technician));

        $response->assertStatus(200);
        $response->assertSee('Técnico Detail');
    }
}
