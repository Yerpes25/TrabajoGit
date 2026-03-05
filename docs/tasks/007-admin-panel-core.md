## Tarea 007 — Panel Admin: usuarios (técnicos/clientes), bonos/saldo, partes globales y auditoría

**Objetivo**
Conectar las vistas del Admin (ya hechas) para que el rol `admin` pueda:
- Administrar usuarios: técnicos y clientes (crear/editar/desactivar)
- Administrar bonos/saldo: asignar tiempo (credit) y ver movimientos
- Visualizar partes de trabajo de todos los técnicos (filtros + detalle)
- Visualizar auditoría (audit_logs) con filtros básicos

**Alcance**
Incluye:
- Controllers finos + Requests + Routes
- Uso de Services existentes: `BalanceService`, `WorkReportService`, `EvidenceService`, `AuditService`
- Policies/Middleware de acceso (admin-only)
- Paginación y filtros básicos
- Mostrar datos en Blade (sin estilos)

Excluye:
- Diseño/estilos (solo estructura y datos)
- Ecommerce/Stripe/vouchers compra online (tarea futura)
- Notificaciones (tarea futura)
- Importación CSV/Excel (tarea futura)

---

## Reglas de negocio
1) Solo `admin` puede acceder a /admin/**
2) Gestión de usuarios:
   - roles válidos: `admin`, `technician`, `client`
   - `is_active=false` bloquea acceso
3) Gestión de saldo:
   - solo admin puede hacer `credit` manual (asignar tiempo)
   - saldo siempre en segundos (input en horas/minutos, convertir a segundos en backend)
4) Partes:
   - admin puede ver TODOS los `work_reports`
   - filtros por: cliente, técnico, estado, rango fechas
5) Auditoría:
   - admin puede consultar logs con filtros por: evento, actor, entidad, fechas

---

## Rutas (admin)
- GET  /admin                         -> AdminDashboardController@index (resumen KPIs básico)
- GET  /admin/users                   -> AdminUserController@index
- GET  /admin/users/create            -> AdminUserController@create
- POST /admin/users                   -> AdminUserController@store
- GET  /admin/users/{user}/edit       -> AdminUserController@edit
- PUT  /admin/users/{user}            -> AdminUserController@update
- POST /admin/users/{user}/toggle     -> AdminUserController@toggleActive

- GET  /admin/clients                 -> AdminClientController@index (solo role=client)
- GET  /admin/clients/{client}        -> AdminClientController@show (saldo + movimientos + partes del cliente)
- POST /admin/clients/{client}/credit -> AdminClientController@credit (asignar tiempo)

- GET  /admin/work-reports            -> AdminWorkReportController@index (listado global + filtros)
- GET  /admin/work-reports/{wr}       -> AdminWorkReportController@show (detalle + evidencias + eventos)

- GET  /admin/audit-logs              -> AdminAuditLogController@index (filtros + paginación)

---

## Inputs (Requests)
- Store/Update user:
  - name, email, password(opcional en update), role, is_active
- Credit saldo:
  - hours (int/float) o hh:mm (define un formato y valida)
  - reason (string opcional, default: "admin_credit")
  - metadata opcional (json)
  - IMPORTANTE: convertir siempre a `seconds` antes de llamar a BalanceService

---

## Integración con Services (obligatorio)
- Asignación saldo:
  - `BalanceService::credit($client, $seconds, $reason, reference_type, reference_id, created_by, metadata)`
  - reference recomendado:
    - reference_type = 'User' / 'Client' (decidir uno y mantener)
    - reference_id   = client_user_id

- Consulta saldo/movimientos:
  - `BalanceService::getBalanceSeconds($client)`
  - Query movimientos por client_id (paginado)

- Consulta partes:
  - Queries Eloquent paginadas con filtros (sin lógica de negocio)
  - Para detalle: cargar relaciones (`client`, `technician`, `events`, `evidences`)

- Auditoría:
  - Query paginada sobre `audit_logs` con filtros (event, actor_id, entity_type, entity_id, dates)

---

## Archivos a crear/editar
Controllers:
- `src/app/Http/Controllers/Admin/AdminDashboardController.php`
- `src/app/Http/Controllers/Admin/AdminUserController.php`
- `src/app/Http/Controllers/Admin/AdminClientController.php`
- `src/app/Http/Controllers/Admin/AdminWorkReportController.php`
- `src/app/Http/Controllers/Admin/AdminAuditLogController.php`

Requests:
- `src/app/Http/Requests/Admin/StoreUserRequest.php`
- `src/app/Http/Requests/Admin/UpdateUserRequest.php`
- `src/app/Http/Requests/Admin/CreditClientRequest.php`

Views:
- Reutilizar tus vistas existentes (no crear nuevas salvo que falten)
- Si faltan, crear placeholders mínimos en:
  - `src/resources/views/admin/...`

Routes:
- `src/routes/web.php` (grupo /admin con middleware `auth` + `role:admin`)

Tests:
- `src/tests/Feature/Admin/AdminAccessTest.php` (accesos 403/redirect)
- `src/tests/Feature/Admin/AdminCreditBalanceTest.php` (credit crea movimiento + audit log)
- `src/tests/Feature/Admin/AdminWorkReportsListTest.php` (filtros básicos)

---

## Checklist de aceptación
- [ ] Admin puede gestionar usuarios (create/edit/toggle active)
- [ ] Admin puede asignar saldo a un cliente (credit) y ver el movimiento reflejado
- [ ] Admin ve listado global de partes con filtros y entra a detalle
- [ ] Admin ve auditoría con filtros y paginación
- [ ] Rutas admin protegidas por rol
- [ ] Tests pasan: `php artisan test`

**Comandos**
- `php artisan test`