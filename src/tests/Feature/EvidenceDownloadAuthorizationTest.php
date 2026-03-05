<?php

namespace Tests\Feature;

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

class EvidenceDownloadAuthorizationTest extends TestCase
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
     * Test: Admin puede descargar cualquier evidencia
     */
    public function test_admin_can_download_any_evidence(): void
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

        $response = $this->actingAs($admin)->get(route('evidences.download', $evidence));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('test_evidence.pdf', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test: Technician puede descargar su evidencia y no la de otro
     */
    public function test_technician_can_download_own_evidence_not_others(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician1 = User::factory()->create(['role' => 'technician']);
        $technician2 = User::factory()->create(['role' => 'technician']);

        // Crear parte del técnico 1
        $workReport1 = $this->workReportService->create($client, $technician1);
        $this->workReportService->start($workReport1, $technician1->id);
        sleep(1);
        $this->workReportService->finish($workReport1, null, $technician1->id);

        $file1 = UploadedFile::fake()->create('evidence1.pdf', 100);
        $evidence1 = $this->evidenceService->upload($workReport1, $file1, $technician1->id);

        // Crear parte del técnico 2
        $workReport2 = $this->workReportService->create($client, $technician2);
        $this->workReportService->start($workReport2, $technician2->id);
        sleep(1);
        $this->workReportService->finish($workReport2, null, $technician2->id);

        $file2 = UploadedFile::fake()->create('evidence2.pdf', 100);
        $evidence2 = $this->evidenceService->upload($workReport2, $file2, $technician2->id);

        // Técnico 1 puede descargar su evidencia
        $response = $this->actingAs($technician1)->get(route('evidences.download', $evidence1));
        $response->assertStatus(200);

        // Técnico 1 NO puede descargar evidencia del técnico 2
        $response = $this->actingAs($technician1)->get(route('evidences.download', $evidence2));
        $response->assertStatus(403);
    }

    /**
     * Test: Client puede descargar su evidencia solo si estado finished/validated
     */
    public function test_client_can_download_evidence_only_if_finished_or_validated(): void
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

        // Crear parte in_progress (no debe poder descargar)
        // Primero necesitamos pausar el validated para liberar al técnico
        // Pero validated no se puede pausar, así que creamos otro técnico o usamos uno diferente
        $technician2 = User::factory()->create(['role' => 'technician']);
        $inProgressReport = $this->workReportService->create($client, $technician2);
        $this->workReportService->start($inProgressReport, $technician2->id);

        $fileInProgress = UploadedFile::fake()->create('inprogress_evidence.pdf', 100);
        $evidenceInProgress = $this->evidenceService->upload($inProgressReport, $fileInProgress, $technician2->id);

        // Cliente puede descargar evidencia de parte finished
        $response = $this->actingAs($clientUser)->get(route('evidences.download', $evidenceFinished));
        $response->assertStatus(200);

        // Cliente puede descargar evidencia de parte validated
        $response = $this->actingAs($clientUser)->get(route('evidences.download', $evidenceValidated));
        $response->assertStatus(200);

        // Cliente NO puede descargar evidencia de parte in_progress
        $response = $this->actingAs($clientUser)->get(route('evidences.download', $evidenceInProgress));
        $response->assertStatus(403);
    }

    /**
     * Test: Client no puede descargar de otros clientes
     */
    public function test_client_cannot_download_from_other_clients(): void
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

        // Cliente 1 NO puede descargar evidencia del cliente 2
        $response = $this->actingAs($clientUser1)->get(route('evidences.download', $evidence2));
        $response->assertStatus(403);
    }

    /**
     * Test: Usuario no autenticado es redirigido a login
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport, null, $technician->id);

        $file = UploadedFile::fake()->create('test_evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician->id);

        $response = $this->get(route('evidences.download', $evidence));
        $response->assertRedirect('/login');
    }
}
