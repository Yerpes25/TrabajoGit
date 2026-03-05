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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // event: tipo de evento auditado (login, saldo_change, validate, delete, edit, etc.)
            $table->string('event')->index();
            // actor_id: usuario que realizó la acción (nullable para acciones del sistema)
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            // entity_type: tipo de entidad afectada (ej: WorkReport, Client, BalanceMovement)
            $table->string('entity_type')->nullable()->index();
            // entity_id: ID de la entidad afectada
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            // ip: dirección IP desde la que se realizó la acción (nullable)
            $table->string('ip')->nullable();
            // user_agent: agente de usuario del navegador/cliente (nullable)
            $table->text('user_agent')->nullable();
            // payload: detalles adicionales en formato JSON (nullable)
            $table->json('payload')->nullable();
            // created_at: momento en que ocurrió el evento
            // NOTE: No se incluye updated_at porque audit_logs es append-only (no se edita)
            $table->timestamp('created_at')->useCurrent();

            // Índice compuesto para búsquedas por entidad
            $table->index(['entity_type', 'entity_id']);
            // Índice compuesto para búsquedas por actor y evento
            $table->index(['actor_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
