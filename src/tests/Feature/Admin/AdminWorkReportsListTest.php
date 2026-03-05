<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkReport;
use App\Services\WorkReportService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkReportsListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private WorkReportService $workReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->workReportService = new WorkReportService(new BalanceService());
    }

    /**
     * Test: Admin puede ver listado de partes
     */
    public function test_admin_can_view_work_reports_list(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport = $this->workReportService->create($client, $technician);

        $response = $this->actingAs($this->admin)->get(route('admin.work-reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.work-reports.index');
        $response->assertViewHas('workReports');
    }

    /**
     * Test: Filtro por cliente funciona
     */
    public function test_filter_by_client_works(): void
    {
        $client1 = Client::create(['name' => 'Cliente 1', 'email' => 'c1@test.com']);
        $client2 = Client::create(['name' => 'Cliente 2', 'email' => 'c2@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        $workReport1 = $this->workReportService->create($client1, $technician);
        $workReport2 = $this->workReportService->create($client2, $technician);

        $response = $this->actingAs($this->admin)->get(route('admin.work-reports.index', [
            'client_id' => $client1->id,
        ]));

        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($workReport1));
        $this->assertFalse($workReports->contains($workReport2));
    }

    /**
     * Test: Filtro por técnico funciona
     */
    public function test_filter_by_technician_works(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician1 = User::factory()->create(['role' => 'technician', 'name' => 'Técnico 1']);
        $technician2 = User::factory()->create(['role' => 'technician', 'name' => 'Técnico 2']);

        $workReport1 = $this->workReportService->create($client, $technician1);
        $workReport2 = $this->workReportService->create($client, $technician2);

        $response = $this->actingAs($this->admin)->get(route('admin.work-reports.index', [
            'technician_id' => $technician1->id,
        ]));

        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($workReport1));
        $this->assertFalse($workReports->contains($workReport2));
    }

    /**
     * Test: Filtro por estado funciona
     */
    public function test_filter_by_status_works(): void
    {
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@test.com']);
        $technician = User::factory()->create(['role' => 'technician']);

        // Crear parte 1 y dejarlo en progreso
        $workReport1 = $this->workReportService->create($client, $technician);
        $this->workReportService->start($workReport1);

        // Crear parte 2, pausar el primero, iniciar el segundo y finalizarlo
        $workReport2 = $this->workReportService->create($client, $technician);
        $this->workReportService->pause($workReport1); // Pausar el primero para poder iniciar el segundo
        $this->workReportService->start($workReport2);
        $this->workReportService->finish($workReport2);

        $response = $this->actingAs($this->admin)->get(route('admin.work-reports.index', [
            'status' => 'finished',
        ]));

        $response->assertStatus(200);
        $workReports = $response->viewData('workReports');
        $this->assertTrue($workReports->contains($workReport2));
        $this->assertFalse($workReports->contains($workReport1));
    }
}
