## Tarea 003 — Validación descuenta saldo (BalanceService) + idempotencia fuerte

**Objetivo**  
Al validar un parte (`work_reports.validate()`), se debe:
1) Cambiar estado a `validated` (si procede)
2) Descontar saldo del cliente en segundos usando `BalanceService::debit()`
3) Registrar trazabilidad en `balance_movements` con referencia al `work_report`
4) Garantizar idempotencia: si se llama dos veces, NO debe descontar dos veces.

**Alcance**
- Incluye:
  - Integración de `WorkReportService::validate()` con `BalanceService`
  - Movimiento de saldo (debit) por el total de segundos del parte
  - Referencia del movimiento: `reference_type='WorkReport'`, `reference_id=<work_report_id>`
  - Tests para idempotencia y saldo insuficiente
- Excluye:
  - Auditoría global en `audit_logs` (tarea posterior)
  - Notificaciones (tarea posterior)
  - UI / rutas / controllers

**Reglas de negocio (obligatorias)**
1. El descuento se realiza **al validar** (no al finalizar).
2. El descuento usa `work_reports.total_seconds` (segundos).
3. El saldo no puede quedar negativo:
   - si no hay saldo suficiente, `validate()` debe fallar y NO cambiar el estado a validated.
4. Idempotencia fuerte:
   - Si el parte ya está `validated`, `validate()` debe devolver sin crear un nuevo `balance_movement`.
   - Si por cualquier motivo hay ya un movimiento en `balance_movements` con `reference_type='WorkReport'` y `reference_id=<id>`, NO crear otro.
5. Todo debe ejecutarse en transacción:
   - cambio de estado + creación evento `validate` + debit de saldo deben ser atómicos.

**Archivos a crear/editar**
- `src/app/Services/WorkReportService.php`
- `src/app/Services/BalanceService.php` (solo si es necesario para soporte de reference/metadata)
- `src/tests/Feature/WorkReportValidationBalanceTest.php` (nuevo)
- (Opcional) `src/database/migrations/*_add_unique_ref_to_balance_movements.php` (si se decide añadir constraint)

**Notas técnicas (recomendadas)**
- Para idempotencia sólida, se recomienda añadir un índice/constraint único:
  - UNIQUE (`reference_type`, `reference_id`, `reason`) o al menos (`reference_type`, `reference_id`)
  - Motivo: evitar doble cargo incluso en concurrencia.
- `reason` recomendado para el debit: `validation_work_report`
- `created_by` del movimiento: `validated_by` (técnico o admin según rol)
- Registrar en `work_report_events` el evento `validate` igual que en Tarea 002.

**Checklist de aceptación**
- [ ] Validar descuenta saldo correctamente (debit con amount_seconds negativo).
- [ ] Si saldo insuficiente: no valida (no cambia status) y no crea movimiento.
- [ ] Si se llama `validate()` dos veces: no duplica movimientos.
- [ ] Existe `balance_movements` con `reference_type='WorkReport'` y `reference_id` correcto.
- [ ] Tests pasan en Docker con MariaDB: `php artisan test`

**Comandos de verificación**
- `php artisan test`