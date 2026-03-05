<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Nombre de la tabla
     *
     * @var string
     */
    protected $table = 'audit_logs';

    /**
     * Indica que el modelo no tiene timestamps (solo created_at)
     * NOTE: audit_logs es append-only, no se actualiza
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event',
        'actor_id',
        'entity_type',
        'entity_id',
        'ip',
        'user_agent',
        'payload',
        'created_at',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación N:1 con User (actor)
     * Usuario que realizó la acción (opcional, puede ser null para acciones del sistema)
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
