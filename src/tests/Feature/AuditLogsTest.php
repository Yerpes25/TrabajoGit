<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\Evidence;
use App\Models\AuditLog;
use App\Services\BalanceService;
use App\Services\WorkReportService;
use App\Services\EvidenceService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuditLogsTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $auditService;
    private BalanceService $balanceService;
    private WorkReportService $workReportService;
    private EvidenceService $evidenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = new AuditService();
        $this->balanceService = new BalanceService($this->auditService);
        $this->workReportService = new WorkReportService($this->balanceService, $this->auditService);
        $this->evidenceService = new EvidenceService($this->auditService);
        
        Storage::fake('public');
    }

    /**
     * Test: AuditService::log() crea registros con payload JSON
     */
    public function test_audit_service_log_creates_record_with_payload(): void
    {
        $user = User::factory()->create();
        $payload = [
            'test_key' => 'test_value',
            'amount' => 1000,
        ];

        $auditLog = $this->auditService->log(
            'test_event',
            $user->id,
            'TestEntity',
            123,
            $payload,
            '127.0.0.1',
            'Test User Agent'
        );

        $this->assertInstanceOf(AuditLog::class, $auditLog);
        $this->assertEquals('test_event', $auditLog->event);
        $this->assertEquals($user->id, $auditLog->actor_id);
        $this->assertEquals('TestEntity', $auditLog->entity_type);
        $this->assertEquals(123, $auditLog->entity_id);
        $this->assertEquals('127.0.0.1', $auditLog->ip);
        $this->assertEquals('Test User Agent', $auditLog->user_agent);
        $this->assertEquals($payload, $auditLog->payload);
        $this->assertNotNull($auditLog->created_at);
    }

    /**
     * Test: Al hacer credit se crea audit log
     */
    public function test_credit_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $user = User::factory()->create();

        $this->balanceService->credit($client, 1000, 'bono_test', null, null, $user->id);

        $auditLog = AuditLog::where('event', 'saldo_change')
            ->where('entity_type', 'BalanceMovement')
            ->where('actor_id', $user->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('saldo_change', $auditLog->event);
        $this->assertEquals('BalanceMovement', $auditLog->entity_type);
        $this->assertArrayHasKey('type', $auditLog->payload ?? []);
        $this->assertEquals('credit', $auditLog->payload['type'] ?? null);
        $this->assertEquals($client->id, $auditLog->payload['client_id'] ?? null);
    }

    /**
     * Test: Al hacer debit se crea audit log
     */
    public function test_debit_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $user = User::factory()->create();

        // Añadir crédito primero
        $this->balanceService->credit($client, 2000, 'bono_test', null, null, $user->id);

        // Realizar débito
        $this->balanceService->debit($client, 500, 'validacion_test', null, null, $user->id);

        // Buscar específicamente el log de débito
        $debitLog = AuditLog::where('event', 'saldo_change')
            ->where('entity_type', 'BalanceMovement')
            ->where('actor_id', $user->id)
            ->whereJsonContains('payload->type', 'debit')
            ->first();

        $this->assertNotNull($debitLog);
        $this->assertEquals('debit', $debitLog->payload['type'] ?? null);
        $this->assertEquals(500, $debitLog->payload['amount_seconds'] ?? null);

        // Verificar que hay al menos 2 logs (credit + debit)
        $totalLogs = AuditLog::where('event', 'saldo_change')
            ->where('entity_type', 'BalanceMovement')
            ->where('actor_id', $user->id)
            ->count();
        $this->assertGreaterThanOrEqual(2, $totalLogs);
    }

    /**
     * Test: Al validar parte se crea audit log
     */
    public function test_validate_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito suficiente
        $this->balanceService->credit($client, 10000, 'bono');

        // Crear y finalizar parte
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport);
        sleep(1);
        $this->workReportService->finish($workReport);

        // Validar
        $this->workReportService->validate($workReport, $validator);

        $auditLog = AuditLog::where('event', 'work_report_validated')
            ->where('entity_type', 'WorkReport')
            ->where('entity_id', $workReport->id)
            ->where('actor_id', $validator->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('work_report_validated', $auditLog->event);
        $this->assertEquals('WorkReport', $auditLog->entity_type);
        $this->assertEquals($workReport->id, $auditLog->entity_id);
        $this->assertArrayHasKey('client_id', $auditLog->payload ?? []);
        $this->assertArrayHasKey('total_seconds', $auditLog->payload ?? []);
    }

    /**
     * Test: Al subir evidencia se crea audit log
     */
    public function test_upload_evidence_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        $file = UploadedFile::fake()->create('test-evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician);

        $auditLog = AuditLog::where('event', 'evidence_uploaded')
            ->where('entity_type', 'Evidence')
            ->where('entity_id', $evidence->id)
            ->where('actor_id', $technician->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('evidence_uploaded', $auditLog->event);
        $this->assertEquals('Evidence', $auditLog->entity_type);
        $this->assertEquals($evidence->id, $auditLog->entity_id);
        $this->assertArrayHasKey('work_report_id', $auditLog->payload ?? []);
        $this->assertArrayHasKey('original_name', $auditLog->payload ?? []);
    }

    /**
     * Test: Al borrar evidencia se crea audit log
     */
    public function test_delete_evidence_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->workReportService->create($client, $technician);

        $file = UploadedFile::fake()->create('test-evidence.pdf', 100);
        $evidence = $this->evidenceService->upload($workReport, $file, $technician);

        // Eliminar evidencia
        $this->evidenceService->delete($evidence);

        $auditLog = AuditLog::where('event', 'evidence_deleted')
            ->where('entity_type', 'Evidence')
            ->where('entity_id', $evidence->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('evidence_deleted', $auditLog->event);
        $this->assertEquals('Evidence', $auditLog->entity_type);
        $this->assertEquals($evidence->id, $auditLog->entity_id);
        $this->assertArrayHasKey('work_report_id', $auditLog->payload ?? []);
        $this->assertArrayHasKey('original_name', $auditLog->payload ?? []);
    }

    /**
     * Test: La auditoría no interrumpe el flujo si falla
     */
    public function test_audit_failure_does_not_interrupt_flow(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $user = User::factory()->create();

        // Crear un AuditService que simule fallo
        $failingAuditService = new class extends AuditService {
            public function log(...$args): ?\App\Models\AuditLog
            {
                throw new \Exception('Simulated audit failure');
            }
        };

        $balanceService = new BalanceService($failingAuditService);

        // El crédito debe completarse aunque falle la auditoría
        $movement = $balanceService->credit($client, 1000, 'bono_test', null, null, $user->id);

        $this->assertNotNull($movement);
        $this->assertEquals(1000, $this->balanceService->getBalanceSeconds($client));
    }
}
