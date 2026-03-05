## Tarea 014 — Admin: CRUD completo Clientes y Técnicos

**Objetivo**
Completar la lógica de administración para crear/actualizar/eliminar clientes y técnicos desde el panel admin.

**Alcance**
Incluye:
- CRUD completo de técnicos (usuarios role=technician)
- CRUD completo de clientes (usuarios role=client + entidad Client)
- Vinculación robusta Client.user_id (ya existe)
- Validaciones (email único, role correcto, etc.)
- Soft approach recomendado: "eliminar" = desactivar (is_active=false) si hay datos asociados

Excluye:
- Estilos/UX
- Importación CSV

**Reglas**
- Admin only (/admin/**)
- No borrar usuarios con actividad (partes, movimientos, evidencias): desactivar
- Si se crea cliente, se crea:
  - User (role=client)
  - Client (user_id del user)
  - ClientProfile si aplica en vuestro schema (balance_seconds inicial 0)

**Rutas**
- /admin/technicians (index/create/store/edit/update/destroy or deactivate)
- /admin/clients (index/create/store/edit/update/destroy or deactivate)

**Archivos**
Controllers:
- AdminTechnicianController
- AdminClientController (ampliar: create/store/edit/update/destroy)
Requests:
- StoreClientRequest / UpdateClientRequest
- StoreTechnicianRequest / UpdateTechnicianRequest
Vistas:
- Reusar las existentes; si faltan create/edit de clients/technicians, crear mínimo
Tests:
- AdminClientCrudTest
- AdminTechnicianCrudTest

**Checklist**
- Admin crea técnico y aparece en listado
- Admin edita técnico
- Admin “elimina” técnico (desactiva si tiene actividad)
- Admin crea cliente (user + client vinculado)
- Admin edita cliente
- Admin “elimina” cliente (desactiva si tiene actividad)
- Tests verdes