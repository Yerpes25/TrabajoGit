<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\BonusIssue;
use App\Models\Client;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BalanceService;
use App\Services\BonusService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusCatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->userService = new UserService();
    }

    /**
     * Test: Admin puede crear un bono.
     */
    public function test_admin_can_create_bonus(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.bonuses.store'), [
            'name' => 'Bono 10 horas',
            'description' => 'Bono de prueba de 10 horas',
            'seconds_total' => 36000, // 10 horas
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.bonuses.index'));
        $response->assertSessionHas('success');

        // Verificar que se creó el bono
        $bonus = Bonus::where('name', 'Bono 10 horas')->first();
        $this->assertNotNull($bonus);
        $this->assertEquals(36000, $bonus->seconds_total);
        $this->assertTrue($bonus->is_active);
    }

    /**
     * Test: Admin puede actualizar un bono.
     */
    public function test_admin_can_update_bonus(): void
    {
        $this->actingAs($this->admin);

        // Crear bono
        $bonus = Bonus::create([
            'name' => 'Bono Original',
            'description' => 'Descripción original',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        $response = $this->put(route('admin.bonuses.update', $bonus), [
            'name' => 'Bono Actualizado',
            'description' => 'Descripción actualizada',
            'seconds_total' => 7200,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.bonuses.index'));
        $response->assertSessionHas('success');

        // Verificar actualización
        $bonus->refresh();
        $this->assertEquals('Bono Actualizado', $bonus->name);
        $this->assertEquals(7200, $bonus->seconds_total);
    }

    /**
     * Test: Admin puede archivar un bono (is_active=false).
     */
    public function test_admin_can_archive_bonus(): void
    {
        $this->actingAs($this->admin);

        $bonus = Bonus::create([
            'name' => 'Bono a archivar',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        $response = $this->put(route('admin.bonuses.update', $bonus), [
            'name' => 'Bono a archivar',
            'seconds_total' => 3600,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.bonuses.index'));
        $response->assertSessionHas('success');

        // Verificar archivado
        $bonus->refresh();
        $this->assertFalse($bonus->is_active);
    }

    /**
     * Test: Admin NO puede eliminar físicamente un bono con emisiones (solo archivar).
     */
    public function test_admin_cannot_delete_bonus_with_issues(): void
    {
        $this->actingAs($this->admin);

        // Crear bono
        $bonus = Bonus::create([
            'name' => 'Bono con emisiones',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        // Crear cliente y emitir bono (crea emisión)
        $client = $this->userService->createClient(
            ['name' => 'Client', 'email' => 'client@test.com', 'password' => 'pass', 'is_active' => true],
            ['name' => 'Client Name']
        );

        $bonusService = new BonusService(
            new BalanceService(new AuditService()),
            new AuditService()
        );
        $bonusService->issue($bonus, $client, $this->admin);

        $bonusId = $bonus->id;

        // Intentar eliminar
        $response = $this->delete(route('admin.bonuses.destroy', $bonus));

        $response->assertRedirect(route('admin.bonuses.index'));
        $response->assertSessionHas('success');

        // Verificar que NO se eliminó físicamente, solo se archivó
        $this->assertNotNull(Bonus::find($bonusId));
        $bonus->refresh();
        $this->assertFalse($bonus->is_active);
    }

    /**
     * Test: Admin puede eliminar físicamente un bono sin emisiones.
     */
    public function test_admin_can_delete_bonus_without_issues(): void
    {
        $this->actingAs($this->admin);

        // Crear bono sin emisiones
        $bonus = Bonus::create([
            'name' => 'Bono sin emisiones',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        $bonusId = $bonus->id;

        // Eliminar
        $response = $this->delete(route('admin.bonuses.destroy', $bonus));

        $response->assertRedirect(route('admin.bonuses.index'));
        $response->assertSessionHas('success');

        // Verificar eliminación física
        $this->assertNull(Bonus::find($bonusId));
    }

    /**
     * Test: Admin puede ver la lista de bonos.
     */
    public function test_admin_can_view_bonuses_index(): void
    {
        $this->actingAs($this->admin);

        // Crear algunos bonos
        Bonus::create(['name' => 'Bono 1', 'seconds_total' => 3600, 'is_active' => true]);
        Bonus::create(['name' => 'Bono 2', 'seconds_total' => 7200, 'is_active' => true]);

        $response = $this->get(route('admin.bonuses.index'));

        $response->assertStatus(200);
        $response->assertSee('Bono 1');
        $response->assertSee('Bono 2');
    }

    /**
     * Test: Admin puede ver el detalle de un bono.
     */
    public function test_admin_can_view_bonus_detail(): void
    {
        $this->actingAs($this->admin);

        $bonus = Bonus::create([
            'name' => 'Bono Detail',
            'description' => 'Descripción del bono',
            'seconds_total' => 3600,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.bonuses.show', $bonus));

        $response->assertStatus(200);
        $response->assertSee('Bono Detail');
        $response->assertSee('Descripción del bono');
    }
}
