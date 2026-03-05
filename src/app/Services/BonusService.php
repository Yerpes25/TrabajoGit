<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\BonusIssue;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service para gestionar la emisión de bonos a clientes.
 *
 * Lógica de negocio centralizada para emitir bonos.
 * Regla: Al emitir un bono, se crea BonusIssue + balance_movement (credit) en transacción.
 */
class BonusService
{
    private BalanceService $balanceService;
    private ?AuditService $auditService;

    public function __construct(BalanceService $balanceService, ?AuditService $auditService = null)
    {
        $this->balanceService = $balanceService;
        $this->auditService = $auditService;
    }

    /**
     * Emite un bono a un cliente.
     *
     * Regla: Se crean BonusIssue y balance_movement (credit) en una transacción para mantener integridad.
     * - BonusIssue con snapshot del bono (seconds_total)
     * - BalanceMovement con reference_type='BonusIssue' y reference_id=bonus_issues.id
     * - metadata incluye bonus_id, bonus_name, seconds_total, note, issued_by
     *
     * @param Bonus $bonus Bono a emitir
     * @param Client $client Cliente receptor
     * @param User $issuer Usuario admin que emite el bono
     * @param string|null $note Nota opcional
     * @return BonusIssue Emisión creada
     * @throws InvalidArgumentException Si el bono no está activo
     * @throws \Exception Si falla la transacción
     */
    public function issue(Bonus $bonus, Client $client, User $issuer, ?string $note = null): BonusIssue
    {
        // Validar que el bono esté activo
        if (!$bonus->is_active) {
            throw new InvalidArgumentException('No se puede emitir un bono archivado.');
        }

        return DB::transaction(function () use ($bonus, $client, $issuer, $note) {
            // Crear BonusIssue con snapshot del bono (seconds_total)
            // NOTE: Se copia el seconds_total del bono para mantener histórico aunque el bono cambie
            $bonusIssue = BonusIssue::create([
                'bonus_id' => $bonus->id,
                'client_id' => $client->id,
                'issued_by' => $issuer->id,
                'seconds_total' => $bonus->seconds_total, // Snapshot
                'note' => $note,
                'metadata' => [
                    'bonus_name' => $bonus->name,
                    'issued_at' => now()->toIso8601String(),
                ],
            ]);

            // Crear balance_movement (credit) usando BalanceService
            // Regla: reference_type='BonusIssue', reference_id=bonus_issues.id
            $metadata = [
                'bonus_id' => $bonus->id,
                'bonus_name' => $bonus->name,
                'seconds_total' => $bonus->seconds_total,
                'issued_by' => $issuer->id,
            ];

            if ($note) {
                $metadata['note'] = $note;
            }

            $this->balanceService->credit(
                $client,
                $bonus->seconds_total,
                'bono', // reason
                'BonusIssue', // reference_type
                $bonusIssue->id, // reference_id
                $issuer->id, // created_by
                $metadata
            );

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'bonus_issued',
                        $issuer->id,
                        'BonusIssue',
                        $bonusIssue->id,
                        [
                            'bonus_id' => $bonus->id,
                            'bonus_name' => $bonus->name,
                            'client_id' => $client->id,
                            'seconds_total' => $bonus->seconds_total,
                            'note' => $note,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    \Illuminate\Support\Facades\Log::warning('Error al registrar auditoría de emisión de bono', [
                        'error' => $e->getMessage(),
                        'bonus_issue_id' => $bonusIssue->id,
                    ]);
                }
            }

            return $bonusIssue;
        });
    }
}
