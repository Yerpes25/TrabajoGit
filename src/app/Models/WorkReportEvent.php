<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkReportEvent extends Model
{
    /**
     * Tipos de eventos válidos
     */
    public const TYPE_START = 'start';
    public const TYPE_PAUSE = 'pause';
    public const TYPE_RESUME = 'resume';
    public const TYPE_FINISH = 'finish';
    public const TYPE_VALIDATE = 'validate';
    public const TYPE_EDIT = 'edit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_report_id',
        'type',
        'occurred_at',
        'elapsed_seconds_after',
        'metadata',
        'created_by',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'occurred_at' => 'datetime',
        'elapsed_seconds_after' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relación N:1 con WorkReport
     * El evento pertenece a un parte
     */
    public function workReport(): BelongsTo
    {
        return $this->belongsTo(WorkReport::class);
    }

    /**
     * Relación N:1 con User (creador del evento)
     * Usuario que generó el evento (opcional)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
