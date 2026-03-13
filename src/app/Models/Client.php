<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use Notifiable;
    protected $table = 'clients';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legal_name',
        'tax_id',
        'phone',
        'address',
        'notes',
        'user_id', // FK a users.id (solo usuarios con role=client)
    ];

    /**
     * Relación N:1 con User
     * El cliente pertenece a un usuario (solo usuarios con role=client).
     * Regla: Esta relación reemplaza la búsqueda por email que existía antes.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación 1:1 con ClientProfile
     * Cada cliente tiene un perfil con el saldo agregado
     */
    public function profile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    /**
     * Relación 1:N con BalanceMovement
     * Historial de movimientos de saldo (fuente de verdad)
     */
    public function balanceMovements(): HasMany
    {
        return $this->hasMany(BalanceMovement::class);
    }

    /**
     * Relación 1:N con WorkReport
     * Partes de trabajo asociados al cliente
     */
    public function workReports(): HasMany
    {
        return $this->hasMany(WorkReport::class);
    }

    /**
     * Relación 1:N con BonusIssue
     * Bonos emitidos al cliente
     */
    public function bonusIssues(): HasMany
    {
        return $this->hasMany(BonusIssue::class);
    }
}
