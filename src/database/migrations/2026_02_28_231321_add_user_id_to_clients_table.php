<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración para añadir relación FK entre Client y User.
 *
 * Regla: Solo usuarios con role=client pueden estar asociados a un Client.
 * Migración segura: user_id nullable inicialmente para permitir backfill sin romper datos existentes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Paso 1: Añadir columna user_id nullable con índice y FK.
     * Paso 2: Backfill de datos existentes (mapeo por email).
     */
    public function up(): void
    {

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
