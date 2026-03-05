<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\BalanceMovement;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private BalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BalanceService();
    }

    /**
     * Test: crédito + crédito + débito = saldo correcto
     */
    public function test_credit_and_debit_calculate_balance_correctly(): void
    {
        // Crear cliente
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Añadir primer crédito de 3600 segundos (1 hora)
        $movement1 = $this->service->credit(
            $client,
            3600,
            'bono_inicial'
        );
        $this->assertInstanceOf(BalanceMovement::class, $movement1);
        $this->assertEquals(3600, $movement1->amount_seconds);
        $this->assertEquals('credit', $movement1->type);
        $this->assertEquals(3600, $this->service->getBalanceSeconds($client));

        // Añadir segundo crédito de 1800 segundos (30 minutos)
        $movement2 = $this->service->credit(
            $client,
            1800,
            'bono_adicional'
        );
        $this->assertEquals(5400, $this->service->getBalanceSeconds($client)); // 3600 + 1800

        // Realizar débito de 1200 segundos (20 minutos)
        $movement3 = $this->service->debit(
            $client,
            1200,
            'validacion_parte'
        );
        $this->assertInstanceOf(BalanceMovement::class, $movement3);
        $this->assertEquals(-1200, $movement3->amount_seconds);
        $this->assertEquals('debit', $movement3->type);
        $this->assertEquals(4200, $this->service->getBalanceSeconds($client)); // 5400 - 1200

        // Verificar que el agregado en client_profiles coincide con el ledger
        $profile = $client->profile;
        $this->assertNotNull($profile);
        $this->assertEquals(4200, $profile->balance_seconds);
        $this->assertEquals(4200, $this->service->getBalanceSeconds($client));
    }

    /**
     * Test: débito sin saldo suficiente falla con excepción
     */
    public function test_debit_fails_when_insufficient_balance(): void
    {
        // Crear cliente
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Añadir crédito de 1000 segundos
        $this->service->credit($client, 1000, 'bono_inicial');
        $this->assertEquals(1000, $this->service->getBalanceSeconds($client));

        // Intentar débito de 2000 segundos (más de lo disponible)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Saldo insuficiente');

        $this->service->debit($client, 2000, 'validacion_parte');

        // Verificar que no se creó ningún movimiento adicional
        $this->assertEquals(1, BalanceMovement::where('client_id', $client->id)->count());
        $this->assertEquals(1000, $this->service->getBalanceSeconds($client));
    }

    /**
     * Test: agregado (client_profiles.balance_seconds) coincide con el ledger
     */
    public function test_profile_balance_matches_ledger(): void
    {
        // Crear cliente
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Realizar múltiples operaciones
        $this->service->credit($client, 5000, 'bono_1');
        $this->service->credit($client, 3000, 'bono_2');
        $this->service->debit($client, 2000, 'validacion_1');
        $this->service->debit($client, 1000, 'validacion_2');

        // Calcular saldo desde ledger
        $ledgerBalance = $this->service->getBalanceSeconds($client);
        $expectedBalance = 5000 + 3000 - 2000 - 1000; // 5000

        $this->assertEquals($expectedBalance, $ledgerBalance);

        // Verificar que el agregado coincide
        $profile = $client->fresh()->profile;
        $this->assertNotNull($profile);
        $this->assertEquals($expectedBalance, $profile->balance_seconds);
        $this->assertEquals($ledgerBalance, $profile->balance_seconds);
    }

    /**
     * Test: credit() con parámetros inválidos lanza excepción
     */
    public function test_credit_validates_parameters(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Segundos negativos o cero
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los segundos deben ser positivos');
        $this->service->credit($client, -100, 'test');

        $this->expectException(InvalidArgumentException::class);
        $this->service->credit($client, 0, 'test');

        // Reason vacío
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El motivo (reason) es obligatorio');
        $this->service->credit($client, 1000, '');
    }

    /**
     * Test: debit() con parámetros inválidos lanza excepción
     */
    public function test_debit_validates_parameters(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Segundos negativos o cero
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los segundos deben ser positivos');
        $this->service->debit($client, -100, 'test');

        $this->expectException(InvalidArgumentException::class);
        $this->service->debit($client, 0, 'test');

        // Reason vacío
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El motivo (reason) es obligatorio');
        $this->service->debit($client, 1000, '');
    }

    /**
     * Test: credit() acepta cliente por ID
     */
    public function test_credit_accepts_client_id(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        $movement = $this->service->credit($client->id, 1000, 'bono');
        $this->assertEquals($client->id, $movement->client_id);
        $this->assertEquals(1000, $this->service->getBalanceSeconds($client->id));
    }

    /**
     * Test: debit() acepta cliente por ID
     */
    public function test_debit_accepts_client_id(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        $this->service->credit($client->id, 2000, 'bono');
        $movement = $this->service->debit($client->id, 500, 'validacion');
        $this->assertEquals($client->id, $movement->client_id);
        $this->assertEquals(1500, $this->service->getBalanceSeconds($client->id));
    }

    /**
     * Test: getBalanceSeconds() con cliente sin movimientos retorna 0
     */
    public function test_get_balance_returns_zero_for_client_without_movements(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals(0, $this->service->getBalanceSeconds($client));
    }

    /**
     * Test: los movimientos son inmutables (no se pueden editar directamente)
     * Nota: Este test verifica que los movimientos se crean correctamente,
     * la inmutabilidad se garantiza a nivel de aplicación (no hay métodos de edición)
     */
    public function test_movements_are_created_correctly(): void
    {
        $client = Client::create([
            'name' => 'Cliente Test',
            'email' => 'test@example.com',
        ]);

        // Crear un usuario para el test de created_by
        $user = \App\Models\User::factory()->create();

        $movement = $this->service->credit(
            $client,
            1000,
            'bono',
            'WorkReport',
            123,
            $user->id,
            ['nota' => 'test']
        );

        $this->assertEquals($client->id, $movement->client_id);
        $this->assertEquals(1000, $movement->amount_seconds);
        $this->assertEquals('credit', $movement->type);
        $this->assertEquals('bono', $movement->reason);
        $this->assertEquals('WorkReport', $movement->reference_type);
        $this->assertEquals(123, $movement->reference_id);
        $this->assertEquals($user->id, $movement->created_by);
        $this->assertEquals(['nota' => 'test'], $movement->metadata);
    }
}
