<?php

namespace App\Services;

use App\Models\Evidence;
use App\Models\WorkReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio para gestionar evidencias (archivos adjuntos) asociadas a work_reports.
 *
 * Reglas de negocio:
 * - Evidencias son opcionales
 * - Solo el técnico puede subir evidencias (validación en upload)
 * - Almacenamiento configurable vía EVIDENCE_DISK (default: public)
 * - Preparado para S3: código agnóstico del disco, cambio solo por .env
 * - Ruta sugerida: work_reports/{work_report_id}/evidences/{uuid}-{originalName}
 */
class EvidenceService
{
    private ?AuditService $auditService;

    public function __construct(?AuditService $auditService = null)
    {
        $this->auditService = $auditService;
    }

    /**
     * Obtiene el disco configurado para evidencias.
     *
     * Por defecto usa 'public', pero puede cambiarse a 's3' vía EVIDENCE_DISK en .env.
     * Esto permite cambiar de almacenamiento local a S3 sin modificar código.
     *
     * @return string Nombre del disco
     */
    private function getEvidenceDisk(): string
    {
        return env('EVIDENCE_DISK', 'public');
    }

    /**
     * Sube una evidencia (archivo) asociada a un work_report.
     *
     * Guarda el archivo en el disco configurado y crea el registro en la base de datos.
     * La ruta del archivo sigue el patrón: work_reports/{work_report_id}/evidences/{uuid}-{originalName}
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @param UploadedFile $file Archivo a subir
     * @param User|int $uploadedBy Usuario que sube el archivo (debe ser técnico)
     * @param array|null $metadata Información adicional en formato array (opcional)
     * @return Evidence Evidencia creada
     * @throws InvalidArgumentException Si los parámetros son inválidos o el usuario no es técnico
     * @throws RuntimeException Si falla la subida del archivo
     */
    public function upload(WorkReport|int $workReport, UploadedFile $file, User|int $uploadedBy, ?array $metadata = null): Evidence
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        // Obtener usuario si se pasó un ID
        $uploadedById = null;
        if ($uploadedBy instanceof User) {
            $uploadedById = $uploadedBy->id;
        } elseif (is_int($uploadedBy)) {
            $uploadedById = $uploadedBy;
            $uploadedBy = User::findOrFail($uploadedById);
        } else {
            throw new InvalidArgumentException('El usuario que sube el archivo es obligatorio.');
        }

        // Validación: el usuario debe ser técnico (por ahora)
        // NOTE: En el futuro esto se validará con policies, pero por ahora validamos aquí
        // TODO: Mover esta validación a policies cuando se implementen

        // Validación: el archivo es requerido
        if (!$file->isValid()) {
            throw new InvalidArgumentException('El archivo no es válido o no se pudo subir.');
        }

        // Obtener disco configurado
        $disk = $this->getEvidenceDisk();

        // Generar nombre único para el archivo
        // Patrón: {uuid}-{originalName} para evitar colisiones
        $uuid = Str::uuid()->toString();
        $originalName = $file->getClientOriginalName();
        $fileName = $uuid . '-' . $originalName;

        // Ruta donde se guardará el archivo
        // Patrón: work_reports/{work_report_id}/evidences/{uuid}-{originalName}
        $directory = "work_reports/{$workReport->id}/evidences";
        $storagePath = "{$directory}/{$fileName}";

        try {
            // Guardar archivo en el disco configurado
            // NOTE: putFileAs() preserva la extensión y maneja nombres de archivo automáticamente
            $savedPath = Storage::disk($disk)->putFileAs(
                $directory,
                $file,
                $fileName
            );

            // Si putFileAs() devuelve solo el nombre del archivo, construir la ruta completa
            if (!str_contains($savedPath, '/')) {
                $savedPath = "{$directory}/{$savedPath}";
            }

            // Obtener metadatos del archivo
            $mimeType = $file->getMimeType();
            $sizeBytes = $file->getSize();

            // Calcular checksum (hash MD5) para verificación de integridad
            $checksum = md5_file($file->getRealPath());

            // Crear registro en base de datos
            $evidence = Evidence::create([
                'work_report_id' => $workReport->id,
                'uploaded_by' => $uploadedById,
                'storage_disk' => $disk,
                'storage_path' => $savedPath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size_bytes' => $sizeBytes,
                'checksum' => $checksum,
                'metadata' => $metadata,
            ]);

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            // NOTE: El AuditService ya captura excepciones internamente, pero añadimos try-catch
            // adicional por si acaso para garantizar que nunca interrumpa el flujo principal
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'evidence_uploaded',
                        $uploadedById,
                        'Evidence',
                        $evidence->id,
                        [
                            'work_report_id' => $workReport->id,
                            'original_name' => $originalName,
                            'mime_type' => $mimeType,
                            'size_bytes' => $sizeBytes,
                            'storage_disk' => $disk,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    \Illuminate\Support\Facades\Log::warning('Error al registrar auditoría de subida de evidencia', [
                        'error' => $e->getMessage(),
                        'evidence_id' => $evidence->id,
                    ]);
                }
            }

