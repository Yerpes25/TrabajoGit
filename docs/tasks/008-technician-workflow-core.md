## Tarea 008 — Técnico: Partes + Cronómetro + Evidencias (workflow core)

**Objetivo**
Conectar las vistas del rol `technician` para que pueda:
- Ver su dashboard con sus partes (en curso/pausadas/finalizadas/validadas)
- Crear un parte de trabajo
- Iniciar / Pausar / Reanudar / Finalizar un parte (cronómetro)
- Subir y borrar evidencias de un parte
- Ver detalle del parte con eventos y evidencias

**Alcance**
Incluye:
- Rutas `/technician/**` protegidas (`auth` + `role:technician`)
- Controllers finos + FormRequests
- Uso de Services existentes:
  - `WorkReportService` (create/start/pause/resume/finish)
  - `EvidenceService` (upload/delete/list)
- Mostrar datos en Blade (sin estilos)

Excluye:
- Validación del parte (eso lo hace admin o el flujo de validación se define en tarea posterior si el técnico valida)
- Notificaciones
- Importación CSV/Excel
- Estilos/diseño

---

## Reglas de negocio (obligatorias)
1) El técnico SOLO ve sus partes (`technician_id = auth()->id()`).
2) Un técnico puede tener muchos partes, pero SOLO 1 en `in_progress` (ya lo aplica el Service).
3) Evidencias son opcionales.
4) El técnico puede editar el parte (requisito): por ahora **solo** campos básicos:
   - `title`, `description` (y/o los campos definidos en DB_SCHEMA)
   - NO se permite cambiar tiempos manualmente.
5) Estados:
   - `in_progress`, `paused`, `finished`, `validated`
6) Acciones permitidas por estado:
   - start: si está `paused` o inicial (según vuestro modelo)
   - pause: solo si `in_progress`
   - resume: solo si `paused`
   - finish: si `in_progress` o `paused`

---

## Rutas mínimas (técnico)
- GET  /technician                       -> TechnicianDashboardController@index
- GET  /technician/work-reports          -> TechnicianWorkReportController@index
- GET  /technician/work-reports/create   -> TechnicianWorkReportController@create
- POST /technician/work-reports          -> TechnicianWorkReportController@store
- GET  /technician/work-reports/{wr}     -> TechnicianWorkReportController@show
- GET  /technician/work-reports/{wr}/edit-> TechnicianWorkReportController@edit
- PUT  /technician/work-reports/{wr}     -> TechnicianWorkReportController@update

Acciones cronómetro:
- POST /technician/work-reports/{wr}/start
- POST /technician/work-reports/{wr}/pause
- POST /technician/work-reports/{wr}/resume
- POST /technician/work-reports/{wr}/finish

Evidencias:
- POST   /technician/work-reports/{wr}/evidences        -> upload
- DELETE /technician/evidences/{evidence}               -> delete

---

## Datos que deben mostrarse en vistas
- Listado: id, título, cliente, estado, total_seconds (formateado hh:mm:ss), fechas relevantes
- Detalle parte:
  - datos principales
  - eventos (work_report_events) ordenados por fecha
  - evidencias con link (EvidenceService::getUrl si existe)
  - acciones según estado (botones start/pause/resume/finish)
  - formulario upload evidencia (archivo + metadata opcional)

---

## Archivos a crear/editar
Controllers:
- `src/app/Http/Controllers/TechnicianDashboardController.php` (si no existe, crear)
- `src/app/Http/Controllers/Technician/TechnicianWorkReportController.php`
- `src/app/Http/Controllers/Technician/TechnicianEvidenceController.php`

Requests:
- `src/app/Http/Requests/Technician/StoreWorkReportRequest.php`
- `src/app/Http/Requests/Technician/UpdateWorkReportRequest.php`
- `src/app/Http/Requests/Technician/UploadEvidenceRequest.php`

Views:
- Reusar tus vistas existentes del técnico.
- Si faltan:
  - `resources/views/dashboard/technician.blade.php` (ya existe)
  - `resources/views/technician/work-reports/index.blade.php`
  - `resources/views/technician/work-reports/create.blade.php`
  - `resources/views/technician/work-reports/edit.blade.php`
  - `resources/views/technician/work-reports/show.blade.php`

Routes:
- `src/routes/web.php` (grupo /technician con middleware `auth` + `role:technician`)

Tests:
- `src/tests/Feature/Technician/TechnicianAccessTest.php` (acceso)
- `src/tests/Feature/Technician/TechnicianWorkReportFlowTest.php` (create + start/pause/resume/finish)
- `src/tests/Feature/Technician/TechnicianEvidenceFlowTest.php` (upload/delete evidencia)

---

## Checklist de aceptación
- [ ] Técnico solo ve sus partes
- [ ] Técnico crea parte y la ve en listado
- [ ] Técnico puede start/pause/resume/finish respetando estados
- [ ] Regla 1 activo se cumple (si intenta otro start/resume, error claro)
- [ ] Técnico edita campos básicos del parte
- [ ] Técnico sube y borra evidencias
- [ ] Todas las rutas protegidas por rol
- [ ] `php artisan test` pasa

**Comandos**
- `php artisan test`