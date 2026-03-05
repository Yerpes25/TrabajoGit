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
        Schema::create('bonus_issues', function (Blueprint $table) {
            $table->id();
            // bonus_id: FK al catálogo de bonos
            $table->foreignId('bonus_id')->constrained('bonuses')->onDelete('restrict');
            // client_id: FK al cliente que recibe el bono
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            // issued_by: FK al usuario admin que emite el bono
            $table->foreignId('issued_by')->constrained('users')->onDelete('restrict');
            // seconds_total: snapshot del bono en el momento de emitir (en segundos)
            // Regla: Se copia del bono para mantener histórico aunque el bono cambie
            $table->bigInteger('seconds_total');
            // note: nota opcional al emitir el bono
            $table->text('note')->nullable();
            // metadata: información adicional en JSON (opcional)
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('bonus_id');
            $table->index('client_id');
            $table->index('issued_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_issues');
    }
};
