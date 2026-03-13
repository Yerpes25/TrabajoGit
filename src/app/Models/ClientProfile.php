<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'balance_seconds',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance_seconds' => 'integer',
    ];

    /**
     * Relación N:1 con Client
     * El perfil pertenece a un cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Accessor para mostrar el balance de segundos formateado
     * en horas y minutos (ej: 1h 05m)
     */
    public function getBalanceFormattedAttribute(): string
    {
        $hours = floor($this->balance_seconds / 3600);
        $minutes = floor(($this->balance_seconds % 3600) / 60);

        return sprintf('%dh %02dm', $hours, $minutes);
    }
}
