## Tarea 009 — Cliente: Portal de consulta (partes + evidencias)

**Objetivo**
Conectar las vistas del rol `client` para que el cliente pueda:
- Ver un dashboard con resumen de sus partes
- Ver listado de partes asociadas a su cuenta (solo `finished` y `validated`)
- Ver detalle de cada parte con:
  - información principal
  - eventos del cronómetro
  - evidencias con enlaces de descarga
- (Opcional) Ver su saldo actual (solo lectura)

**Alcance**
Incluye:
- Rutas `/client/**` protegidas (`auth` + `role:client`)
- Controllers finos + FormRequests si hicieran falta
- Uso de Services existentes:
  - `BalanceService` (solo lectura de saldo si se muestra)
  - `EvidenceService` (URLs)
- Mostrar datos en Blade (sin estilos)

Excluye:
- Validación/cambios por parte del cliente
- Notificaciones
- Estilos/diseño
- Exportaciones

---

## Reglas de negocio (obligatorias)
1) El cliente SOLO ve sus partes (`work_reports.client_id = client_id asociado al usuario`).
2) El cliente SOLO ve estados:
   - `finished` y `validated`
   (no debe ver `in_progress` ni `paused`)
3) El cliente NO puede editar ni crear partes.
4) Evidencias: el cliente puede descargarlas/visualizarlas si existen.

---

## Rutas mínimas (cliente)
- GET /client                         -> ClientDashboardController@index
- GET /client/work-reports            -> ClientWorkReportController@index
- GET /client/work-reports/{wr}       -> ClientWorkReportController@show

---

## Datos a mostrar
Dashboard:
- contadores por estado (finished / validated)
- últimos 5 partes
- (opcional) saldo actual (hh:mm o en horas)

Listado:
- id, título, técnico, estado, total_seconds (hh:mm:ss), fechas

Detalle:
- info del parte
- eventos (work_report_events)
- evidencias con URL (EvidenceService o Storage::url)
- si está validated, mostrar quién validó y cuándo

---

## Archivos a crear/editar
Controllers:
- `src/app/Http/Controllers/ClientDashboardController.php` (si no existe, crear)
- `src/app/Http/Controllers/Client/ClientWorkReportController.php`

Views:
- Reusar vistas existentes del cliente.
- Si faltan:
  - `resources/views/dashboard/client.blade.php` (ya existe)
  - `resources/views/client/work-reports/index.blade.php`
  - `resources/views/client/work-reports/show.blade.php`

Routes:
- `src/routes/web.php` (grupo /client con middleware `auth` + `role:client`)

Tests:
- `src/tests/Feature/Client/ClientAccessTest.php`
- `src/tests/Feature/Client/ClientWorkReportsViewTest.php`

---

## Checklist de aceptación
- [ ] Cliente solo ve sus partes (y solo finished/validated)
- [ ] Cliente puede ver listado y detalle
- [ ] Cliente ve evidencias con enlace
- [ ] Rutas protegidas por rol
- [ ] Tests pasan: `php artisan test`

**Comandos**
- `php artisan test`