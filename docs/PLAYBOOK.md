# Playbook de implementación (para agentes)

Este documento define el orden de implementación para minimizar retrabajo y asegurar coherencia.

## Fase 0 — Base del repo
1. Verificar Docker funciona y Laravel responde.
2. Asegurar permisos en `src/storage` y `src/bootstrap/cache`.
3. Confirmar configuración `.env` (DB host `db`, puerto 3306).

## Fase 1 — Auth + Roles (MVP)
Objetivo: poder entrar como admin/técnico/cliente.
1. Migración: añadir `role` e `is_active` a `users`.
2. Seed: crear `admin@local.test`, `tech@local.test`, `client@local.test`.
3. Middleware: `role` (o policies) para proteger rutas.
4. (Opcional recomendado) Breeze Blade para login.

**Criterio de aceptación**
- Login funciona.
- Se puede acceder a rutas protegidas por rol.

## Fase 2 — Modelo de datos core (sin UI compleja)
Objetivo: soportar saldo por ledger y partes con timer.

### Tablas mínimas
1. `clients`
2. `bonuses` (histórico de compras/bonos, aunque consumo sea bolsa global)
3. `credit_ledger` (movimientos + y - en segundos)
4. `work_reports`
5. `time_entries` (intervalos start/end; la pausa no suma)
6. `attachments` (evidencias)
7. `audit_logs`

### Reglas a implementar
- saldo = suma `credit_ledger.amount_seconds` (no negativo al consumir)
- 1 parte activo por técnico (backend)
- `validate` descuenta saldo y crea auditoría
- edición de parte crea auditoría

**Criterio de aceptación**
- Se puede crear un cliente.
- Se puede añadir crédito.
- Se puede crear un parte, iniciar/pausar/reanudar/finalizar/validar.
- Validar descuenta saldo y crea movimiento.
- Cliente solo ve sus partes finalizados/validados.

## Fase 3 — Services (obligatorio)
Crear:
- `WorkReportService`
- `CreditService`
- `AuditService`

Los controllers solo llaman a estos services.

## Fase 4 — Policies y seguridad
- Policies por rol y pertenencia.
- Cliente no puede ver partes no permitidos.

## Fase 5 — UI Blade mínima
- Panel Técnico: lista de partes + botones start/pause/resume/finish/validate
- Panel Cliente: lista de partes finalizados/validados + detalle
- Panel Admin: clientes + saldo + auditoría

## Fase 6 — Notificaciones
- Events al cambiar estado y al validar.
- Envío email y push (websocket).
- Inicialmente: cola en DB.

## Fase 7 — Importación CSV/Excel
- Import de clientes y/o saldo.
- Auditoría de importación.

## Fase 8 — Preparar “compras/facturación” desactivado
- Estructura de tablas/estados y feature flag OFF.