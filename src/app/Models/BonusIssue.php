<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para las emisiones/asignaciones de bonos a clientes.
 *
 * Reglas:
 * - Sin caducidad.
 * - seconds_total se copia del bono (snapshot para histórico).
 * - Cada emisión debe crear un movimiento en balance_movements (credit) con referencia a BonusIssue.
 */
class BonusIssue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bonus_id',
        'client_id',
        'issued_by',
        'seconds_total',
        'note',
        'metadata',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'seconds_total' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relación N:1 con Bonus
     * La emisión pertenece a un bono del catálogo
     */
    public function bonus(): BelongsTo
    {
        return $this->belongsTo(Bonus::class);
    }

    /**
     * Relación N:1 con Client
     * La emisión pertenece a un cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación N:1 con User (issued_by)
     * Usuario admin que emitió el bono
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
