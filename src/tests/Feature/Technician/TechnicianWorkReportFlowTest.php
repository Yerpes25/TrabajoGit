<?php

namespace Tests\Feature\Technician;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianWorkReportFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
    }

    /**
     * Test: Técnico solo ve sus partes
     */
    public function test_technician_only_sees_own_work_reports(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);

        // Crear parte del técnico autenticado
        $ownReport = $this->workReportService->create($client, $this->technician);

        // Crear parte de otro técnico
        $otherTechnician = User::factory()->create(['role' => 'technician']);
        $otherReport = $this->workReportService->create($client, $otherTechnician);

        $response = $this->actingAs($this->technician)->get(route('technician.work-reports.index'));

        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($ownReport));
        $this->assertFalse($workReports->contains($otherReport));
    }

    /**
     * Test: Técnico crea parte y lo ve en listado
     */
    public function test_technician_can_create_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.store'), [
            'client_id' => $client->id,
            'title' => 'Parte de prueba',
            'description' => 'Descripción del parte',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('work_reports', [
            'client_id' => $client->id,
            'technician_id' => $this->technician->id,
            'title' => 'Parte de prueba',
            'status' => WorkReport::STATUS_PAUSED,
        ]);
    }

    /**
     * Test: Técnico puede iniciar parte (start)
     */
    public function test_technician_can_start_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.start', $workReport));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertNotNull($workReport->active_started_at);
    }

    /**
     * Test: Técnico puede pausar parte (pause)
     */
    public function test_technician_can_pause_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);
        $this->workReportService->start($workReport, $this->technician->id);
        sleep(2); // Esperar para que haya tiempo acumulado

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.pause', $workReport));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_PAUSED, $workReport->status);
        $this->assertNull($workReport->active_started_at);
        $this->assertGreaterThan(0, $workReport->total_seconds);
    }

    /**
     * Test: Técnico puede reanudar parte (resume)
     */
    public function test_technician_can_resume_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);
        $this->workReportService->start($workReport, $this->technician->id);
        $this->workReportService->pause($workReport, $this->technician->id);

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.resume', $workReport));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertNotNull($workReport->active_started_at);
    }

    /**
     * Test: Técnico puede finalizar parte (finish)
     */
    public function test_technician_can_finish_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);
        $this->workReportService->start($workReport, $this->technician->id);
        sleep(1);

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.finish', $workReport));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_FINISHED, $workReport->status);
        $this->assertNotNull($workReport->finished_at);
    }

    /**
     * Test: Regla 1 activo se cumple (si intenta otro start/resume, error claro)
     */
    public function test_technician_cannot_start_second_active_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport1 = $this->workReportService->create($client, $this->technician);
        $this->workReportService->start($workReport1, $this->technician->id);

        $workReport2 = $this->workReportService->create($client, $this->technician);

        $response = $this->actingAs($this->technician)->post(route('technician.work-reports.start', $workReport2));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('activo', $errorMessage);
    }

    /**
     * Test: Técnico edita campos básicos del parte
     */
    public function test_technician_can_edit_basic_fields(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);

        // Regla: summary solo se puede editar en finished, no en paused
        // Solo editamos title y description en paused
        $response = $this->actingAs($this->technician)->put(route('technician.work-reports.update', $workReport), [
            'title' => 'Título actualizado',
            'description' => 'Descripción actualizada',
            // summary no se puede editar en paused según reglas de negocio
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $workReport->refresh();
        $this->assertEquals('Título actualizado', $workReport->title);
        $this->assertEquals('Descripción actualizada', $workReport->description);
        // summary no se editó porque el parte está paused
    }

    /**
     * Test: Técnico no puede ver partes de otros técnicos
     */
    public function test_technician_cannot_view_other_technician_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $otherTechnician = User::factory()->create(['role' => 'technician']);
        $otherReport = $this->workReportService->create($client, $otherTechnician);

        $response = $this->actingAs($this->technician)->get(route('technician.work-reports.show', $otherReport));

        $response->assertStatus(403);
    }
}
