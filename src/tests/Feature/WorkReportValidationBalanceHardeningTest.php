<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\WorkReportEvent;
use App\Models\BalanceMovement;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkReportValidationBalanceHardeningTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $workReportService;
    private BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new BalanceService();
        $this->workReportService = new WorkReportService($this->balanceService);
    }

    /**
     * Test: caso reparación - parte validated sin movimiento => crea movimiento y no falla por estado
     */
    public function test_repair_creates_movement_for_validated_without_movement(): void
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

        // Simular caso de reparación: parte validated pero sin movimiento
        // (esto podría ocurrir si hubo un error parcial en una validación anterior)
        $workReport->update([
            'status' => WorkReport::STATUS_VALIDATED,
            'validated_at' => now(),
            'validated_by' => $validator->id,
        ]);

        // Verificar que NO existe movimiento
        $movementBefore = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->first();
        $this->assertNull($movementBefore);

        // Validar (debe crear el movimiento sin fallar por estado)
        $this->workReportService->validate($workReport, $validator);

        // Verificar que ahora SÍ existe el movimiento
        $movementAfter = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->first();
        $this->assertNotNull($movementAfter);
        $this->assertEquals(-$workReport->total_seconds, $movementAfter->amount_seconds);

        // Verificar que el saldo se descontó
        $expectedBalance = 10000 - $workReport->total_seconds;
        $this->assertEquals($expectedBalance, $this->balanceService->getBalanceSeconds($client));
    }

    /**
     * Test: caso reparación - no duplica eventos validate si ya existe
     */
    public function test_repair_does_not_duplicate_validate_event(): void
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

        // Simular caso de reparación: parte validated con evento pero sin movimiento
        $workReport->update([
            'status' => WorkReport::STATUS_VALIDATED,
            'validated_at' => now(),
            'validated_by' => $validator->id,
        ]);

        // Crear evento validate manualmente (simula que existía antes)
        WorkReportEvent::create([
            'work_report_id' => $workReport->id,
            'type' => WorkReportEvent::TYPE_VALIDATE,
            'occurred_at' => now(),
            'elapsed_seconds_after' => $workReport->total_seconds,
            'created_by' => $validator->id,
        ]);

        $eventCountBefore = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_VALIDATE)
            ->count();
        $this->assertEquals(1, $eventCountBefore);

        // Validar (reparación)
        $this->workReportService->validate($workReport, $validator);

        // Verificar que NO se duplicó el evento
        $eventCountAfter = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_VALIDATE)
            ->count();
        $this->assertEquals(1, $eventCountAfter);
    }

    /**
     * Test: caso duplicate key - si se fuerza condición, validate() no rompe (retorna work_report validado)
     * 
     * Este test simula una condición de concurrencia donde dos validaciones simultáneas
     * intentan crear el mismo movimiento, causando violación del índice único.
     */
    public function test_duplicate_key_handled_as_idempotent(): void
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

        // Simular concurrencia: crear el movimiento manualmente antes de validar
        // Esto simula que otra transacción concurrente ya creó el movimiento
        BalanceMovement::create([
            'client_id' => $client->id,
            'amount_seconds' => -$workReport->total_seconds,
            'type' => 'debit',
            'reason' => 'validation_work_report',
            'reference_type' => 'WorkReport',
            'reference_id' => $workReport->id,
            'created_by' => $validator->id,
            'metadata' => [
                'work_report_id' => $workReport->id,
                'total_seconds' => $workReport->total_seconds,
            ],
        ]);

        // Actualizar el perfil del cliente para reflejar el movimiento
        $profile = $client->profile ?? \App\Models\ClientProfile::create([
            'client_id' => $client->id,
            'balance_seconds' => 0,
        ]);
        $profile->decrement('balance_seconds', $workReport->total_seconds);

        // Intentar validar (debe detectar el movimiento existente y ser idempotente)
        // No debe fallar, debe retornar el parte validado
        $result = $this->workReportService->validate($workReport, $validator);

        $this->assertNotNull($result);
        $this->assertEquals(WorkReport::STATUS_VALIDATED, $result->status);

        // Verificar que solo hay un movimiento (no se duplicó)
        $movementCount = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->count();
        $this->assertEquals(1, $movementCount);
    }

    /**
     * Test: caso normal sigue funcionando (tests existentes pasan)
     * Este test verifica que el flujo normal de validación sigue funcionando correctamente
     */
    public function test_normal_validation_flow_still_works(): void
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

        // Verificar que está finished
        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_FINISHED, $workReport->status);

        // Validar normalmente
        $this->workReportService->validate($workReport, $validator);

        // Verificar que se validó correctamente
        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_VALIDATED, $workReport->status);
        $this->assertNotNull($workReport->validated_at);
        $this->assertEquals($validator->id, $workReport->validated_by);

        // Verificar que existe el movimiento
        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->first();
        $this->assertNotNull($movement);

        // Verificar que existe el evento
        $event = WorkReportEvent::where('work_report_id', $workReport->id)
            ->where('type', WorkReportEvent::TYPE_VALIDATE)
            ->first();
        $this->assertNotNull($event);
    }

    /**
     * Test: reparación mantiene validated_at y validated_by existentes si ya existen
     */
    public function test_repair_preserves_existing_validated_at_and_by(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $originalValidator = User::factory()->create();
        $newValidator = User::factory()->create();

        // Añadir crédito suficiente
        $this->balanceService->credit($client, 10000, 'bono');

        // Crear y finalizar parte
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport);
        sleep(1);
        $this->workReportService->finish($workReport);

        // Simular caso de reparación: parte validated con validated_at y validated_by
        $originalValidatedAt = now()->subHour();
        $workReport->update([
            'status' => WorkReport::STATUS_VALIDATED,
            'validated_at' => $originalValidatedAt,
            'validated_by' => $originalValidator->id,
        ]);

        // Validar con otro validador (reparación)
        $this->workReportService->validate($workReport, $newValidator);

        // Verificar que se mantienen los valores originales
        $workReport->refresh();
        $this->assertEquals($originalValidatedAt->format('Y-m-d H:i:s'), $workReport->validated_at->format('Y-m-d H:i:s'));
        $this->assertEquals($originalValidator->id, $workReport->validated_by);
    }
}
