<?php

namespace Tests\Feature\Client;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientUserLinkTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
    }

    /**
     * Test: Cambiar email del user NO rompe el portal cliente
     *
     * Regla: La relación User-Client se hace por FK (user_id), no por email.
     * Cambiar el email del usuario no debe afectar la relación.
     */
    public function test_email_change_does_not_break_client_portal(): void
    {
        // Crear usuario cliente y cliente asociado por FK
        $clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);

        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'client@test.com',
            'user_id' => $clientUser->id, // Asociación por FK
        ]);

        // Crear un parte finished para el cliente
        $technician = User::factory()->create(['role' => 'technician']);
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport, null, $technician->id);

        // Verificar que el cliente puede ver su parte antes del cambio de email
        $response = $this->actingAs($clientUser)->get(route('client.work-reports.index'));
        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($workReport));

        // Cambiar el email del usuario
        $clientUser->update(['email' => 'newemail@test.com']);

        // Verificar que el cliente sigue pudiendo ver su parte después del cambio de email
        $response = $this->actingAs($clientUser)->get(route('client.work-reports.index'));
        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($workReport));

        // Verificar que la relación FK sigue funcionando
        $client->refresh();
        $this->assertEquals($clientUser->id, $client->user_id);
        $this->assertEquals('newemail@test.com', $clientUser->email);
        // El email del cliente puede ser diferente (no se actualiza automáticamente)
        $this->assertEquals('client@test.com', $client->email);
    }

    /**
     * Test: La relación FK funciona correctamente
     */
    public function test_fk_relationship_works_correctly(): void
    {
        $clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);

        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'client@test.com',
            'user_id' => $clientUser->id,
        ]);

        // Verificar relación User -> Client
        $this->assertNotNull($clientUser->client);
        $this->assertEquals($client->id, $clientUser->client->id);

        // Verificar relación Client -> User
        $this->assertNotNull($client->user);
        $this->assertEquals($clientUser->id, $client->user->id);
    }

    /**
     * Test: Cliente sin user_id asociado no puede ver partes
     */
    public function test_client_without_user_id_cannot_see_work_reports(): void
    {
        $clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);

        // Cliente sin user_id (no asociado)
        $client = Client::create([
            'name' => 'Cliente Sin Asociar',
            'email' => 'client@test.com',
            'user_id' => null,
        ]);

        // El usuario no debería tener cliente asociado
        $this->assertNull($clientUser->client);

        // El dashboard debería mostrar mensaje de que no hay cliente asociado
        $response = $this->actingAs($clientUser)->get(route('client.dashboard'));
        $response->assertStatus(200);
        $viewData = $response->viewData();
        $this->assertNull($viewData['client'] ?? null);
    }
}
