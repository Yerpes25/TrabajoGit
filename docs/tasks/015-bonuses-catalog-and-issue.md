## Tarea 015 — Bonos: catálogo + emisión a cliente (sin caducidad) + integración con saldo

**Objetivo**
Implementar “bonos” como entidad real para poder **crear / modificar / eliminar (archivar)** bonos, y permitir al admin **emitir/asignar** un bono a un cliente, generando automáticamente saldo (`balance_movements`) y actualizando el agregado (`client_profiles.balance_seconds`).

> Nota: El ledger (`balance_movements`) es inmutable. Los bonos no editan movimientos existentes: emitir un bono crea un `credit`.

---

## Alcance
Incluye:
- CRUD de catálogo de bonos
- Emisión/asignación interna de bonos a clientes (sin caducidad)
- Integración con `BalanceService::credit()`
- Trazabilidad mediante referencia `reference_type/reference_id`
- Integración con auditoría (si `AuditService` está disponible)
- Vistas mínimas (sin estilos) para gestionar bonos y emitir desde ficha de cliente
- Tests completos (CRUD + emisión)

Excluye:
- Compras online (`purchases`) y facturación
- Notificaciones
- Estilos/UI avanzada

---

## Modelo de datos (tablas nuevas)

### 1) `bonuses` (catálogo)
Campos:
- `id`
- `name` string (index)
- `description` text nullable
- `seconds_total` bigint (index)  *(tiempo del bono en segundos)*
- `is_active` boolean (default true, index)
- `created_at`, `updated_at`

Reglas:
- Los bonos con `is_active=false` se consideran archivados.
- Si un bono tiene emisiones (`bonus_issues`), no se permite borrado físico (solo archivar).

### 2) `bonus_issues` (emisiones/asignaciones)
Campos:
- `id`
- `bonus_id` FK -> bonuses.id (index)
- `client_id` FK -> clients.id (index)
- `issued_by` FK -> users.id (index) *(admin que emite)*
- `seconds_total` bigint *(snapshot del bono en el momento de emitir)*
- `note` text nullable
- `metadata` json nullable
- `created_at`, `updated_at`

Reglas:
- Sin caducidad.
- `seconds_total` se copia del bono (snapshot para histórico).
- Cada emisión debe crear un movimiento en `balance_movements` (credit) con referencia a `BonusIssue`.

---

## Integración con saldo (obligatoria)

Al emitir un bono:
1) Crear registro en `bonus_issues`
2) Ejecutar `BalanceService::credit()`:
   - `client` = cliente receptor
   - `amount_seconds` = `bonus_issues.seconds_total`
   - `reason` = `'bono'`
   - `reference_type` = `'BonusIssue'`
   - `reference_id` = `bonus_issues.id`
   - `created_by` = admin (issued_by)
   - `metadata` = incluir:
     - bonus_id, bonus_name, seconds_total, note (si hay), issued_by

Todo dentro de **transacción**.

---

## Rutas (Admin)
- CRUD catálogo bonos:
  - GET    `/admin/bonuses`
  - GET    `/admin/bonuses/create`
  - POST   `/admin/bonuses`
  - GET    `/admin/bonuses/{bonus}/edit`
  - PUT    `/admin/bonuses/{bonus}`
  - DELETE `/admin/bonuses/{bonus}` *(archiva si tiene issues, o elimina si no tiene)*

- Emisión de bono a cliente (desde ficha cliente):
  - POST `/admin/clients/{client}/bonuses/issue`

Middleware:
- `auth` + `role:admin`

---

## Pantallas mínimas (sin estilos)
1) Admin > Bonos
- Listado paginado
- Crear / Editar
- Archivar / Eliminar (según reglas)

2) Admin > Cliente (show)
- Bloque “Bonos emitidos”:
  - listado paginado de `bonus_issues` del cliente
- Formulario “Emitir bono”:
  - select de bono activo
  - note opcional
  - submit

---

## Archivos a crear/editar

Migraciones:
- `src/database/migrations/*_create_bonuses_table.php`
- `src/database/migrations/*_create_bonus_issues_table.php`

Modelos:
- `src/app/Models/Bonus.php`
- `src/app/Models/BonusIssue.php`

Controllers:
- `src/app/Http/Controllers/Admin/AdminBonusController.php`
- Emisión:
  - opción A: `src/app/Http/Controllers/Admin/AdminClientBonusIssueController.php`
  - opción B: añadir método `issueBonus()` en `AdminClientController`
  *(elige la opción más consistente con el repo y mantén controllers finos)*

FormRequests:
- `src/app/Http/Requests/Admin/StoreBonusRequest.php`
- `src/app/Http/Requests/Admin/UpdateBonusRequest.php`
- `src/app/Http/Requests/Admin/IssueBonusRequest.php`

Views:
- `src/resources/views/admin/bonuses/index.blade.php`
- `src/resources/views/admin/bonuses/create.blade.php`
- `src/resources/views/admin/bonuses/edit.blade.php`
- Actualizar: `src/resources/views/admin/clients/show.blade.php` (bloque bonos + formulario emitir)

Routes:
- `src/routes/web.php`

Tests:
- `src/tests/Feature/BonusCatalogCrudTest.php`
- `src/tests/Feature/BonusIssueCreatesCreditTest.php`
- (Opcional) `src/tests/Feature/Admin/BonusAccessTest.php`

---

## Reglas de borrado/archivado
- DELETE bonus:
  - Si NO tiene `bonus_issues`: permitir borrado físico.
  - Si TIENE `bonus_issues`: NO borrar; marcar `is_active=false` y devolver mensaje “archivado”.

---

## Checklist de aceptación
- [ ] Migraciones ejecutan sin error
- [ ] CRUD catálogo bonos funcionando
- [ ] Emitir bono desde cliente crea:
  - bonus_issue
  - balance_movement credit con reference_type/id correcto
  - actualiza client_profiles.balance_seconds
- [ ] Auditoría: registrar evento si `AuditService` existe
- [ ] Rutas protegidas admin-only
- [ ] Tests pasan: `php artisan test`

**Comandos**
- `php artisan migrate`
- `php artisan test`