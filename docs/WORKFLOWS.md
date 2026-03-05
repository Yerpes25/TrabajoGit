# Flujos del sistema

## Flujo: Crear y trabajar un parte (técnico)
1. Técnico crea parte (cliente + descripción inicial)
2. Inicia cronómetro (estado in_progress)
3. Puede pausar (estado paused) -> se cierra el intervalo de tiempo
4. Puede reanudar -> nuevo intervalo
5. Finaliza (estado finished) -> se cierra intervalo si estaba activo

Regla: solo 1 parte activo por técnico.

## Flujo: Validación y descuento
1. Parte en finished
2. Técnico valida (o Admin si se cambia)
3. Se descuenta saldo en ledger (movimiento negativo)
4. Estado pasa a validated
5. Se registra auditoría y se notifican interesados

Regla: validación idempotente (no doble descuento).

## Flujo: Cliente consulta
- Cliente ve sus partes finalizados/validados y el detalle.
- No ve partes en curso/pausados (según requisito).