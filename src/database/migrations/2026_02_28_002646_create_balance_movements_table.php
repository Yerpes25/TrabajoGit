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
        Schema::create('balance_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            // amount_seconds: positivo para crédito, negativo para débito (en segundos)
            $table->bigInteger('amount_seconds');
            // type: opcional, se puede deducir del signo de amount_seconds
            $table->string('type')->index(); // 'credit' o 'debit'
            // reason: obligatorio para trazabilidad (ej: 'bono', 'ajuste', 'validacion_parte', 'importacion')
            $table->string('reason')->index();
            // reference_type y reference_id: opcionales, para relacionar con otras entidades (ej: WorkReport)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            // created_by: usuario que generó el movimiento (opcional)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // metadata: información adicional en JSON
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index(['client_id', 'created_at']);
            $table->index(['client_id', 'reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_movements');
    }
};
