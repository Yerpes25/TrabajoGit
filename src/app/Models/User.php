<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class
User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     *
     * @param string $role Rol a verificar
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Verifica si el usuario es admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica si el usuario es técnico.
     *
     * @return bool
     */
    public function isTechnician(): bool
    {
        return $this->hasRole('technician');
    }

    /**
     * Verifica si el usuario es cliente.
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Relación 1:1 con Client
     * Un usuario con role=client puede tener un cliente asociado.
     * Regla: Esta relación reemplaza la búsqueda por email que existía antes.
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Relación 1:N con WorkReport (como técnico)
     * Un técnico puede tener muchos partes de trabajo.
     */
    public function workReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkReport::class, 'technician_id');
    }
}
