## Tarea 001 — Saldo por ledger (balance_movements) + agregado (client_profiles.balance_seconds)

**Objetivo**  
Implementar el sistema de saldo del cliente basado en movimientos en segundos usando **balance_movements** como fuente de verdad, y **client_profiles.balance_seconds** como agregado de rendimiento (opcional pero mantenido en la misma transacción).

**Alcance**
- Incluye:
  - Migraciones de `clients`, `client_profiles`, `balance_movements`
  - Modelos Eloquent y relaciones básicas
  - Service `BalanceService` (credit/debit/getBalance)
  - Tests mínimos de consistencia e integridad
- Excluye:
  - UI Blade
  - Notificaciones
  - Imports
  - Integración con work_reports (se hará en tarea posterior)

**Archivos a crear/editar**
- `src/database/migrations/*_create_clients_table.php`
- `src/database/migrations/*_create_client_profiles_table.php`
- `src/database/migrations/*_create_balance_movements_table.php`
- `src/app/Models/Client.php`
- `src/app/Models/ClientProfile.php`
- `src/app/Models/BalanceMovement.php`
- `src/app/Services/BalanceService.php`
- `src/tests/Feature/BalanceServiceTest.php`

**Reglas de negocio**
1. Tiempo y saldo se miden en **segundos**.
2. Saldo del cliente (fuente de verdad) = suma de `balance_movements.amount_seconds`.
3. `client_profiles.balance_seconds` es un agregado; debe mantenerse consistente en la misma transacción.
4. Los movimientos son **inmutables**: si hay corrección, se crea un movimiento compensatorio (no se edita el movimiento original).
5. No permitir saldo negativo: `debit()` debe fallar si no hay saldo suficiente.
6. Se debe poder trazar el origen del movimiento:
   - `reason` obligatorio (ej: bono, ajuste, validación_parte, importacion)
   - `reference_type/reference_id` opcional
   - `created_by` opcional

**Criterios de aceptación**
- [ ] `php artisan migrate` ejecuta sin error.
- [ ] Se puede crear un `Client` y su `ClientProfile`.
- [ ] `BalanceService::credit()`:
  - crea `balance_movements` con `amount_seconds` positivo
  - actualiza `client_profiles.balance_seconds`
- [ ] `BalanceService::debit()`:
  - valida saldo suficiente
  - crea `balance_movements` negativo
  - actualiza `client_profiles.balance_seconds`
- [ ] `BalanceService::getBalanceSeconds()`:
  - devuelve saldo correcto calculado desde ledger
- [ ] Test:
  - crédito + crédito + débito = saldo correcto
  - débito sin saldo suficiente falla (exception)
  - agregado (`client_profiles.balance_seconds`) coincide con el ledger

**Notas técnicas**
- Usar transacciones DB en credit/debit.
- Indexar `balance_movements.client_id` y `(client_id, created_at)`.
- No añadir UI en esta tarea.