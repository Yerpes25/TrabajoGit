# DECISIONS.md (ADR ligero)

Aquí se registran decisiones técnicas relevantes para que el equipo no repita debates.

Formato recomendado:
- Fecha
- Decisión
- Motivo
- Alternativas descartadas
- Impacto

---

## 2026-02-28 — Constraint único en balance_movements para idempotencia fuerte

**Decisión**: Añadir constraint único `(reference_type, reference_id, reason)` en la tabla `balance_movements`.

**Motivo**: 
- Garantizar idempotencia fuerte: evitar doble cargo por el mismo work_report incluso en condiciones de concurrencia
- Prevenir errores de aplicación que podrían crear movimientos duplicados
- Cumplir con la regla de negocio: "jamás doble cargo por el mismo work_report"

**Alternativas descartadas**:
- Solo verificación a nivel de aplicación: descartado porque no protege contra condiciones de carrera
- Índice único solo en `(reference_type, reference_id)`: descartado porque podría permitir múltiples movimientos con diferentes reasons para la misma referencia
- No añadir constraint: descartado porque la idempotencia es crítica para la integridad financiera

**Impacto**: 
- Protección a nivel de base de datos contra duplicados
- Si se intenta crear un movimiento duplicado, la base de datos lanzará excepción de integridad
- El código debe manejar esta excepción apropiadamente (ya implementado en WorkReportService)