<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\BalanceMovement;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class WorkReportValidationBalanceTest extends TestCase
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
     * Test: validar descuenta saldo correctamente (debit con amount_seconds negativo)
     */
    public function test_validate_debits_balance_correctly(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito al cliente
        $this->balanceService->credit($client, 10000, 'bono_inicial');
        $this->assertEquals(10000, $this->balanceService->getBalanceSeconds($client));

        // Crear y finalizar parte con 3600 segundos (1 hora)
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport);
        sleep(1); // Trabajar 1 segundo
        $this->workReportService->finish($workReport);

        $workReport->refresh();
        $this->assertGreaterThanOrEqual(1, $workReport->total_seconds);

        // Validar el parte
        $this->workReportService->validate($workReport, $validator);

        // Verificar que el saldo se descontó
        $workReport->refresh();
        $client->refresh();
        $expectedBalance = 10000 - $workReport->total_seconds;
        $this->assertEquals($expectedBalance, $this->balanceService->getBalanceSeconds($client));

        // Verificar que existe el movimiento en balance_movements
        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(-$workReport->total_seconds, $movement->amount_seconds);
        $this->assertEquals('debit', $movement->type);
        $this->assertEquals($validator->id, $movement->created_by);
        $this->assertEquals($workReport->id, $movement->metadata['work_report_id'] ?? null);
    }

    /**
     * Test: si saldo insuficiente, no valida (no cambia status) y no crea movimiento
     */
    public function test_validate_fails_with_insufficient_balance(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito insuficiente (solo 5 segundos)
        $this->balanceService->credit($client, 5, 'bono_inicial');
        $this->assertEquals(5, $this->balanceService->getBalanceSeconds($client));

        // Crear y finalizar parte
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport);
        $this->workReportService->finish($workReport);

        // Establecer tiempo trabajado mayor que el saldo disponible (para el test)
        $workReport->update(['total_seconds' => 10]);
        $workReport->refresh();
        $this->assertGreaterThan(5, $workReport->total_seconds);

        // Intentar validar (debe fallar por saldo insuficiente)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Saldo insuficiente');

        try {
            $this->workReportService->validate($workReport, $validator);
        } finally {
            // Verificar que el estado NO cambió a validated
            $workReport->refresh();
            $this->assertEquals(WorkReport::STATUS_FINISHED, $workReport->status);
            $this->assertNull($workReport->validated_at);

            // Verificar que NO se creó ningún movimiento
            $movement = BalanceMovement::where('client_id', $client->id)
                ->where('reference_type', 'WorkReport')
                ->where('reference_id', $workReport->id)
                ->first();
            $this->assertNull($movement);

            // Verificar que el saldo NO cambió
            $this->assertEquals(5, $this->balanceService->getBalanceSeconds($client));
        }
    }

    /**
     * Test: si se llama validate() dos veces, no duplica movimientos (idempotencia)
     */
    public function test_validate_is_idempotent_no_duplicate_movements(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito suficiente
        $this->balanceService->credit($client, 10000, 'bono_inicial');

        // Crear y finalizar parte
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport);
        sleep(1);
        $this->workReportService->finish($workReport);

        $workReport->refresh();
        $initialBalance = $this->balanceService->getBalanceSeconds($client);

        // Validar primera vez
        $this->workReportService->validate($workReport, $validator);
        $workReport->refresh();

        $firstBalance = $this->balanceService->getBalanceSeconds($client);
        $firstMovementCount = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->count();

        $this->assertEquals(1, $firstMovementCount);
        $this->assertEquals($initialBalance - $workReport->total_seconds, $firstBalance);

        // Validar segunda vez (debe ser idempotente)
        $this->workReportService->validate($workReport, $validator);
        $workReport->refresh();

        $secondBalance = $this->balanceService->getBalanceSeconds($client);
        $secondMovementCount = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->where('reason', 'validation_work_report')
            ->count();

        // No debe haber duplicado de movimientos
        $this->assertEquals(1, $secondMovementCount);
        // El saldo no debe cambiar (no se descuenta dos veces)
        $this->assertEquals($firstBalance, $secondBalance);
        // El parte sigue validado
        $this->assertEquals(WorkReport::STATUS_VALIDATED, $workReport->status);
    }

    /**
     * Test: existe balance_movements con reference_type='WorkReport' y reference_id correcto
     */
    public function test_balance_movement_has_correct_reference(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito
        $this->balanceService->credit($client, 5000, 'bono');

        // Crear y finalizar parte
        $workReport = $this->workReportService->create($client, $technician, 'Título del parte');
        $this->workReportService->start($workReport);
        sleep(1);
        $this->workReportService->finish($workReport);

        // Validar
        $this->workReportService->validate($workReport, $validator);

        // Verificar el movimiento
        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('WorkReport', $movement->reference_type);
        $this->assertEquals($workReport->id, $movement->reference_id);
        $this->assertEquals('validation_work_report', $movement->reason);
        $this->assertEquals($client->id, $movement->client_id);
        $this->assertEquals($validator->id, $movement->created_by);
        $this->assertArrayHasKey('work_report_id', $movement->metadata ?? []);
        $this->assertEquals($workReport->id, $movement->metadata['work_report_id'] ?? null);
    }

    /**
     * Test: validar parte sin tiempo trabajado falla
     */
    public function test_validate_fails_with_zero_total_seconds(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();

        // Añadir crédito
        $this->balanceService->credit($client, 1000, 'bono');

        // Crear parte sin iniciar (total_seconds = 0)
        $workReport = $this->workReportService->create($client, $technician);
        $this->workReportService->finish($workReport); // Finalizar sin trabajar

        $workReport->refresh();
        $this->assertEquals(0, $workReport->total_seconds);

        // Intentar validar (debe fallar)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede validar un parte sin tiempo trabajado');

        $this->workReportService->validate($workReport, $validator);

        // Verificar que NO se creó movimiento
        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'WorkReport')
            ->where('reference_id', $workReport->id)
            ->first();
        $this->assertNull($movement);
    }
}
