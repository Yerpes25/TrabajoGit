<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\WorkReportEvent;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class WorkReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkReportService(new BalanceService());
    }

    /**
     * Test: crear un work_report para un client y technician
     */
    public function test_can_create_work_report(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        $technician = User::factory()->create();

        $workReport = $this->service->create($client, $technician, 'Título', 'Descripción');

        $this->assertInstanceOf(WorkReport::class, $workReport);
        $this->assertEquals($client->id, $workReport->client_id);
        $this->assertEquals($technician->id, $workReport->technician_id);
        $this->assertEquals('Título', $workReport->title);
        $this->assertEquals('Descripción', $workReport->description);
        $this->assertEquals(WorkReport::STATUS_PAUSED, $workReport->status);
        $this->assertEquals(0, $workReport->total_seconds);
    }

    /**
     * Test: start() setea status in_progress, active_started_at y crea evento start
     */
    public function test_start_sets_status_and_creates_event(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertNotNull($workReport->active_started_at);

        $event = $workReport->events()->where('type', WorkReportEvent::TYPE_START)->first();
        $this->assertNotNull($event);
        $this->assertEquals(0, $event->elapsed_seconds_after);
    }

    /**
     * Test: pause() solo si estaba in_progress, suma delta a total_seconds, limpia active_started_at
     */
    public function test_pause_calculates_delta_and_updates_total(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        // Iniciar y esperar un momento
        $this->service->start($workReport);
        sleep(2); // Esperar 2 segundos

        $this->service->pause($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_PAUSED, $workReport->status);
        $this->assertNull($workReport->active_started_at);
        $this->assertGreaterThanOrEqual(2, $workReport->total_seconds);

        $event = $workReport->events()->where('type', WorkReportEvent::TYPE_PAUSE)->first();
        $this->assertNotNull($event);
        $this->assertEquals($workReport->total_seconds, $event->elapsed_seconds_after);
    }

    /**
     * Test: pause() falla si no está in_progress
     */
    public function test_pause_fails_if_not_in_progress(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        // Finalizar el parte primero (estado finished)
        $this->service->finish($workReport);

        // Intentar pausar desde finished (debe fallar)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede pausar un parte que está en estado');

        $this->service->pause($workReport);
    }

    /**
     * Test: resume() solo si estaba paused, verifica regla "solo 1 activo", setea status y active_started_at
     */
    public function test_resume_sets_status_and_creates_event(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        $this->service->pause($workReport);
        $this->service->resume($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertNotNull($workReport->active_started_at);

        $event = $workReport->events()->where('type', WorkReportEvent::TYPE_RESUME)->first();
        $this->assertNotNull($event);
    }

    /**
     * Test: resume() falla si el técnico ya tiene otro parte activo (regla core)
     */
    public function test_resume_fails_if_technician_has_another_active_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();

        // Crear y activar primer parte
        $workReport1 = $this->service->create($client, $technician);
        $this->service->start($workReport1);

        // Crear segundo parte (pausado, sin iniciar)
        $workReport2 = $this->service->create($client, $technician);

        // Intentar iniciar el segundo parte (debe fallar porque el primero está activo)
        // Pero primero necesitamos iniciarlo para luego pausarlo y reanudarlo
        // Como start() falla, vamos a pausar el primero y luego intentar reanudar el segundo
        $this->service->pause($workReport1);
        
        // Ahora iniciar y pausar el segundo
        $this->service->start($workReport2);
        $this->service->pause($workReport2);
        
        // Reanudar el primero
        $this->service->resume($workReport1);

        // Intentar reanudar el segundo parte (debe fallar porque el primero está activo)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Solo puede haber 1 parte en \'in_progress\' por técnico');

        $this->service->resume($workReport2);
    }

    /**
     * Test: start() falla si el técnico ya tiene otro parte activo (regla core)
     */
    public function test_start_fails_if_technician_has_another_active_report(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();

        // Crear y activar primer parte
        $workReport1 = $this->service->create($client, $technician);
        $this->service->start($workReport1);

        // Crear segundo parte (pausado)
        $workReport2 = $this->service->create($client, $technician);

        // Intentar iniciar el segundo parte (debe fallar porque el primero está activo)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Solo puede haber 1 parte en \'in_progress\' por técnico');

        $this->service->start($workReport2);
    }

    /**
     * Test: finish() cierra tramo si estaba activo, setea status finished y finished_at
     */
    public function test_finish_closes_active_track_and_sets_finished(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        sleep(1);
        $this->service->finish($workReport, 'Resumen del trabajo');

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_FINISHED, $workReport->status);
        $this->assertNotNull($workReport->finished_at);
        $this->assertNull($workReport->active_started_at);
        $this->assertEquals('Resumen del trabajo', $workReport->summary);
        $this->assertGreaterThanOrEqual(1, $workReport->total_seconds);

        $event = $workReport->events()->where('type', WorkReportEvent::TYPE_FINISH)->first();
        $this->assertNotNull($event);
    }

    /**
     * Test: finish() puede finalizar desde paused también
     */
    public function test_finish_can_finish_from_paused(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        $this->service->pause($workReport);
        $this->service->finish($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_FINISHED, $workReport->status);
    }

    /**
     * Test: finish() falla si no está en estado válido
     */
    public function test_finish_fails_if_not_valid_state(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        // Intentar finalizar sin iniciar (está paused, debería funcionar)
        // Pero si está finished, debe fallar
        $this->service->finish($workReport);
        $workReport->refresh();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede finalizar un parte que está en estado');

        $this->service->finish($workReport);
    }

    /**
     * Test: validate() solo si estaba finished, setea status validated, validated_at y validated_by
     */
    public function test_validate_sets_status_and_creates_event(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();
        
        // Añadir crédito suficiente para validar
        $balanceService = new BalanceService();
        $balanceService->credit($client, 10000, 'bono_test');
        
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        sleep(1); // Trabajar al menos 1 segundo
        $this->service->finish($workReport);
        $this->service->validate($workReport, $validator);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_VALIDATED, $workReport->status);
        $this->assertNotNull($workReport->validated_at);
        $this->assertEquals($validator->id, $workReport->validated_by);

        $event = $workReport->events()->where('type', WorkReportEvent::TYPE_VALIDATE)->first();
        $this->assertNotNull($event);
    }

    /**
     * Test: validate() falla si no está finished (idempotencia)
     */
    public function test_validate_fails_if_not_finished(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        // Intentar validar sin finalizar
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede validar un parte que está en estado');

        $this->service->validate($workReport, $validator);
    }

    /**
     * Test: validate() es idempotente (no falla si ya está validated)
     */
    public function test_validate_is_idempotent(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $validator = User::factory()->create();
        
        // Añadir crédito suficiente para validar
        $balanceService = new BalanceService();
        $balanceService->credit($client, 10000, 'bono_test');
        
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        sleep(1); // Trabajar al menos 1 segundo
        $this->service->finish($workReport);
        $this->service->validate($workReport, $validator);

        // Intentar validar de nuevo (debe ser idempotente)
        $this->service->validate($workReport, $validator);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_VALIDATED, $workReport->status);

        // Debe haber solo un evento de validación
        $validateEvents = $workReport->events()->where('type', WorkReportEvent::TYPE_VALIDATE)->count();
        $this->assertEquals(1, $validateEvents);
    }

    /**
     * Test: start() es idempotente (no falla si ya está in_progress)
     */
    public function test_start_is_idempotent(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        $initialStartedAt = $workReport->fresh()->active_started_at;

        // Intentar iniciar de nuevo (debe ser idempotente)
        $this->service->start($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertEquals($initialStartedAt->format('Y-m-d H:i:s'), $workReport->active_started_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test: pause() es idempotente (no falla si ya está paused)
     */
    public function test_pause_is_idempotent(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        $this->service->pause($workReport);
        $initialTotalSeconds = $workReport->fresh()->total_seconds;

        // Intentar pausar de nuevo (debe ser idempotente)
        $this->service->pause($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_PAUSED, $workReport->status);
        $this->assertEquals($initialTotalSeconds, $workReport->total_seconds);
    }

    /**
     * Test: resume() es idempotente (no falla si ya está in_progress)
     */
    public function test_resume_is_idempotent(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        $this->service->start($workReport);
        $initialStartedAt = $workReport->fresh()->active_started_at;

        // Intentar reanudar cuando ya está activo (debe ser idempotente)
        $this->service->resume($workReport);

        $workReport->refresh();
        $this->assertEquals(WorkReport::STATUS_IN_PROGRESS, $workReport->status);
        $this->assertEquals($initialStartedAt->format('Y-m-d H:i:s'), $workReport->active_started_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test: el tiempo en pausa NO suma al total_seconds
     */
    public function test_pause_time_does_not_add_to_total(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create();
        $workReport = $this->service->create($client, $technician);

        // Iniciar, trabajar 1 segundo, pausar
        $this->service->start($workReport);
        sleep(1);
        $this->service->pause($workReport);
        $totalAfterPause = $workReport->fresh()->total_seconds;

        // Esperar en pausa (este tiempo NO debe sumar)
        sleep(2);

        // Reanudar y trabajar 1 segundo más
        $this->service->resume($workReport);
        sleep(1);
        $this->service->pause($workReport);
        $totalAfterSecondPause = $workReport->fresh()->total_seconds;

        // El total debe ser aproximadamente 2 segundos (1 + 1), no 4 (1 + 2 + 1)
        $this->assertGreaterThanOrEqual(2, $totalAfterSecondPause);
        $this->assertLessThan(4, $totalAfterSecondPause);
    }
}
