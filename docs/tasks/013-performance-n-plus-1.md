## Tarea 013 — Performance: evitar N+1 y optimizar listados/KPIs

**Objetivo**
Optimizar rendimiento en listados y dashboards para que el sistema aguante volumen:
- Evitar N+1 en Admin (clientes con saldo, partes globales, auditoría)
- Evitar N+1 en Técnico (listados y detalle)
- Evitar N+1 en Cliente (listado/detalle)
- Mejorar KPIs (contadores por estado) sin queries excesivas

**Alcance**
Incluye:
- Eager loading (`with`, `withCount`)
- Paginación consistente
- Selección de columnas (`select`) cuando aplique
- Ajustes de queries y relaciones
- Tests de regresión (funcional) + (opcional) asserts básicos de queries si el proyecto ya lo soporta

Excluye:
- Cache avanzada o Redis (tarea posterior)
- Index tuning a nivel de DB (salvo añadir índices obvios si faltan)

---

## Puntos a optimizar (prioridad)
1) Admin — Clientes index
- Si se calcula saldo cliente a cliente llamando a BalanceService, cambiar a:
  - usar `client_profiles.balance_seconds` (agregado) para listado
  - o una query agregada con SUM sobre ledger si fuera necesario (pero ya existe agregado)
- Eager load de `user` y `profile` (si aplica)

2) Admin — Work reports index/show
- `with(['client', 'technician'])` en listados
- `with(['events', 'evidences', 'client', 'technician'])` en detalle

3) Técnico — Work reports index/show
- `with(['client'])` en index
- `with(['events', 'evidences', 'client'])` en show

4) Cliente — Work reports index/show
- `with(['technician'])` en index
- `with(['events', 'evidences', 'technician'])` en show

5) Dashboards (admin/technician/client)
- Contadores por estado con `selectRaw`/`groupBy` o `withCount` sin cargar colecciones completas
- “últimos 5” con query paginada y relaciones

---

## Restricciones
- No cambiar UI/estilos.
- No romper tests existentes.
- Comentarios explicando el por qué (performance).

---

## Archivos a revisar/modificar (según necesidad)
Controllers:
- `src/app/Http/Controllers/Admin/AdminDashboardController.php`
- `src/app/Http/Controllers/Admin/AdminClientController.php`
- `src/app/Http/Controllers/Admin/AdminWorkReportController.php`
- `src/app/Http/Controllers/TechnicianDashboardController.php`
- `src/app/Http/Controllers/Technician/TechnicianWorkReportController.php`
- `src/app/Http/Controllers/ClientDashboardController.php`
- `src/app/Http/Controllers/Client/ClientWorkReportController.php`

Models (si hace falta para scopes/relations):
- `src/app/Models/Client.php`
- `src/app/Models/WorkReport.php`
- `src/app/Models/Evidence.php`

Tests:
- Mantener suite en verde (`php artisan test`)
- (Opcional) añadir 1–2 tests que aseguren que listados no hacen consultas absurdas si ya hay helpers de query counting

---

## Checklist de aceptación
- [ ] Listados principales usan eager loading y no hacen N+1 evidente
- [ ] KPIs no cargan colecciones completas innecesarias
- [ ] No cambia funcionalidad
- [ ] `php artisan test` pasa

**Comandos**
- `php artisan test`