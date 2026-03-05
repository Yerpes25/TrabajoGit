# DB_SCHEMA — Modelo de datos (Punto 10)

Este documento define el esquema lógico de base de datos del sistema “Banco de Horas” y sus reglas de integridad.

## 1) Convenciones generales
- **Unidad de tiempo**: todos los cálculos y saldos se almacenan en **segundos** (`*_seconds`).
- **Claves**: `id` como `unsignedBigInteger` autoincremental.
- **Timestamps**: `created_at`, `updated_at` (Laravel).
- **Soft delete**: recomendable en entidades core (`deleted_at`) para auditoría (opcional según se acuerde).
- **Índices**: en claves de búsqueda (`client_id`, `technician_id`, `status`, `created_at`).

---

## 2) Tablas core

### 2.1 `users`
Usuarios del sistema (admin/técnico/cliente).

Campos recomendados:
- `id`
- `name`
- `email` (unique)
- `email_verified_at` (nullable)
- `password`
- `role` enum/string: `admin | technician | client` (index)
- `is_active` boolean (index)
- `last_login_at` (nullable)
- `remember_token` (nullable)
- `created_at`, `updated_at`

Relaciones:
- Un técnico (user) puede tener muchos `work_reports`.

---

### 2.2 `clients`
Entidad “cliente” a la que se le imputan partes y saldo.

Campos recomendados:
- `id`
- `name` (index)
- `legal_name` (nullable)
- `tax_id` (nullable, index)  *(si aplica para empresas)*
- `email` (nullable, index)
- `phone` (nullable)
- `address` (nullable)
- `notes` (nullable)
- `created_at`, `updated_at`

Relaciones:
- `clients (1) -> (1) client_profiles`
- `clients (1) -> (N) work_reports`
- `clients (1) -> (N) balance_movements`

---

### 2.3 `client_profiles`
Datos operativos del cliente y **saldo agregado** (optimización).

Campos recomendados:
- `id`
- `client_id` (FK unique -> clients.id)
- `balance_seconds` bigint (default 0)  **(saldo agregado)**
- `created_at`, `updated_at`

Regla:
- `balance_seconds` es un agregado de rendimiento.
- La fuente de verdad es `balance_movements`, y en operaciones críticas se actualiza **en la misma transacción**.

---

### 2.4 `balance_movements`  ✅ (ledger de saldo)
Movimientos de saldo (crédito/débito) en segundos. Fuente de verdad del saldo.

Campos recomendados:
- `id`
- `client_id` (FK -> clients.id, index)
- `amount_seconds` bigint  *(+ suma, - resta)*
- `type` enum/string: `credit | debit` (index) *(opcional si se deduce por signo)*
- `reason` string (index) *(ej: bono, ajuste, validación parte, importación)*
- `reference_type` string (nullable) *(ej: WorkReport)*
- `reference_id` bigint (nullable) *(id del parte u otro)*
- `created_by` (FK -> users.id, nullable) *(quién generó el movimiento)*
- `metadata` json (nullable) *(detalle extra)*
- `created_at`, `updated_at`

Índices recomendados:
- `(client_id, created_at)`
- `(client_id, reference_type, reference_id)` para búsquedas por parte
- `reason`

Reglas:
- El saldo **no puede quedar negativo** al realizar un `debit`.
- Los movimientos son **inmutables** (si hay corrección, se añade un movimiento compensatorio).
- Descuento principal: **al validar el parte**.

---

### 2.5 `work_reports`
Partes de trabajo realizados por técnicos para clientes.

Campos recomendados:
- `id`
- `client_id` (FK -> clients.id, index)
- `technician_id` (FK -> users.id, index) *(role=technician)*
- `title` (nullable) *(breve)*
- `description` (nullable) *(inicio/observaciones)*
- `summary` (nullable) *(lo realizado al finalizar/validar)*
- `status` enum/string (index): `in_progress | paused | finished | validated`
- `total_seconds` bigint (default 0) *(cache/derivado de eventos o cálculo)*
- `active_started_at` datetime (nullable) *(inicio del tramo activo actual)*
- `finished_at` datetime (nullable)
- `validated_at` datetime (nullable)
- `validated_by` (FK -> users.id, nullable) *(quién valida; según requisito: técnico)*
- `created_at`, `updated_at`

