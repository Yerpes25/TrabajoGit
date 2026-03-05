# Arquitectura del sistema

## Objetivo
Diseñar una plataforma mantenible para gestionar bonos de tiempo (saldo en segundos) consumidos por partes de trabajo con cronómetro, estados, validación, auditoría y evidencias.

## Principios
- Convención Laravel primero.
- Lógica de negocio fuera de Controllers (Services).
- Auditoría obligatoria en eventos críticos.
- Tiempo en segundos.
- Saldo por cliente basado en ledger de movimientos.

## Capas
### UI (Blade)
- Vistas Blade para Admin/Técnico/Cliente.
- Sin lógica de negocio (solo presentación).

### Controllers
- Validan request (FormRequest), autorizan (Policies) y delegan en Services.

### Services (core)
- `WorkReportService`: flujo del parte (start/pause/resume/finish/validate).
- `CreditService`: saldo por ledger (add/consume/getBalance).
- `AuditService`: registra eventos relevantes.
- Futuro: `NotificationService`, `ImportService`.

### Policies & Middleware
- Policies para acceso por rol y pertenencia (cliente solo lo suyo; técnico solo lo suyo).

### Events/Listeners
- Eventos del dominio: `WorkReportStatusChanged`, `WorkReportValidated`, `CreditChanged`.
- Listeners: auditoría, notificaciones (email/push).

### Jobs/Queues
- Inicialmente: `QUEUE_CONNECTION=database`
- Futuro: Redis para colas y websocket (cuando se formalice).

## Feature flags (módulos apagados)
- Compras desde plataforma (preparado, OFF).
- Facturación (futuro, OFF).

## Puntos de calidad
- Idempotencia en validación (no doble descuento).
- Transacciones en operaciones que afecten saldo + estado.
- Tests mínimos para reglas core.