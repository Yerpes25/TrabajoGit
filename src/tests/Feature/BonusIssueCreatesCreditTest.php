<?php

namespace Tests\Feature;

use App\Models\BalanceMovement;
use App\Models\Bonus;
use App\Models\BonusIssue;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BalanceService;
use App\Services\BonusService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusIssueCreatesCreditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private UserService $userService;
    private BonusService $bonusService;
    private BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->userService = new UserService();
        $this->balanceService = new BalanceService(new AuditService());
        $this->bonusService = new BonusService($this->balanceService, new AuditService());
    }

    /**
     * Test: Emitir bono crea bonus_issue.
     */
    public function test_issue_bonus_creates_bonus_issue(): void
    {
        $this->actingAs($this->admin);

        // Crear bono y cliente
        $bonus = Bonus::create([
            'name' => 'Bono Test',
            'seconds_total' => 3600, // 1 hora
            'is_active' => true,
        ]);

        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $response = $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
            'note' => 'Nota de prueba',
        ]);

        $response->assertRedirect(route('admin.clients.show', $client));
        $response->assertSessionHas('success');

        // Verificar que se creó BonusIssue
        $bonusIssue = BonusIssue::where('bonus_id', $bonus->id)
            ->where('client_id', $client->id)
            ->first();

        $this->assertNotNull($bonusIssue);
        $this->assertEquals(3600, $bonusIssue->seconds_total);
        $this->assertEquals('Nota de prueba', $bonusIssue->note);
        $this->assertEquals($this->admin->id, $bonusIssue->issued_by);
    }

    /**
     * Test: Emitir bono crea balance_movement credit con reference_type/id correcto.
     */
    public function test_issue_bonus_creates_balance_movement(): void
    {
        $this->actingAs($this->admin);

        // Crear bono y cliente
        $bonus = Bonus::create([
            'name' => 'Bono Test',
            'seconds_total' => 7200, // 2 horas
            'is_active' => true,
        ]);

        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
            'note' => 'Nota de prueba',
        ]);

        // Verificar que se creó BalanceMovement
        $bonusIssue = BonusIssue::where('bonus_id', $bonus->id)
            ->where('client_id', $client->id)
            ->first();

        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('reference_type', 'BonusIssue')
            ->where('reference_id', $bonusIssue->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('credit', $movement->type);
        $this->assertEquals(7200, $movement->amount_seconds);
        $this->assertEquals('bono', $movement->reason);
        $this->assertEquals('BonusIssue', $movement->reference_type);
        $this->assertEquals($bonusIssue->id, $movement->reference_id);
        $this->assertEquals($this->admin->id, $movement->created_by);

        // Verificar metadata
        $this->assertNotNull($movement->metadata);
        $this->assertEquals($bonus->id, $movement->metadata['bonus_id']);
        $this->assertEquals($bonus->name, $movement->metadata['bonus_name']);
        $this->assertEquals(7200, $movement->metadata['seconds_total']);
        $this->assertEquals($this->admin->id, $movement->metadata['issued_by']);
        $this->assertEquals('Nota de prueba', $movement->metadata['note']);
    }

    /**
     * Test: Emitir bono actualiza client_profiles.balance_seconds.
     */
    public function test_issue_bonus_updates_client_profile_balance(): void
    {
        $this->actingAs($this->admin);

        // Crear bono y cliente
        $bonus = Bonus::create([
            'name' => 'Bono Test',
            'seconds_total' => 10800, // 3 horas
            'is_active' => true,
        ]);

        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $initialBalance = $client->profile->balance_seconds;

        $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
        ]);

        // Verificar que se actualizó el saldo
        $client->profile->refresh();
        $this->assertEquals($initialBalance + 10800, $client->profile->balance_seconds);
    }

    /**
     * Test: Emitir bono crea audit log si está implementado.
     */
    public function test_issue_bonus_creates_audit_log(): void
    {
        $this->actingAs($this->admin);

        // Crear bono y cliente
        $bonus = Bonus::create([
            'name' => 'Bono Test',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
        ]);

        // Verificar que se creó audit log
        $bonusIssue = BonusIssue::where('bonus_id', $bonus->id)
            ->where('client_id', $client->id)
            ->first();

        $auditLog = \App\Models\AuditLog::where('event', 'bonus_issued')
            ->where('entity_type', 'BonusIssue')
            ->where('entity_id', $bonusIssue->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->actor_id);
    }

    /**
     * Test: No-admin no puede acceder a rutas de bonos.
     */
    public function test_non_admin_cannot_access_bonus_routes(): void
    {
        $technician = User::factory()->create(['role' => 'technician', 'is_active' => true]);
        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $this->actingAs($technician);

        // Intentar acceder a rutas de bonos (middleware redirige o devuelve 403)
        $response = $this->get(route('admin.bonuses.index'));
        $this->assertTrue($response->isRedirect() || $response->status() === 403);

        $response = $this->get(route('admin.bonuses.create'));
        $this->assertTrue($response->isRedirect() || $response->status() === 403);

        $bonus = Bonus::create(['name' => 'Bono', 'seconds_total' => 3600, 'is_active' => true]);
        $response = $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
        ]);
        $this->assertTrue($response->isRedirect() || $response->status() === 403);
    }

    /**
     * Test: No se puede emitir un bono archivado.
     */
    public function test_cannot_issue_archived_bonus(): void
    {
        $this->actingAs($this->admin);

        // Crear bono archivado
        $bonus = Bonus::create([
            'name' => 'Bono Archivado',
            'seconds_total' => 3600,
            'is_active' => false, // Archivado
        ]);

        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $response = $this->post(route('admin.clients.bonuses.issue', $client), [
            'bonus_id' => $bonus->id,
        ]);

        // La validación del FormRequest rechaza el bono archivado (where('is_active', true))
        // Por lo tanto, la respuesta será un redirect con errores de validación
        $response->assertSessionHasErrors('bonus_id');
    }
}
