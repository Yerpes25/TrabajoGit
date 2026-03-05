## Tarea 003.1 — Hardening de validate(): reparación + concurrencia (unique key)

**Objetivo**  
Perfeccionar `WorkReportService::validate()` para:
1) Corregir el flujo cuando un parte está `validated` pero no tiene movimiento en `balance_movements` (caso raro pero posible).
2) Manejar condiciones de concurrencia donde dos validaciones simultáneas causen violación del índice único, tratándolo como idempotencia (sin romper).

**Alcance**
- Incluye:
  - Ajuste del flujo de validación para soportar “reparación” de `validated` sin movimiento.
  - Manejo de excepción por duplicate key al crear el movimiento (índice único).
  - Tests nuevos específicos (reparación + concurrencia simulada).
- Excluye:
  - UI, rutas, controllers
  - auditoría global en `audit_logs`

**Reglas obligatorias**
1) Validación normal:
   - Solo se valida desde `finished` (igual que ahora).
2) Reparación:
   - Si `work_report.status = validated` y NO existe `balance_movement` de validación:
     - Se permite crear el movimiento **sin exigir** estado `finished`.
     - Debe dejar `validated_at` y `validated_by` coherentes (si ya existen, se mantienen).
     - Debe crear evento `validate` solo si no existe ya (no duplicar eventos).
3) Concurrencia:
   - Si durante el debit se produce error de clave duplicada (`balance_movements_reference_unique`),
     se debe considerar como operación idempotente:
     - Recargar el parte desde BD y devolverlo sin error de negocio.
4) Transacción:
   - Mantener transacción atómica.

**Archivos a modificar/crear**
- `src/app/Services/WorkReportService.php`
- `src/tests/Feature/WorkReportValidationBalanceHardeningTest.php` (nuevo)

**Implementación recomendada**
- Definir un flag booleano: `$isRepair = $workReport->isValidated() && ! $existingMovement;`
- Ajustar validación de estado:
  - si `$isRepair` permitir continuar
  - si no, exigir `finished`
- En el bloque de transacción:
  - envolver el `BalanceService::debit()` con try/catch:
    - si error duplicate key => tratar como idempotente (buscar movimiento existente y continuar)
- No duplicar evento `validate`:
  - si ya existe en `work_report_events` un evento `validate` para ese parte, no crear otro.

**Checklist de aceptación**
- [ ] Caso normal sigue funcionando (tests existentes pasan).
- [ ] Caso reparación: parte validated sin movimiento => crea movimiento y no falla por estado.
- [ ] Caso duplicate key: si se fuerza condición, validate() no rompe (retorna work_report validado).
- [ ] No se duplican eventos `validate` en reparación o reintento.
- [ ] `php artisan test` pasa en Docker con MariaDB.

**Comandos de verificación**
- `php artisan test`