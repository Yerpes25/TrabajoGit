<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\WorkReportEvent;
use App\Models\AuditLog;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de trazabilidad y permisos para edición de partes.
 *
 * Casos de prueba:
 * 1) Technician edita title/description en paused => OK + evento edit + audit log
 * 2) Technician intenta editar summary en in_progress => falla (según regla aplicada)
 * 3) Technician edita summary en finished => OK + evento + audit
 * 4) Edit en validated => bloqueado (403 o InvalidArgument)
 * 5) Admin puede editar parte de otro técnico (según política) pero respeta validated bloqueado
 */
class WorkReportEditTraceTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $workReportService;
    private User $technician;
    private User $admin;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear servicios
        $balanceService = new BalanceService(new AuditService());
        $this->workReportService = new WorkReportService($balanceService, new AuditService());

        // Crear usuarios de prueba
        $this->technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Crear cliente
        $this->client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'client@test.com',
        ]);
    }

    /**
     * Test: Technician edita title/description en paused => OK + evento edit + audit log
     */
    public function test_technician_can_edit_title_and_description_in_paused_status(): void
    {
        // Crear parte en estado paused
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $this->technician->id,
            'status' => WorkReport::STATUS_PAUSED,
            'title' => 'Título original',
            'description' => 'Descripción original',
            'total_seconds' => 0,
        ]);

        // Editar title y description
        $updatedReport = $this->workReportService->updateDetails(
            $workReport,
            [
                'title' => 'Título actualizado',
                'description' => 'Descripción actualizada',
            ],
            $this->technician->id
        );

        // Verificar que se actualizó
        $this->assertEquals('Título actualizado', $updatedReport->title);
        $this->assertEquals('Descripción actualizada', $updatedReport->description);

        // Verificar que se creó evento edit
        $editEvent = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_EDIT)
            ->first();

        $this->assertNotNull($editEvent);
        $this->assertEquals($this->technician->id, $editEvent->created_by);
        $this->assertArrayHasKey('diff', $editEvent->metadata);
        $this->assertArrayHasKey('title', $editEvent->metadata['diff']);
        $this->assertArrayHasKey('description', $editEvent->metadata['diff']);

        // Verificar que se creó audit log
        $auditLog = AuditLog::where('entity_type', 'WorkReport')
            ->where('entity_id', $workReport->id)
            ->where('event', 'work_report_edited')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->technician->id, $auditLog->actor_id);
    }

    /**
     * Test: Technician intenta editar summary en in_progress => falla
     */
    public function test_technician_cannot_edit_summary_in_in_progress_status(): void
    {
        // Crear parte en estado in_progress
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $this->technician->id,
            'status' => WorkReport::STATUS_IN_PROGRESS,
        ]);

        // Intentar editar summary (debe fallar)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede editar 'summary' en un parte con estado 'in_progress'");

        $this->workReportService->updateDetails(
            $workReport,
            ['summary' => 'Resumen no permitido'],
            $this->technician->id
        );
    }

    /**
     * Test: Technician edita summary en finished => OK + evento + audit
     */
    public function test_technician_can_edit_summary_in_finished_status(): void
    {
        // Crear parte en estado finished
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $this->technician->id,
            'status' => WorkReport::STATUS_FINISHED,
            'summary' => 'Resumen original',
        ]);

        // Editar summary
        $updatedReport = $this->workReportService->updateDetails(
            $workReport,
            ['summary' => 'Resumen actualizado'],
            $this->technician->id
        );

        // Verificar que se actualizó
        $this->assertEquals('Resumen actualizado', $updatedReport->summary);

        // Verificar que se creó evento edit
        $editEvent = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_EDIT)
            ->first();

        $this->assertNotNull($editEvent);
        $this->assertArrayHasKey('diff', $editEvent->metadata);
        $this->assertArrayHasKey('summary', $editEvent->metadata['diff']);

        // Verificar que se creó audit log
        $auditLog = AuditLog::where('entity_type', 'WorkReport')
            ->where('entity_id', $workReport->id)
            ->where('event', 'work_report_edited')
            ->first();

        $this->assertNotNull($auditLog);
    }

    /**
     * Test: Edit en validated => bloqueado (InvalidArgumentException)
     */
    public function test_cannot_edit_validated_work_report(): void
    {
        // Crear parte en estado validated
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $this->technician->id,
            'status' => WorkReport::STATUS_VALIDATED,
        ]);

        // Intentar editar (debe fallar)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede editar un parte que está en estado 'validated'");

        $this->workReportService->updateDetails(
            $workReport,
            ['title' => 'Título no permitido'],
            $this->technician->id
        );
    }

    /**
     * Test: Admin puede editar parte de otro técnico pero respeta validated bloqueado
     */
    public function test_admin_can_edit_other_technician_work_report_but_respects_validated_block(): void
    {
        // Crear otro técnico
        $otherTechnician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        // Crear parte de otro técnico en estado paused
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $otherTechnician->id,
            'status' => WorkReport::STATUS_PAUSED,
            'title' => 'Título original',
        ]);

        // Admin puede editar
        $updatedReport = $this->workReportService->updateDetails(
            $workReport,
            ['title' => 'Título editado por admin'],
            $this->admin->id
        );

        $this->assertEquals('Título editado por admin', $updatedReport->title);

        // Verificar evento con admin como creador
        $editEvent = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_EDIT)
            ->first();

        $this->assertNotNull($editEvent);
        $this->assertEquals($this->admin->id, $editEvent->created_by);

        // Validar el parte
        $workReport->update(['status' => WorkReport::STATUS_VALIDATED]);

        // Admin NO puede editar validated (bloqueado)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede editar un parte que está en estado 'validated'");

        $this->workReportService->updateDetails(
            $workReport,
            ['title' => 'Título no permitido'],
            $this->admin->id
        );
    }

    /**
     * Test: No se pueden editar campos de tiempo
     */
    public function test_cannot_edit_time_fields(): void
    {
        // Crear parte en estado paused
        $workReport = WorkReport::create([
            'client_id' => $this->client->id,
            'technician_id' => $this->technician->id,
            'status' => WorkReport::STATUS_PAUSED,
            'total_seconds' => 1000,
        ]);

        $originalTotalSeconds = $workReport->total_seconds;

        // Intentar editar (total_seconds no está en allowedFields, se ignora)
        $updatedReport = $this->workReportService->updateDetails(
            $workReport,
            [
                'title' => 'Título actualizado',
                'total_seconds' => 9999, // Intentar cambiar tiempo (debe ignorarse)
            ],
            $this->technician->id
        );

        // Verificar que title se actualizó pero total_seconds NO
        $this->assertEquals('Título actualizado', $updatedReport->title);
        $this->assertEquals($originalTotalSeconds, $updatedReport->total_seconds);
    }
}
