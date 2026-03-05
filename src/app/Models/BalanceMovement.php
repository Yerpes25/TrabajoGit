<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BalanceMovement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'amount_seconds',
        'type',
        'reason',
        'reference_type',
        'reference_id',
        'created_by',
        'metadata',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_seconds' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relación N:1 con Client
     * El movimiento pertenece a un cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación N:1 con User (creador del movimiento)
     * Usuario que generó el movimiento (opcional)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación polimórfica con la entidad referenciada
     * Permite relacionar el movimiento con otras entidades (ej: WorkReport)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }
}