            return $evidence;
        } catch (\Exception $e) {
            // Si falla la creación del registro, intentar eliminar el archivo subido
            try {
                Storage::disk($disk)->delete($storagePath);
            } catch (\Exception $deleteException) {
                // Ignorar error al eliminar (el archivo puede no existir)
            }

            throw new RuntimeException(
                "Error al subir la evidencia: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Lista todas las evidencias asociadas a un work_report.
     *
     * @param WorkReport|int $workReport Parte o ID del parte
     * @return \Illuminate\Database\Eloquent\Collection Colección de evidencias
     */
    public function listByWorkReport(WorkReport|int $workReport): \Illuminate\Database\Eloquent\Collection
    {
        // Obtener parte si se pasó un ID
        if (is_int($workReport)) {
            $workReport = WorkReport::findOrFail($workReport);
        }

        return Evidence::where('work_report_id', $workReport->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Elimina una evidencia (archivo y registro).
     *
     * Elimina el archivo del disco y el registro de la base de datos.
     * Si el archivo no existe en el disco, elimina el registro de todas formas.
     *
     * @param Evidence|int $evidence Evidencia o ID de la evidencia
     * @return bool True si se eliminó correctamente
     * @throws RuntimeException Si falla la eliminación
     */
    public function delete(Evidence|int $evidence): bool
    {
        // Obtener evidencia si se pasó un ID
        if (is_int($evidence)) {
            $evidence = Evidence::findOrFail($evidence);
        }

        $disk = $evidence->storage_disk;
        $storagePath = $evidence->storage_path;

        try {
            // Intentar eliminar el archivo del disco (si existe)
            // NOTE: No fallamos si el archivo no existe (puede haber sido eliminado manualmente)
            if (Storage::disk($disk)->exists($storagePath)) {
                Storage::disk($disk)->delete($storagePath);
            }

            // Guardar información antes de eliminar para auditoría
            $evidenceId = $evidence->id;
            $workReportId = $evidence->work_report_id;
            $originalName = $evidence->original_name;
            $uploadedById = $evidence->uploaded_by;

            // Eliminar registro de la base de datos
            $evidence->delete();

            // Registrar auditoría (no debe interrumpir el flujo si falla)
            // NOTE: El AuditService ya captura excepciones internamente, pero añadimos try-catch
            // adicional por si acaso para garantizar que nunca interrumpa el flujo principal
            if ($this->auditService) {
                try {
                    $this->auditService->log(
                        'evidence_deleted',
                        $uploadedById,
                        'Evidence',
                        $evidenceId,
                        [
                            'work_report_id' => $workReportId,
                            'original_name' => $originalName,
                            'storage_disk' => $disk,
                        ]
                    );
                } catch (\Exception $e) {
                    // Si falla la auditoría, registrar warning pero no interrumpir
                    \Illuminate\Support\Facades\Log::warning('Error al registrar auditoría de eliminación de evidencia', [
                        'error' => $e->getMessage(),
                        'evidence_id' => $evidenceId,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Error al eliminar la evidencia: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Obtiene la URL pública de una evidencia.
     *
     * Para discos 'public', genera URL relativa que requiere storage:link.
     * Para discos 's3', devuelve URL temporal o permanente según configuración.
     *
     * @param Evidence $evidence Evidencia
     * @param int|null $expirationMinutes Minutos de expiración para URLs temporales (S3)
     * @return string URL del archivo
     */
    public function getUrl(Evidence $evidence, ?int $expirationMinutes = null): string
    {
        $disk = $evidence->storage_disk;

        if ($disk === 's3' && $expirationMinutes !== null) {
            // URL temporal para S3
            return Storage::disk($disk)->temporaryUrl(
                $evidence->storage_path,
                now()->addMinutes($expirationMinutes)
            );
        }

        // URL permanente (public o S3)
        return Storage::disk($disk)->url($evidence->storage_path);
    }
}
