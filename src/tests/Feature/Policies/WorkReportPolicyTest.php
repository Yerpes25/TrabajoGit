<?php

namespace Tests\Feature\Policies;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\BalanceService;
use App\Services\AuditService;
use App\Services\WorkReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkReportPolicyTest extends TestCase
{
    use RefreshDatabase;

    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workReportService = new WorkReportService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
    }

    /**
     * Test: Admin puede ver cualquier parte
     */
    public function test_admin_can_view_any_work_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport = $this->workReportService->create($client, $technician);

        $this->assertTrue($admin->can('view', $workReport));
    }

    /**
     * Test: Technician puede ver sus partes y no los de otros
     */
    public function test_technician_can_view_own_work_reports_not_others(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician1 = User::factory()->create(['role' => 'technician']);
        $technician2 = User::factory()->create(['role' => 'technician']);

        $workReport1 = $this->workReportService->create($client, $technician1);
        $workReport2 = $this->workReportService->create($client, $technician2);

        $this->assertTrue($technician1->can('view', $workReport1));
        $this->assertFalse($technician1->can('view', $workReport2));
    }

    /**
     * Test: Client puede ver sus partes y no los de otros
     */
    public function test_client_can_view_own_work_reports_not_others(): void
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

        $workReport1 = $this->workReportService->create($client1, $technician);
        $workReport2 = $this->workReportService->create($client2, $technician);

        // Finalizar ambos para que el cliente pueda verlos
        $this->workReportService->start($workReport1, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport1, null, $technician->id);

        $this->workReportService->start($workReport2, $technician->id);
        sleep(1);
        $this->workReportService->finish($workReport2, null, $technician->id);

        $this->assertTrue($clientUser1->can('view', $workReport1));
        $this->assertFalse($clientUser1->can('view', $workReport2));
    }

    /**
     * Test: Client solo puede ver partes finished o validated
     */
    public function test_client_can_only_view_finished_or_validated_work_reports(): void
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

        // Crear parte validated
        $balanceService = new BalanceService(new AuditService());
        $balanceService->credit($client, 3600, 'test_credit', 'User', $technician->id, $technician->id);

        $validatedReport = $this->workReportService->create($client, $technician);
        $this->workReportService->start($validatedReport, $technician->id);
        sleep(1);
        $this->workReportService->finish($validatedReport, null, $technician->id);
        $this->workReportService->validate($validatedReport, $technician->id);

        // Crear parte in_progress (usar otro técnico para no tener conflicto)
        $technician2 = User::factory()->create(['role' => 'technician']);
        $inProgressReport = $this->workReportService->create($client, $technician2);
        $this->workReportService->start($inProgressReport, $technician2->id);

        $this->assertTrue($clientUser->can('view', $finishedReport));
        $this->assertTrue($clientUser->can('view', $validatedReport));
        $this->assertFalse($clientUser->can('view', $inProgressReport));
    }

    /**
     * Test: Technician puede update/start/pause/resume/finish sus partes
     */
    public function test_technician_can_manage_own_work_reports(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician1 = User::factory()->create(['role' => 'technician']);
        $technician2 = User::factory()->create(['role' => 'technician']);

        $workReport1 = $this->workReportService->create($client, $technician1);
        $workReport2 = $this->workReportService->create($client, $technician2);

        // Technician 1 puede gestionar su parte
        $this->assertTrue($technician1->can('update', $workReport1));
        $this->assertTrue($technician1->can('start', $workReport1));
        $this->assertTrue($technician1->can('pause', $workReport1));
        $this->assertTrue($technician1->can('resume', $workReport1));
        $this->assertTrue($technician1->can('finish', $workReport1));
        $this->assertTrue($technician1->can('validate', $workReport1));

        // Technician 1 NO puede gestionar el parte del technician 2
        $this->assertFalse($technician1->can('update', $workReport2));
        $this->assertFalse($technician1->can('start', $workReport2));
        $this->assertFalse($technician1->can('pause', $workReport2));
    }

    /**
     * Test: Admin puede gestionar cualquier parte
     */
    public function test_admin_can_manage_any_work_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'client@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport = $this->workReportService->create($client, $technician);

        $this->assertTrue($admin->can('view', $workReport));
        $this->assertTrue($admin->can('update', $workReport));
        $this->assertTrue($admin->can('start', $workReport));
        $this->assertTrue($admin->can('pause', $workReport));
        $this->assertTrue($admin->can('resume', $workReport));
        $this->assertTrue($admin->can('finish', $workReport));
        $this->assertTrue($admin->can('validate', $workReport));
    }
}
