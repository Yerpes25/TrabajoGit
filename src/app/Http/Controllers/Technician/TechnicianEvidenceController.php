<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\UploadEvidenceRequest;
use App\Models\Evidence;
use App\Models\WorkReport;
use App\Policies\EvidencePolicy;
use App\Services\EvidenceService;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Controller para gestionar evidencias desde el panel técnico.
 *
 * Controller fino: solo orquesta, sin lógica de negocio.
 * La lógica de negocio está en EvidenceService.
 * Autorización centralizada en EvidencePolicy.
 */
class TechnicianEvidenceController extends Controller
{
    use AuthorizesRequests;
    private EvidenceService $evidenceService;

    public function __construct(EvidenceService $evidenceService)
    {
        $this->evidenceService = $evidenceService;
    }

    /**
     * Sube una evidencia (archivo) a un parte.
     *
     * Controller fino: solo valida, sube y redirige.
     * Regla: Solo puede subir evidencias a sus propios partes.
     *
     * @param UploadEvidenceRequest $request
     * @param WorkReport $workReport
     * @return RedirectResponse
     */
    public function upload(UploadEvidenceRequest $request, WorkReport $workReport): RedirectResponse
    {
        // Verificar permisos mediante Policy (upload recibe WorkReport)
        // NOTE: Como upload está en EvidencePolicy pero recibe WorkReport, llamamos directamente a la policy
        $policy = new EvidencePolicy();
        if (!$policy->upload(auth()->user(), $workReport)) {
            abort(403, 'No tienes permiso para subir evidencias a este parte.');
        }

        try {
            $evidence = $this->evidenceService->upload(
                $workReport,
                $request->file('file'),
                auth()->id(),
                $request->input('metadata')
            );

            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Evidencia subida correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', 'Error al subir la evidencia: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una evidencia.
     *
     * Controller fino: solo valida, elimina y redirige.
     * Regla: Solo puede eliminar evidencias de sus propios partes.
     *
     * @param Evidence $evidence
     * @return RedirectResponse
     */
    public function delete(Evidence $evidence): RedirectResponse
    {
        // Verificar permisos mediante Policy
        $this->authorize('delete', $evidence);

        $workReport = $evidence->workReport;

        try {
            $this->evidenceService->delete($evidence);
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('success', 'Evidencia eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('technician.work-reports.show', $workReport)
                ->with('error', 'Error al eliminar la evidencia: ' . $e->getMessage());
        }
    }
}
