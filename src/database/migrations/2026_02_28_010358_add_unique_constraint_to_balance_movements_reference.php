<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Añade constraint único para garantizar idempotencia fuerte:
     * No puede haber dos movimientos con la misma referencia (reference_type, reference_id, reason).
     * Esto previene doble cargo incluso en condiciones de concurrencia.
     */
    public function up(): void
    {
        Schema::table('balance_movements', function (Blueprint $table) {
            // Constraint único para evitar doble cargo por el mismo work_report
            // Solo aplica cuando reference_type y reference_id no son null
            // NOTA: MySQL/MariaDB no soporta índices únicos parciales directamente,
            // así que usamos un índice único en (reference_type, reference_id, reason)
            // que solo será efectivo cuando estos campos no sean null
            $table->unique(['reference_type', 'reference_id', 'reason'], 'balance_movements_reference_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_movements', function (Blueprint $table) {
            $table->dropUnique('balance_movements_reference_unique');
        });
    }
};
