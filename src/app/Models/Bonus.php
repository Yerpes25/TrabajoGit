<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo para el catálogo de bonos.
 *
 * Reglas:
 * - Los bonos con is_active=false se consideran archivados.
 * - Si un bono tiene emisiones (bonus_issues), no se permite borrado físico (solo archivar).
 */
class Bonus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'seconds_total',
        'is_active',
    ];

    /**
     * Casts para tipos de datos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'seconds_total' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relación 1:N con BonusIssue
     * Un bono puede tener muchas emisiones
     */
    public function bonusIssues(): HasMany
    {
        return $this->hasMany(BonusIssue::class);
    }

    /**
     * Verifica si el bono tiene emisiones asociadas.
     *
     * @return bool
     */
    public function hasIssues(): bool
    {
        return $this->bonusIssues()->exists();
    }
}
