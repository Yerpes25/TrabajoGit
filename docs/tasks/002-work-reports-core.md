## Tarea 002 — Partes de trabajo (work_reports) + eventos de cronómetro (work_report_events) + regla “1 activo”

**Objetivo**  
Implementar el núcleo de partes de trabajo y cronómetro mediante:
- Tabla `work_reports` (parte)
- Tabla `work_report_events` (eventos del cronómetro y trazabilidad)
- Service `WorkReportService` con operaciones: start/pause/resume/finish/validate
- Regla core: un técnico solo puede tener **1** parte en `in_progress` (activo)

**Alcance**
- Incluye:
  - Migraciones `work_reports` y `work_report_events` según `docs/DB_SCHEMA.md`
  - Modelos `WorkReport` y `WorkReportEvent` + relaciones
  - `WorkReportService` con reglas core (sin UI)
  - Tests mínimos de reglas de negocio (Feature tests)
  - Auditoría mínima: registrar en `work_report_events` cada cambio de estado y tiempo
- Excluye:
  - Integración con saldo (`balance_movements`) → se hará en Tarea 003 (descuento al validar)
  - `audit_logs` global (se hará en tarea posterior)
  - Evidencias (`evidences`) y S3 (tarea posterior)
  - Rutas / controllers / UI (tarea posterior)

**Tablas / Nombres obligatorios**
- `work_reports`
- `work_report_events`
Usar EXACTAMENTE esos nombres y los campos definidos en `docs/DB_SCHEMA.md`.

**Reglas de negocio (obligatorias)**
1. Estados del parte (`work_reports.status`):
   - `in_progress`, `paused`, `finished`, `validated`
2. Un técnico puede tener muchos partes, pero **solo 1** puede estar en `in_progress`.
   - Intentar iniciar/reanudar otro parte debe fallar con excepción clara (no auto-pausar en esta tarea).
3. Tiempo en segundos:
   - El tiempo se acumula mediante eventos `start/pause/resume/finish`.
   - Pausas **no** suman.
4. El cronómetro se detiene al pausar.
5. `finish`:
   - Cambia estado a `finished`
   - Cierra el tramo activo si existía y deja `total_seconds` actualizado
6. `validate`:
   - Cambia estado a `validated`
   - Registra evento `validate`
   - NO descuenta saldo aún (eso es Tarea 003)
7. Idempotencia de eventos:
   - No permitir `validate` si ya está `validated`
   - No permitir `resume` si está `in_progress`
   - No permitir `pause` si no está `in_progress`

**Archivos a crear/editar**
- `src/database/migrations/*_create_work_reports_table.php`
- `src/database/migrations/*_create_work_report_events_table.php`
- `src/app/Models/WorkReport.php`
- `src/app/Models/WorkReportEvent.php`
- `src/app/Services/WorkReportService.php`
- `src/tests/Feature/WorkReportServiceTest.php`

**Notas de implementación (recomendación)**
- Mantener `work_reports.total_seconds` como cache:
  - al pausar/finish calcular `delta_seconds = now - active_started_at` y sumarlo
  - al resume set `active_started_at = now`
- Registrar siempre eventos en `work_report_events` con:
  - `type`, `occurred_at`, `elapsed_seconds_after`, `metadata`, `created_by`
- Usar transacciones en operaciones que cambien estado + creen evento + actualicen total.

**Checklist de aceptación**
- [ ] Migraciones ejecutan sin error.
- [ ] Se puede crear un work_report para un client y technician.
- [ ] `WorkReportService::start()`:
  - set status `in_progress`
  - set `active_started_at`
  - crea evento `start`
- [ ] `pause()`:
  - solo si estaba `in_progress`
  - suma delta a `total_seconds`, limpia `active_started_at`
  - status `paused`
  - crea evento `pause`
- [ ] `resume()`:
  - solo si estaba `paused`
  - verifica regla “solo 1 activo”
  - status `in_progress`, set `active_started_at`
  - crea evento `resume`
- [ ] `finish()`:
  - cierra tramo si estaba activo
  - status `finished`, set `finished_at`
  - crea evento `finish`
- [ ] `validate()`:
  - solo si estaba `finished`
  - status `validated`, set `validated_at` y `validated_by`
  - crea evento `validate`
- [ ] Regla “solo 1 activo por técnico” verificada con test.
- [ ] Tests pasan en Docker con MariaDB: `php artisan test`

**Comandos de verificación**
- `php artisan migrate`
- `php artisan test`