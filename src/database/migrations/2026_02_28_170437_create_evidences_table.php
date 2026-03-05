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
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_report_id')->constrained('work_reports')->onDelete('cascade');
            // uploaded_by: usuario que subió la evidencia (según requisito: solo técnico)
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            // storage_disk: nombre del disco usado (public ahora, s3 futuro)
            // Se guarda para poder cambiar de disco sin perder referencia
            $table->string('storage_disk');
            // storage_path: ruta/key del archivo en el disco
            // Para public: ruta relativa desde storage/app/public
            // Para S3: key del objeto en el bucket
            $table->string('storage_path');
            // original_name: nombre original del archivo subido
            $table->string('original_name');
            // mime_type: tipo MIME del archivo (opcional pero recomendado)
            $table->string('mime_type')->nullable();
            // size_bytes: tamaño del archivo en bytes (opcional pero recomendado)
            $table->bigInteger('size_bytes')->nullable();
            // checksum: hash del archivo para verificación de integridad (opcional)
            $table->string('checksum')->nullable();
            // metadata: información adicional en formato JSON (opcional)
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('work_report_id');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
