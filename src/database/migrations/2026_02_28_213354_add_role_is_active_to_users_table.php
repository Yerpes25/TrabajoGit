<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role: rol del usuario (admin, technician, client)
            // Se usa enum para garantizar valores válidos según reglas de negocio
            $table->enum('role', ['admin', 'technician', 'client'])->default('client')->after('password');
            // is_active: indica si el usuario puede acceder al sistema
            // Si false, el usuario no debe poder hacer login o debe ser desconectado
            $table->boolean('is_active')->default(true)->after('role');
            // last_login_at: timestamp del último acceso (opcional, para auditoría)
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Índices para búsquedas frecuentes
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};
