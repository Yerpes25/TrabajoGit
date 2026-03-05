# Notificaciones

## Canales
- Email
- Push (websocket)

## Eventos candidatos
- Parte creado/asignado
- Parte finalizado
- Parte validado
- Saldo actualizado
- Comentario/edición relevante del parte

## Reglas
- Notificaciones deben ser desacopladas (Events/Listeners).
- Envíos asíncronos vía colas (database al inicio).
- Futuro: Redis + workers dedicados.

## Preferencias (futuro)
- Activar/desactivar por usuario
- Frecuencia/resumen