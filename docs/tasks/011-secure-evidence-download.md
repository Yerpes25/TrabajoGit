## Tarea 011 — Descarga segura de evidencias (evitar acceso por URL pública directa)

**Objetivo**
Aunque el storage sea `public` por simplicidad, la descarga debe ser controlada por permisos:
- Admin: puede descargar cualquiera
- Técnico: solo evidencias de sus partes
- Cliente: solo evidencias de sus partes (finished/validated)

**Motivo**
Si usas `public/storage/...` cualquiera con el link podría acceder.

**Solución**
- Añadir endpoint de descarga:
  - GET `/evidences/{evidence}/download`
- Verificar permisos en controller/policy
- Devolver `Storage::disk(...)->download(...)`
- En las vistas, usar la ruta de descarga (no `Storage::url` directa)

**Archivos**
- `EvidencePolicy` o middleware checker
- `EvidenceDownloadController`
- Ajustar vistas admin/technician/client para links
- Tests acceso: admin ok, tecnico/client solo suyos