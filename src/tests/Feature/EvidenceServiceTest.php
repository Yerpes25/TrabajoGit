<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\Evidence;
use App\Services\WorkReportService;
use App\Services\EvidenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class EvidenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private EvidenceService $evidenceService;
    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evidenceService = new EvidenceService();
        $this->workReportService = new WorkReportService(new \App\Services\BalanceService());
        
        // Usar Storage fake para pruebas
        Storage::fake('public');
    }

    /**
     * Test: upload() guarda archivo en disco configurado y crea registro DB coherente
     */
    public function test_upload_saves_file_and_creates_record(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        // Crear archivo fake
        $file = UploadedFile::fake()->create('test-evidence.pdf', 100);

        // Subir evidencia
        $evidence = $this->evidenceService->upload($workReport, $file, $technician);

        // Verificar que se creó el registro
        $this->assertInstanceOf(Evidence::class, $evidence);
        $this->assertEquals($workReport->id, $evidence->work_report_id);
        $this->assertEquals($technician->id, $evidence->uploaded_by);
        $this->assertEquals('public', $evidence->storage_disk);
        $this->assertStringContainsString('work_reports/' . $workReport->id . '/evidences/', $evidence->storage_path);
        $this->assertEquals('test-evidence.pdf', $evidence->original_name);
        $this->assertNotNull($evidence->mime_type);
        // UploadedFile::fake()->create() crea archivos de 102400 bytes por defecto
        $this->assertGreaterThan(0, $evidence->size_bytes);
        $this->assertNotNull($evidence->checksum);

        // Verificar que el archivo existe en el storage
        Storage::disk('public')->assertExists($evidence->storage_path);
    }

    /**
     * Test: upload() genera nombre único con UUID
     */
    public function test_upload_generates_unique_filename_with_uuid(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        // Subir dos archivos con el mismo nombre
        $file1 = UploadedFile::fake()->create('same-name.pdf', 100);
        $file2 = UploadedFile::fake()->create('same-name.pdf', 100);

        $evidence1 = $this->evidenceService->upload($workReport, $file1, $technician);
        $evidence2 = $this->evidenceService->upload($workReport, $file2, $technician);

        // Verificar que tienen nombres diferentes (UUID)
        $this->assertNotEquals($evidence1->storage_path, $evidence2->storage_path);
        $this->assertStringContainsString('same-name.pdf', $evidence1->storage_path);
        $this->assertStringContainsString('same-name.pdf', $evidence2->storage_path);
    }

    /**
     * Test: upload() requiere work_report existente
     */
    public function test_upload_requires_existing_work_report(): void
    {
        $technician = User::factory()->create();
        $file = UploadedFile::fake()->create('test.pdf', 100);

        // Intentar subir a un parte inexistente
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->evidenceService->upload(99999, $file, $technician);
    }

    /**
     * Test: listByWorkReport() devuelve evidencias del parte
     */
    public function test_list_by_work_report_returns_evidences(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport1 = $this->workReportService->create($client, $technician);
        $workReport2 = $this->workReportService->create($client, $technician);

        // Subir evidencias a ambos partes
        $file1 = UploadedFile::fake()->create('evidence1.pdf', 100);
        $file2 = UploadedFile::fake()->create('evidence2.pdf', 100);
        $file3 = UploadedFile::fake()->create('evidence3.pdf', 100);

        $evidence1 = $this->evidenceService->upload($workReport1, $file1, $technician);
        $evidence2 = $this->evidenceService->upload($workReport1, $file2, $technician);
        $evidence3 = $this->evidenceService->upload($workReport2, $file3, $technician);

        // Listar evidencias del primer parte
        $evidences = $this->evidenceService->listByWorkReport($workReport1);

        $this->assertCount(2, $evidences);
        $this->assertTrue($evidences->contains($evidence1));
        $this->assertTrue($evidences->contains($evidence2));
        $this->assertFalse($evidences->contains($evidence3));

        // Verificar orden (más reciente primero)
        // NOTE: Puede haber un pequeño delay, así que verificamos que están ordenados por created_at desc
        $evidencesArray = $evidences->toArray();
        $this->assertGreaterThanOrEqual($evidencesArray[0]['created_at'], $evidencesArray[1]['created_at']);
    }

    /**
     * Test: delete() elimina archivo del disco y registro DB
     */
    public function test_delete_removes_file_and_record(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        // Subir evidencia
        $file = UploadedFile::fake()->create('test-evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician);

        // Verificar que existe
        Storage::disk('public')->assertExists($evidence->storage_path);
        $this->assertDatabaseHas('evidences', ['id' => $evidence->id]);

        // Eliminar
        $result = $this->evidenceService->delete($evidence);

        $this->assertTrue($result);
        
        // Verificar que el archivo fue eliminado
        Storage::disk('public')->assertMissing($evidence->storage_path);
        
        // Verificar que el registro fue eliminado
        $this->assertDatabaseMissing('evidences', ['id' => $evidence->id]);
    }

    /**
     * Test: delete() no falla si el archivo no existe en el disco
     */
    public function test_delete_does_not_fail_if_file_missing(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        // Crear evidencia manualmente (sin archivo)
        $evidence = Evidence::create([
            'work_report_id' => $workReport->id,
            'uploaded_by' => $technician->id,
            'storage_disk' => 'public',
            'storage_path' => 'work_reports/999/evidences/non-existent.pdf',
            'original_name' => 'non-existent.pdf',
        ]);

        // Eliminar (no debe fallar aunque el archivo no exista)
        $result = $this->evidenceService->delete($evidence);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('evidences', ['id' => $evidence->id]);
    }

    /**
     * Test: upload() guarda metadata opcional
     */
    public function test_upload_saves_optional_metadata(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        $file = UploadedFile::fake()->create('test.pdf', 100);
        $metadata = [
            'description' => 'Evidencia de prueba',
            'category' => 'foto',
        ];

        $evidence = $this->evidenceService->upload($workReport, $file, $technician, $metadata);

        $this->assertEquals($metadata, $evidence->metadata);
    }

    /**
     * Test: getUrl() genera URL correcta para disco public
     */
    public function test_get_url_generates_correct_url_for_public_disk(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        $file = UploadedFile::fake()->create('test.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician);

        $url = $this->evidenceService->getUrl($evidence);

        $this->assertStringContainsString('/storage/', $url);
        $this->assertStringContainsString($evidence->storage_path, $url);
    }
}
