## Tarea 006 — Auth robusto con Laravel Breeze (Blade) + roles + UI mínima por rol

**Objetivo**
Implementar un sistema de autenticación robusto y seguro con **Laravel Breeze (Blade)** y habilitar:
- Login / logout
- Middleware/policies por rol (`admin`, `technician`, `client`)
- Redirección post-login por rol a su dashboard
- UI mínima (Blade) con 3 dashboards

**Alcance**
- Incluye:
  - Instalación Breeze (Blade) en `src/`
  - Migración/ajuste de `users` para `role` e `is_active`
  - Seeders: crear 1 usuario por rol para pruebas
  - Middleware `role` o policies (mínimo: role middleware + gates)
  - Rutas protegidas por rol:
    - `/admin` (admin)
    - `/technician` (técnico)
    - `/client` (cliente)
  - Detección `is_active` (si false, bloquear login o logout inmediato)
  - Tests básicos de acceso (Feature)
- Excluye:
  - UI completa de gestión (solo dashboards)
  - Gestión avanzada de permisos por entidad (policies completas se harán en tareas posteriores)
  - Notificaciones push/email (tarea posterior)

**Reglas de negocio**
1. Roles válidos: `admin`, `technician`, `client`.
2. Un usuario `is_active=false` no debe poder usar el sistema.
3. Tras login, redirigir según rol:
   - admin -> `/admin`
   - technician -> `/technician`
   - client -> `/client`
4. Las rutas deben estar protegidas:
   - si un rol intenta acceder a otro dashboard -> 403 o redirect a su dashboard.
5. Mantener seguridad “Laravel standard” (CSRF, sesiones, hashing).

**Archivos a crear/editar**
- `src/composer.json` (Breeze)
- `src/routes/web.php`
- `src/app/Http/Middleware/RoleMiddleware.php` (si se usa middleware)
- `src/app/Providers/AppServiceProvider.php` (si se usan Gates)
- `src/app/Http/Controllers/*DashboardController.php` (mínimo o closures; preferible controllers finos)
- `src/resources/views/dashboard/*.blade.php` (admin/technician/client)
- `src/database/migrations/*_add_role_is_active_to_users_table.php` (si no existe)
- `src/database/seeders/*` (crear usuarios demo)
- `src/tests/Feature/AuthAccessTest.php`

**Notas técnicas (obligatorias)**
- Instalar Breeze **Blade** (no Inertia).
- Ejecutar build frontend (Vite):
  - `npm install`
  - `npm run build` (o `npm run dev` en local)
- Asegurar que docker tiene node/npm o usar un contenedor node (según vuestro setup actual).
- El middleware de rol debe ser simple:
  - `->middleware(['auth', 'role:admin'])`

**Checklist de aceptación**
- [ ] Login/Logout funcionando (Breeze).
- [ ] Usuarios tienen `role` e `is_active`.
- [ ] Seed crea 3 usuarios (admin/technician/client).
- [ ] Post-login redirect por rol correcto.
- [ ] `/admin`, `/technician`, `/client` funcionan y están protegidos.
- [ ] Tests de acceso pasan (`php artisan test`).
- [ ] Repo en verde.

**Comandos de verificación**
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan test`
- (Frontend) `npm run build`