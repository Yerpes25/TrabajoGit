## Tarea 005 — Auditoría global (audit_logs) + AuditService + integración mínima

**Objetivo**
Implementar auditoría global para registrar eventos críticos del sistema en la tabla `audit_logs`, con una capa de servicio reutilizable (`AuditService`) y una integración mínima en operaciones ya existentes.

**Alcance**
- Incluye:
  - Migración `audit_logs` según `docs/DB_SCHEMA.md`
  - Modelo `AuditLog`
  - `AuditService` con método principal `log(...)`
  - Integración mínima en:
    - `BalanceService::credit()` y `BalanceService::debit()` (saldo_change)
    - `WorkReportService::validate()` (work_report_validated)
    - `EvidenceService::upload()` y `EvidenceService::delete()` (evidence_uploaded/evidence_deleted)
  - Tests básicos comprobando que se crean logs
- Excluye:
  - Auditoría de login/logout (se hará al montar auth/UI)
  - UI de auditoría
  - Notificaciones

**Reglas**
1. `audit_logs` es append-only (no se edita).
2. Guardar: `event`, `actor_id`, `entity_type`, `entity_id`, `payload`, `ip`, `user_agent`, `created_at`.
3. Retención: objetivo >= 5 años (documentar; el borrado/archivado sería tarea futura).
4. La auditoría no debe romper la operación principal:
   - Si falla el log, no debe tumbar el negocio (se puede capturar y registrar warning en logs).
   - EXCEPCIÓN: si queréis auditoría “estricta”, se decide en `docs/DECISIONS.md`.

**Archivos a crear/editar**
- `src/database/migrations/*_create_audit_logs_table.php`
- `src/app/Models/AuditLog.php`
- `src/app/Services/AuditService.php`
- `src/app/Services/BalanceService.php`
- `src/app/Services/WorkReportService.php`
- `src/app/Services/EvidenceService.php`
- `src/tests/Feature/AuditLogsTest.php`

**Checklist de aceptación**
- [ ] Migración `audit_logs` ejecuta sin error.
- [ ] `AuditService::log()` crea registros con payload JSON.
- [ ] Al hacer `credit/debit` se crea audit log.
- [ ] Al validar parte se crea audit log.
- [ ] Al subir/borrar evidencia se crea audit log.
- [ ] Tests pasan: `php artisan test`

**Comandos de verificación**
- `php artisan migrate`
- `php artisan test`