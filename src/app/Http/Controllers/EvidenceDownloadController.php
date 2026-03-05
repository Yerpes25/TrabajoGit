<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller para descargar evidencias de forma segura.
 *
 * Regla: NO usar URLs públicas directas (/storage/... ni Storage::url) en vistas.
 * Todas las descargas deben pasar por este endpoint que verifica permisos.
 *
 * Autorización:
 * - Admin: puede descargar cualquier evidencia
 * - Technician: solo evidencias de partes donde work_reports.technician_id = auth()->id()
 * - Client: solo evidencias de partes del cliente autenticado por FK y solo si está finished/validated
 */
class EvidenceDownloadController extends Controller
{
    use AuthorizesRequests;

    /**
     * Descarga una evidencia de forma segura.
     *
     * Verifica permisos mediante EvidencePolicy@download y descarga el archivo
     * usando Storage::disk($evidence->storage_disk)->download(...).
     *
     * Regla: Mantener EVIDENCE_DISK configurable (descargar con Storage::disk($evidence->storage_disk)).
     *
     * @param Request $request
     * @param Evidence $evidence
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException Si el usuario no tiene permiso
     */
    public function download(Request $request, Evidence $evidence)
    {
        // Verificar permisos mediante Policy
        $this->authorize('download', $evidence);

        // Obtener el disco configurado en la evidencia
        $disk = $evidence->storage_disk;
        $storagePath = $evidence->storage_path;

        // Verificar que el archivo existe
        if (!Storage::disk($disk)->exists($storagePath)) {
            abort(404, 'El archivo de evidencia no existe.');
        }

        // Descargar el archivo usando el disco configurado
        // NOTE: download() maneja automáticamente el nombre del archivo y headers apropiados
        return Storage::disk($disk)->download(
            $storagePath,
            $evidence->original_name
        );
    }
}
