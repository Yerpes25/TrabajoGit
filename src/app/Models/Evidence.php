<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    /**
     * Nombre de la tabla (Laravel infiere 'evidence' por defecto, pero necesitamos 'evidences')
     *
     * @var string
     */
    protected $table = 'evidences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_report_id',
        'uploaded_by',
        'storage_disk',
        'storage_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum',
        'metadata',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size_bytes' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relación N:1 con WorkReport
     * La evidencia pertenece a un parte
     */
    public function workReport(): BelongsTo
    {
        return $this->belongsTo(WorkReport::class);
    }

    /**
     * Relación N:1 con User (usuario que subió la evidencia)
     * Usuario que subió el archivo (según requisito: solo técnico)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
