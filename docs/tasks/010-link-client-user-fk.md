## Tarea 010 — Robustez: vincular Client <-> User por FK (eliminar dependencia de email)

**Objetivo**
Eliminar la relación implícita por email entre `users` (rol client) y `clients`, y establecer una relación robusta por FK.

**Problema actual**
El portal de cliente resuelve qué `Client` le corresponde al usuario por email.
- Si el email cambia, se rompe.
- No hay integridad referencial.

**Solución**
Añadir `user_id` a `clients` (o a `client_profiles` si el schema lo prefiere) como FK a `users.id`.

> Usar el nombre final según `docs/DB_SCHEMA.md`. Si en vuestro schema el owner real es `clients`, se hace ahí.

---

## Alcance
Incluye:
- Migración para añadir `clients.user_id` (nullable al principio) + índice + FK
- Backfill de datos existentes (mapeo por email actual)
- Restricciones:
  - asegurar que solo usuarios role=client puedan enlazarse
- Actualizar modelos y relaciones Eloquent
- Actualizar controllers del portal cliente para usar FK
- Actualizar admin (creación de cliente/usuario) si aplica
- Tests de regresión (cliente ve sus partes, etc.)

Excluye:
- UI de “cambiar asociación cliente-usuario” (tarea posterior si se necesita)

---

## Pasos técnicos (obligatorios)
1) **Migración 1**: añadir `user_id` nullable + índice + FK
2) **Backfill**:
   - Para cada `client` buscar `users.email == clients.email` (o el campo equivalente)
   - Setear `clients.user_id`
3) **Migración 2 (opcional pero recomendada)**:
   - si todos tienen user_id, convertir a NOT NULL
4) Actualizar código:
   - `User` hasOne `Client`
   - `Client` belongsTo `User`
   - Portal cliente: resolver `auth()->user()->client` (no por email)
5) Tests:
   - Nuevo test: cambiar email del user no afecta relación
   - Mantener tests existentes de Client portal.

---

## Archivos a crear/editar
- `src/database/migrations/*_add_user_id_to_clients_table.php`
- `src/app/Models/User.php`
- `src/app/Models/Client.php`
- `src/app/Http/Controllers/ClientDashboardController.php`
- `src/app/Http/Controllers/Client/ClientWorkReportController.php`
- Tests:
  - `src/tests/Feature/Client/ClientUserLinkTest.php` (nuevo)
  - Ajustes a `ClientWorkReportsViewTest` si fuese necesario

---

## Checklist de aceptación
- [ ] `clients.user_id` existe con FK e índice
- [ ] Backfill ejecuta sin error (en dev)
- [ ] El portal cliente funciona por FK (no email)
- [ ] Cambiar email del user NO rompe el portal cliente
- [ ] Tests pasan: `php artisan test`