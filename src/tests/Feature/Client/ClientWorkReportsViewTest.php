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

class ClientWorkReportsViewTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private Client $client;
    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientUser = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client@test.com',
        ]);
        $this->client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'client@test.com',
            'user_id' => $this->clientUser->id, // Asociación por FK (no por email)
        ]);
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
    }

    /**
     * Test: Cliente solo ve sus partes (y solo finished/validated)
     */
    public function test_client_only_sees_own_finished_and_validated_work_reports(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        // Crear parte in_progress del cliente (no debe aparecer)
        $inProgressReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($inProgressReport, $technician->id);

        // Crear parte finished del cliente
        $this->workReportService->pause($inProgressReport, $technician->id);
        $finishedReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($finishedReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($finishedReport, null, $technician->id);

        // Crear parte validated del cliente
        // Primero añadir saldo al cliente para poder validar
        $balanceService = new BalanceService(new AuditService());
        $balanceService->credit($this->client, 3600, 'test_credit', 'User', $technician->id, $technician->id);
        
        $validatedReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($validatedReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($validatedReport, null, $technician->id);
        $this->workReportService->validate($validatedReport, $technician->id);

        // Crear parte de otro cliente
        $otherClient = Client::create(['name' => 'Otro Cliente', 'email' => 'other@test.com']);
        $otherReport = $this->workReportService->create($otherClient, $technician);
        $this->workReportService->start($otherReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($otherReport, null, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.index'));

        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($finishedReport));
        $this->assertTrue($workReports->contains($validatedReport));
        $this->assertFalse($workReports->contains($inProgressReport));
        $this->assertFalse($workReports->contains($otherReport));
    }

    /**
     * Test: Cliente puede ver listado y detalle
     */
    public function test_client_can_view_list_and_detail(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $workReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($workReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport, null, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('client.work-reports.index');

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.show', $workReport));
        $response->assertStatus(200);
        $response->assertViewIs('client.work-reports.show');
    }

    /**
     * Test: Cliente no puede ver partes in_progress o paused
     */
    public function test_client_cannot_view_in_progress_or_paused_work_reports(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        // Parte in_progress
        $inProgressReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($inProgressReport, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.show', $inProgressReport));
        $response->assertStatus(403);

        // Parte paused (primero pausar el parte anterior)
        $this->workReportService->pause($inProgressReport, $technician->id);
        $pausedReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($pausedReport, $technician->id);
        $this->workReportService->pause($pausedReport, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.show', $pausedReport));
        $response->assertStatus(403);
    }

    /**
     * Test: Cliente no puede ver partes de otros clientes
     */
    public function test_client_cannot_view_other_client_work_reports(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $otherClient = Client::create(['name' => 'Otro Cliente', 'email' => 'other@test.com']);
        $otherReport = $this->workReportService->create($otherClient, $technician);
        $this->workReportService->start($otherReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($otherReport, null, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.show', $otherReport));
        $response->assertStatus(403);
    }

    /**
     * Test: Cliente ve evidencias con enlace
     */
    public function test_client_can_view_evidences_with_link(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $workReport = $this->workReportService->create($this->client, $technician);
        $this->workReportService->start($workReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport, null, $technician->id);

        $response = $this->actingAs($this->clientUser)->get(route('client.work-reports.show', $workReport));
        $response->assertStatus(200);
        $response->assertViewHas('workReport');
    }
}
