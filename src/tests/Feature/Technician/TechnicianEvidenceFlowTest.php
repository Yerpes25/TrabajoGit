<?php

namespace Tests\Feature\Technician;

use App\Models\Client;
use App\Models\Evidence;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use App\Services\AuditService;
use App\Services\EvidenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicianEvidenceFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;
    private WorkReportService $workReportService;
    private EvidenceService $evidenceService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
        $this->evidenceService = new EvidenceService(new AuditService());
    }

    /**
     * Test: Técnico sube evidencia
     */
    public function test_technician_can_upload_evidence(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);
        $file = UploadedFile::fake()->create('evidence.pdf', 100);

        $response = $this->actingAs($this->technician)->post(
            route('technician.work-reports.evidences.upload', $workReport),
            ['file' => $file]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evidences', [
            'work_report_id' => $workReport->id,
            'uploaded_by' => $this->technician->id,
            'original_name' => 'evidence.pdf',
        ]);
    }

    /**
     * Test: Técnico elimina evidencia
     */
    public function test_technician_can_delete_evidence(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $workReport = $this->workReportService->create($client, $this->technician);
        $file = UploadedFile::fake()->create('to-delete.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $this->technician->id);

        $response = $this->actingAs($this->technician)->delete(route('technician.evidences.delete', $evidence));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('evidences', ['id' => $evidence->id]);
    }

    /**
     * Test: Técnico no puede subir evidencia a parte de otro técnico
     */
    public function test_technician_cannot_upload_evidence_to_other_technician_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $otherTechnician = User::factory()->create(['role' => 'technician']);
        $otherReport = $this->workReportService->create($client, $otherTechnician);
        $file = UploadedFile::fake()->create('evidence.pdf', 100);

        $response = $this->actingAs($this->technician)->post(
            route('technician.work-reports.evidences.upload', $otherReport),
            ['file' => $file]
        );

        $response->assertStatus(403);
    }

    /**
     * Test: Técnico no puede eliminar evidencia de parte de otro técnico
     */
    public function test_technician_cannot_delete_evidence_from_other_technician_work_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $otherTechnician = User::factory()->create(['role' => 'technician']);
        $otherReport = $this->workReportService->create($client, $otherTechnician);
        $file = UploadedFile::fake()->create('evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($otherReport, $file, $otherTechnician->id);

        $response = $this->actingAs($this->technician)->delete(route('technician.evidences.delete', $evidence));

        $response->assertStatus(403);
    }
}