Reglas:
- Un técnico puede tener muchos partes, pero **solo 1** en `in_progress` (el resto `paused`).
- El tiempo en pausa no suma.
- El cliente ve partes `finished` y `validated` (según requisitos).

---

### 2.6 `work_report_events`
Detalle del cronómetro y trazabilidad de cambios del parte (incluye pausas).

Campos recomendados:
- `id`
- `work_report_id` (FK -> work_reports.id, index)
- `type` enum/string (index):
  - `start`, `pause`, `resume`, `finish`, `validate`, `edit`
- `occurred_at` datetime (index)
- `elapsed_seconds_after` bigint (default 0) *(segundos acumulados tras el evento)*
- `metadata` json (nullable) *(motivo pausa, cambios, diffs, etc.)*
- `created_by` (FK -> users.id, nullable)
- `created_at`, `updated_at`

Reglas:
- El cálculo de tiempo se basa en eventos (o en su agregado en `work_reports.total_seconds`).
- El evento `pause` marca fin de tramo; `resume` crea inicio de tramo.
- El tiempo en pausa **no** se suma, pero queda reflejado por eventos.

---

### 2.7 `evidences`
Archivos opcionales asociados a partes (almacenamiento S3).

Campos recomendados:
- `id`
- `work_report_id` (FK -> work_reports.id, index)
- `uploaded_by` (FK -> users.id, index) *(según requisito: solo técnico)*
- `storage_disk` string *(ej: s3)*
- `storage_path` string *(key/ruta en S3)*
- `original_name` string
- `mime_type` string (nullable)
- `size_bytes` bigint (nullable)
- `checksum` string (nullable) *(opcional)*
- `metadata` json (nullable)
- `created_at`, `updated_at`

Reglas:
- Evidencias opcionales.
- Sin límite práctico (S3).
- Permiso de subida: técnico.

---

### 2.8 `audit_logs`
Auditoría global para seguridad y trazabilidad (retención ≥ 5 años).

Campos recomendados:
- `id`
- `event` string (index) *(login, saldo_change, validate, delete, edit, etc.)*
- `actor_id` (FK -> users.id, nullable, index)
- `entity_type` string (nullable, index)
- `entity_id` bigint (nullable, index)
- `ip` string (nullable)
- `user_agent` string (nullable)
- `payload` json (nullable) *(detalles)*
- `created_at`

Reglas:
- Append-only (no se edita).
- Retención mínima 5 años.

---

## 3) Tablas de soporte

### 3.1 `imports`
Importación de datos (CSV/Excel) a la plataforma.

Campos recomendados:
- `id`
- `type` string (index) *(clients, balance, etc.)*
- `status` enum/string: `pending | processing | completed | failed`
- `file_name` string
- `stored_path` string (nullable)
- `total_rows` int (default 0)
- `success_rows` int (default 0)
- `failed_rows` int (default 0)
- `error_report` json/text (nullable)
- `created_by` (FK -> users.id, index)
- `created_at`, `updated_at`

---

### 3.2 `notifications`
Notificaciones (email/push), con historial y estado.

Campos recomendados:
- `id`
- `user_id` (FK -> users.id, index)
- `channel` enum/string (index): `email | push`
- `event` string (index)
- `title` string (nullable)
- `message` text
- `status` enum/string: `pending | sent | failed`
- `sent_at` datetime (nullable)
- `error` text (nullable)
- `metadata` json (nullable)
- `created_at`, `updated_at`

---

### 3.3 `purchases` (futuro / desactivado)
Compras desde plataforma (preparado para futuro).

Campos recomendados:
- `id`
- `client_id` (FK -> clients.id, index)
- `status` enum/string: `draft | pending_payment | paid | cancelled | refunded` *(ajustable)*
- `total_seconds` bigint (default 0) *(tiempo comprado)*
- `amount_money` decimal(10,2) (nullable) *(si en el futuro hay facturación)*
- `currency` string(3) (nullable)
- `metadata` json (nullable)
- `created_at`, `updated_at`

Reglas:
- No se activa en MVP, pero queda diseñado.

---

## 4) Reglas de integridad críticas (backend)
- **Saldo**: `balance_seconds` nunca negativo.
- **Validación idempotente**: si `work_reports.status = validated`, no volver a generar `balance_movements` por ese parte.
- **1 activo por técnico**: solo un `work_report` en `in_progress` por `technician_id`.
- **Cliente**: solo ve `work_reports` en `finished` o `validated`.

---