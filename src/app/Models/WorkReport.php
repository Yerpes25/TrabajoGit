<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkReport extends Model
{
    /**
     * Estados válidos del parte
     */
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_VALIDATED = 'validated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'technician_id',
        'title',
        'description',
        'summary',
        'status',
        'total_seconds',
        'active_started_at',
        'finished_at',
        'validated_at',
        'validated_by',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_seconds' => 'integer',
        'active_started_at' => 'datetime',
        'finished_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    /**
     * Relación N:1 con Client
     * El parte pertenece a un cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación N:1 con User (técnico)
     * El parte pertenece a un técnico
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Relación N:1 con User (validador)
     * Usuario que validó el parte (opcional)
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relación 1:N con WorkReportEvent
     * Historial de eventos del cronómetro y trazabilidad
     */
    public function events(): HasMany
    {
        return $this->hasMany(WorkReportEvent::class);
    }

    /**
     * Relación 1:N con Evidence
     * Archivos adjuntos (evidencias) asociados al parte
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * Verifica si el parte está en progreso
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Verifica si el parte está pausado
     *
     * @return bool
     */
    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    /**
     * Verifica si el parte está finalizado
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    /**
     * Verifica si el parte está validado
     *
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->status === self::STATUS_VALIDATED;
    }
}
