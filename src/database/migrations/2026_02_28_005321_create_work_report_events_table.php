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
        Schema::create('work_report_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_report_id')->constrained('work_reports')->onDelete('cascade');
            // type: tipo de evento del cronómetro y trazabilidad
            // start, pause, resume, finish, validate, edit
            $table->string('type')->index();
            // occurred_at: momento en que ocurrió el evento (puede diferir de created_at)
            $table->dateTime('occurred_at')->index();
            // elapsed_seconds_after: segundos acumulados tras este evento
            // Se calcula sumando todos los tramos activos hasta este punto
            $table->bigInteger('elapsed_seconds_after')->default(0);
            // metadata: información adicional (motivo pausa, cambios, diffs, etc.)
            $table->json('metadata')->nullable();
            // created_by: usuario que generó el evento (opcional)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índice para búsquedas por parte y orden cronológico
            $table->index(['work_report_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_report_events');
    }
};
