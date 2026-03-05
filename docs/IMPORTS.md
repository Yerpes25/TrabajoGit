# Importación (CSV/Excel)

## Objetivo
Permitir importar datos a la plataforma (sin exportar por ahora).

## Formatos
- CSV (recomendado)
- Excel (XLSX) como alternativa

## Alcance inicial
- Importar clientes
- Importar saldos/bonos (como créditos en ledger)

## Reglas
- Importación debe ser auditable (quién, cuándo, qué se importó).
- Si hay errores: reportar filas fallidas y motivo.
- No duplicar clientes: usar un identificador (email, CIF/NIF, etc.) definido en requisitos.

## Resultado esperado
- Clientes creados/actualizados
- Movimientos de saldo creados en `credit_ledger`