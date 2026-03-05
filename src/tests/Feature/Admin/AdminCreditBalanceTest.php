<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\User;
use App\Models\BalanceMovement;
use App\Models\AuditLog;
use App\Services\BalanceService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreditBalanceTest extends TestCase
{
    use RefreshDatabase;

    private BalanceService $balanceService;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new BalanceService(new AuditService());
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Admin puede asignar saldo a un cliente
     */
    public function test_admin_can_credit_balance_to_client(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'test@example.com']);

        $response = $this->actingAs($this->admin)->post(route('admin.clients.credit', $client), [
            'hours' => 10.5,
            'reason' => 'Bono inicial',
        ]);

        $response->assertRedirect(route('admin.clients.show', $client));
        $response->assertSessionHas('success');

        // Verificar que se creó el movimiento
        $movement = BalanceMovement::where('client_id', $client->id)
            ->where('type', 'credit')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(37800, $movement->amount_seconds); // 10.5 horas = 37800 segundos
        $this->assertEquals('Bono inicial', $movement->reason);
        $this->assertEquals($this->admin->id, $movement->created_by);
    }

    /**
     * Test: Asignar saldo crea audit log
     */
    public function test_credit_creates_audit_log(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'test@example.com']);

        $this->actingAs($this->admin)->post(route('admin.clients.credit', $client), [
            'hours' => 5,
            'reason' => 'admin_credit',
        ]);

        $auditLog = AuditLog::where('event', 'saldo_change')
            ->where('entity_type', 'BalanceMovement')
            ->where('actor_id', $this->admin->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('credit', $auditLog->payload['type'] ?? null);
    }

    /**
     * Test: Validación de horas mínimas
     */
    public function test_credit_validates_minimum_hours(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'test@example.com']);

        $response = $this->actingAs($this->admin)->post(route('admin.clients.credit', $client), [
            'hours' => 0,
        ]);

        $response->assertSessionHasErrors(['hours']);
    }

    /**
     * Test: Conversión correcta de horas a segundos
     */
    public function test_credit_converts_hours_to_seconds_correctly(): void
    {
        $client = Client::create(['name' => 'Cliente Test', 'email' => 'test@example.com']);

        $this->actingAs($this->admin)->post(route('admin.clients.credit', $client), [
            'hours' => 2.5,
        ]);

        $movement = BalanceMovement::where('client_id', $client->id)->first();
        $this->assertEquals(9000, $movement->amount_seconds); // 2.5 horas = 9000 segundos
    }
}
