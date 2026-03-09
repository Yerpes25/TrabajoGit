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
        Schema::table('clients', function (Blueprint $table) {
            // Añadir columna user_id nullable (se convertirá a NOT NULL después del backfill si es necesario)
            // NOTE: Nullable inicialmente para permitir migración segura sin romper datos existentes
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->index('user_id');
            // FK con restricción: solo usuarios con role=client pueden estar asociados
            // NOTE: La validación de role=client se hace a nivel de aplicación, no en DB
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Si se elimina el usuario, mantener el cliente pero sin user_id
        });

        // Backfill: asociar clients existentes con users por email
        // Regla: Solo usuarios con role=client pueden estar asociados
        // NOTE: Esto resuelve la relación implícita por email que existía antes

        $driver = Schema::getConnection()->getDriverName();
        if($driver === 'sqlite'){
            DB::statement("
                UPDATE clients
                SET user_id = (
                    SELECT id
                    FROM users
                    WHERE user.email = clients.email
                        AND users.role = 'client'
                    LIMIT 1
                )
           ");
        } else {
            DB::statement("
                UPDATE clients c
                INNER JOIN users u ON c.email = u.email AND u.role = 'client'
                SET c.user_id = u.id
                WHERE c.user_id IS NULL
            ");
        }
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
