<?php

namespace Tests\Feature\Policies;

use App\Models\Client;
use App\Models\Evidence;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\BalanceService;
use App\Services\AuditService;
use App\Services\EvidenceService;
use App\Services\WorkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidencePolicyTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $workReportService;
    private EvidenceService $evidenceService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
        $this->evidenceService = new EvidenceService(new AuditService());
    }

    /**
     * Test: Admin puede ver/upload/delete cualquier evidencia
     */
    public function test_admin_can_manage_any_evidence(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport, null, $technician->id);

        $file = UploadedFile::fake()->create('test_evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician->id);

        $this->assertTrue($admin->can('view', $evidence));
        // upload se verifica con el WorkReport directamente
        $policy = new \App\Policies\EvidencePolicy();
        $this->assertTrue($policy->upload($admin, $workReport));
        $this->assertTrue($admin->can('delete', $evidence));
        $this->assertTrue($admin->can('download', $evidence));
    }

    /**
     * Test: Technician puede ver/upload/delete solo evidencias de sus partes
     */
    public function test_technician_can_manage_only_own_evidence(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician1 = User::factory()->create(['role' => 'technician']);
        $technician2 = User::factory()->create(['role' => 'technician']);

        // Crear partes
        $workReport1 = $this->workReportService->create($client, $technician1);
        $this->workReportService->start($workReport1, $technician1->id);
        sleep(1);
        $this->workReportService->finish($workReport1, null, $technician1->id);

        $workReport2 = $this->workReportService->create($client, $technician2);
        $this->workReportService->start($workReport2, $technician2->id);
        sleep(1);
        $this->workReportService->finish($workReport2, null, $technician2->id);

        // Crear evidencias
        $file1 = UploadedFile::fake()->create('evidence1.pdf', 100);
        $evidence1 = $this->evidenceService->upload($workReport1, $file1, $technician1->id);

        $file2 = UploadedFile::fake()->create('evidence2.pdf', 100);
        $evidence2 = $this->evidenceService->upload($workReport2, $file2, $technician2->id);

        // Technician 1 puede gestionar su evidencia
        $this->assertTrue($technician1->can('view', $evidence1));
        $policy = new \App\Policies\EvidencePolicy();
        $this->assertTrue($policy->upload($technician1, $workReport1));
        $this->assertTrue($technician1->can('delete', $evidence1));
        $this->assertTrue($technician1->can('download', $evidence1));

        // Technician 1 NO puede gestionar evidencia del technician 2
        $this->assertFalse($technician1->can('view', $evidence2));
        $this->assertFalse($policy->upload($technician1, $workReport2));
        $this->assertFalse($technician1->can('delete', $evidence2));
        $this->assertFalse($technician1->can('download', $evidence2));
    }

    /**
     * Test: Client puede ver/download solo evidencias de sus partes y solo si finished/validated
     */
    public function test_client_can_view_evidence_only_if_finished_or_validated(): void
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
        $technician = User::factory()->create(['role' => 'technician']);

        // Crear parte finished
        $finishedReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($finishedReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($finishedReport, null, $technician->id);

        $fileFinished = UploadedFile::fake()->create('finished_evidence.pdf', 100);
        $evidenceFinished = $this->evidenceService->upload($finishedReport, $fileFinished, $technician->id);

        // Crear parte validated
        $balanceService = new BalanceService(new AuditService());
        $balanceService->credit($client, 3600, 'test_credit', 'User', $technician->id, $technician->id);

        $validatedReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($validatedReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($validatedReport, null, $technician->id);
        $this->workReportService->validate($validatedReport, $technician->id);

        $fileValidated = UploadedFile::fake()->create('validated_evidence.pdf', 100);
        $evidenceValidated = $this->evidenceService->upload($validatedReport, $fileValidated, $technician->id);

        // Crear parte in_progress (usar otro técnico para no tener conflicto)
        $technician2 = User::factory()->create(['role' => 'technician']);
        $inProgressReport = $this->workReportService->create($client, $technician2);
        $this->workReportService->start($inProgressReport, $technician2->id);

        $fileInProgress = UploadedFile::fake()->create('inprogress_evidence.pdf', 100);
        $evidenceInProgress = $this->evidenceService->upload($inProgressReport, $fileInProgress, $technician2->id);

        // Cliente puede ver/descargar evidencias de partes finished/validated
        $this->assertTrue($clientUser->can('view', $evidenceFinished));
        $this->assertTrue($clientUser->can('download', $evidenceFinished));
        $this->assertTrue($clientUser->can('view', $evidenceValidated));
        $this->assertTrue($clientUser->can('download', $evidenceValidated));

        // Cliente NO puede ver/descargar evidencias de partes in_progress
        $this->assertFalse($clientUser->can('view', $evidenceInProgress));
        $this->assertFalse($clientUser->can('download', $evidenceInProgress));

        // Cliente NO puede subir/eliminar evidencias
        $policy = new \App\Policies\EvidencePolicy();
        $this->assertFalse($policy->upload($clientUser, $finishedReport));
        $this->assertFalse($clientUser->can('delete', $evidenceFinished));
    }

    /**
     * Test: Client no puede ver evidencias de otros clientes
     */
    public function test_client_cannot_view_evidence_from_other_clients(): void
    {
        $clientUser1 = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client1@test.com',
        ]);
        $client1 = Client::create([
            'name' => 'Cliente 1',
            'email' => 'client1@test.com',
            'user_id' => $clientUser1->id,
        ]);

        $clientUser2 = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'email' => 'client2@test.com',
        ]);
        $client2 = Client::create([
            'name' => 'Cliente 2',
            'email' => 'client2@test.com',
            'user_id' => $clientUser2->id,
        ]);

        $technician = User::factory()->create(['role' => 'technician']);

        // Crear parte del cliente 2
        $workReport2 = $this->workReportService->create($client2, $technician);
        $this->workReportService->start($workReport2, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport2, null, $technician->id);

        $file2 = UploadedFile::fake()->create('client2_evidence.pdf', 100);
        $evidence2 = $this->evidenceService->upload($workReport2, $file2, $technician->id);

        // Cliente 1 NO puede ver evidencia del cliente 2
        $this->assertFalse($clientUser1->can('view', $evidence2));
        $this->assertFalse($clientUser1->can('download', $evidence2));
    }
}
