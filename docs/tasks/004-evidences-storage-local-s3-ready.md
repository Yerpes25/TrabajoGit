## Tarea 004 — Evidences con storage `public` y preparado para S3

**Objetivo**
Implementar la gestión de evidencias (archivos adjuntos) asociadas a `work_reports`:
- Subida, listado y borrado (registro DB + fichero)
- Almacenamiento **local en disco `public`** por ahora
- Diseñado para cambiar a **S3** solo con `.env` (sin tocar código)

**Alcance**
- Incluye:
  - Tabla `evidences` según `docs/DB_SCHEMA.md`
  - Modelo `Evidence` + relaciones
  - Service `EvidenceService`:
    - `upload()`, `listByWorkReport()`, `delete()`
  - Config de storage:
    - disco configurable `EVIDENCE_DISK` con default **public**
  - `storage:link` documentado (y comando de verificación)
  - Tests (Feature):
    - subida (Storage::fake)
    - listado
    - borrado
- Excluye:
  - UI / rutas / controllers
  - Policies completas (se hará en tarea posterior)
  - Notificaciones
  - Integración con auditoría global `audit_logs` (tarea posterior)

**Reglas de negocio**
1. Evidencias son opcionales.
2. Solo el técnico puede subir evidencias (por ahora):
   - `uploaded_by` debe ser un usuario con role `technician`.
3. Evidencias asociadas a un `work_report` existente.
4. Guardar metadata:
   - `storage_disk`, `storage_path`, `original_name`, `mime_type`, `size_bytes`, `metadata` opcional.
5. Preparado para S3:
   - El Service NO debe depender de rutas locales.
   - El disco se elige por configuración: `env('EVIDENCE_DISK', 'public')`.
6. Acceso web (solo cuando se sirva por URL):
   - Se debe usar `php artisan storage:link` para exponer `/storage/...`.

**Tablas / nombres obligatorios**
- `evidences` (exacto)

**Archivos a crear/editar**
- `src/database/migrations/*_create_evidences_table.php`
- `src/app/Models/Evidence.php`
- `src/app/Models/WorkReport.php` (añadir relación evidences si no existe)
- `src/app/Services/EvidenceService.php`
- `src/config/filesystems.php` (añadir `evidence_disk` configurable)
- `src/tests/Feature/EvidenceServiceTest.php`
- (Opcional recomendado) `README.md` o `docs/DEPLOYMENT.md` (nota sobre `storage:link`)

**Notas técnicas (obligatorias)**
- Guardar con:
  - `Storage::disk($disk)->putFileAs(...)` o `put(...)`
- Ruta sugerida:
  - `work_reports/{work_report_id}/evidences/{uuid}-{originalName}`
- `storage_disk` debe guardar el nombre del disco usado (`public` ahora, `s3` futuro).
- Para pruebas usar `Storage::fake('public')` y asegurar que el Service use el disk configurable.
- Validaciones mínimas:
  - archivo requerido en upload
  - `work_report` debe existir
  - `uploaded_by` debe existir y ser técnico

**Checklist de aceptación**
- [ ] Migración `evidences` ejecuta sin error.
- [ ] `EvidenceService::upload()`:
  - guarda archivo en disco configurado (`public` por defecto)
  - crea registro DB coherente
- [ ] `EvidenceService::listByWorkReport()` devuelve evidencias del parte.
- [ ] `EvidenceService::delete()`:
  - elimina el archivo del disco (si existe)
  - elimina registro DB (o soft delete si se decide, pero mantener simple en esta tarea)
- [ ] Tests pasan: `php artisan test`
- [ ] `php artisan storage:link` funciona en local (documentado/comprobado)

**Comandos de verificación**
- `php artisan migrate`
- `php artisan test`
- `php artisan storage:link`