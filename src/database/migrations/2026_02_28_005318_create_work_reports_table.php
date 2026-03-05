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
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            // technician_id: usuario con role=technician que realiza el parte
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable(); // Título breve del parte
            $table->text('description')->nullable(); // Descripción inicial/observaciones
            $table->text('summary')->nullable(); // Resumen de lo realizado al finalizar/validar
            // status: estados del parte (in_progress, paused, finished, validated)
            // Regla core: un técnico solo puede tener 1 parte en in_progress
            $table->string('status')->index(); // in_progress | paused | finished | validated
            // total_seconds: cache del tiempo acumulado (en segundos)
            // Se actualiza al pausar/finalizar sumando el delta desde active_started_at
            // Las pausas NO suman al total
            $table->bigInteger('total_seconds')->default(0);
            // active_started_at: inicio del tramo activo actual (cuando está in_progress)
            // Se setea al start/resume, se limpia al pause/finish
            $table->dateTime('active_started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('validated_at')->nullable();
            // validated_by: usuario que valida el parte (según requisito: técnico)
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index(['technician_id', 'status']); // Para verificar "solo 1 activo por técnico"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
