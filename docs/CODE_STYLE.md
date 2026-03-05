# Estilo de código

## PHP/Laravel
- Nombres claros, sin abreviaturas raras.
- Services con métodos pequeños y testeables.
- Validación en FormRequest.
- Respetar PSR-12.

## Base de datos
- FK con `unsignedBigInteger`.
- Índices para consultas habituales.
- Ledger inmutable: si hay ajuste, se crea movimiento compensatorio.

## Errores
- Excepciones con mensajes útiles.
- No silenciar errores.