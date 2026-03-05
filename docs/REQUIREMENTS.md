# Requisitos — Gestión de Bonos (Banco de Horas)

## 1. Objetivo del sistema
El sistema permite gestionar clientes con **bonos de tiempo** (saldo en segundos) consumidos mediante **partes de trabajo** realizados por técnicos. El sistema centraliza el registro de trabajos, el control de tiempo y saldo, la adjunción de evidencias opcionales, el historial completo y la auditoría de actividad durante al menos 5 años.

## 2. Roles y permisos
Roles existentes:
- **Admin**
- **Técnico**
- **Cliente**

Permisos (alto nivel):
- **Admin**
  - Gestiona clientes, bonos, saldos.
  - Acceso completo a partes.
  - Ajustes manuales de saldo (si se habilita).
  - Acceso a auditoría completa.
- **Técnico**
  - Crea partes asociados a un cliente.
  - Controla cronómetro (iniciar/pausar/reanudar/finalizar).
  - **Valida** el parte (según requisito: la validación la realiza el técnico).
  - Adjunta evidencias (opcional).
- **Cliente**
  - Consulta su saldo.
  - Consulta sus partes **Finalizados o Validados**.

> Nota: si en el futuro se decide que la validación la hace Admin, se documentará como cambio en DECISIONS/ADR.

## 3. Bonos y saldo
- Se venden/gestionan **bonos por tiempo**.
- El saldo se gestiona con precisión en **segundos**.
- Los bonos/saldo de un cliente están en la plataforma y el crédito puede gastarse en cualquier trabajo.
- Cualquier técnico puede realizar el trabajo para un cliente (no hay “bono por técnico”).
- **No hay caducidad del bono**.

Modelo recomendado:
- Saldo como **ledger de movimientos**: créditos (+) y consumos (-) con trazabilidad.
- Agregado opcional: `balance_seconds` en perfil/cliente para rendimiento.

## 4. Partes de trabajo
### 4.1 Estados
Estados definidos:
- **En curso** (`in_progress`)
- **Pausado** (`paused`)
- **Finalizado** (`finished`)
- **Validado** (`validated`)

### 4.2 Reglas de cronómetro
- El cronómetro calcula tiempo por contador guardando **segundos**.
- El tiempo en pausa **no cuenta**.
- El tiempo en pausa se guarda como información para el detalle del parte.
- Si el técnico “se desconecta / cierra navegador”, el cronómetro **se detiene** (no continúa sumando).

### 4.3 Regla: 1 parte activo por técnico
- Un técnico puede tener muchos partes en curso, pero solo **uno activo**.
- Todos los demás deben estar pausados.
- El cronómetro se detiene al pausar.

### 4.4 Finalización y validación
- Al finalizar el parte: se registra la finalización.
- Al validar: se registra lo desarrollado/realizado por el técnico.
- El saldo se descuenta **al validar la finalización del parte**.

### 4.5 Edición
- El parte se debe poder **editar**.
- Las ediciones deben quedar auditadas.

## 5. Evidencias
- Las evidencias no son obligatorias.
- Deben poder guardarse en **cualquier formato**.
- No hay límite práctico de evidencias: almacenamiento en **Amazon S3**.
- Solo el **técnico** sube evidencias (según requisito).

## 6. Visibilidad para cliente
- El cliente ve todos los partes **finalizados o validados** realizados para él.

## 7. Notificaciones
- Implementar notificaciones por:
  - **Push** (websocket)
  - **Email**

Eventos típicos:
- cambio de estado de parte
- validación
- saldo actualizado

## 8. Auditoría y retención
- Registrar eventos importantes:
  - login/logout
  - cambios de saldo
  - validaciones
  - borrados
  - ediciones de partes
- Retención mínima: **5 años**.

## 9. Importación de datos
- No se requiere exportación.
- Sí se requiere **importar** datos (Excel/CSV).

## 10. Infraestructura y entornos
- VPS propio.
- Entornos deseados:
  - local
  - staging (preproducción)
  - producción
- Backups diarios:
  - inicio: NAS
  - evolución: S3
- Monitorización deseada.

## 11. Roadmap / preparado para futuro (desactivado por ahora)
- Facturación (futuro)
- Compras desde la plataforma con estados de compra
- Sin caducidad de bonos (se mantiene)

## 12. Tecnologías
- Laravel última versión + Blade.
- BBDD: MariaDB/MySQL.
- Websocket para notificaciones push.
- Control de versiones en GitHub con ramas por implantación.
- Redis/colas: no conocido; se documentará cuando se introduzca (inicialmente colas en DB).