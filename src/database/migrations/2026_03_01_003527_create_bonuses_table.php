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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            // name: nombre del bono (ej: "Bono 10 horas", "Bono mensual")
            $table->string('name')->index();
            // description: descripción opcional del bono
            $table->text('description')->nullable();
            // seconds_total: tiempo del bono en segundos (regla: tiempo siempre en segundos)
            $table->bigInteger('seconds_total')->index();
            // is_active: si está activo (false = archivado)
            // Regla: Si un bono tiene emisiones (bonus_issues), no se permite borrado físico (solo archivar)
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
