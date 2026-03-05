## Tarea 001.1 — Tests ejecutables en Docker con MariaDB (sin SQLite) + revertir decisión SQLite

**Objetivo**  
Dejar los tests de la Tarea 001 ejecutándose correctamente en el entorno Docker actual usando **MariaDB**, y eliminar la dependencia de SQLite. Rechazar/retirar la decisión “Requisito de extensión SQLite para tests” en `docs/DECISIONS.md`.

**Alcance**
- Incluye:
  - Configurar el entorno de tests para usar MariaDB del `docker-compose` (servicio `db`).
  - Crear una base de datos de test (ej: `gestion_bonos_test`) y asegurarse de que existe.
  - Ajustar configuración de tests (`phpunit.xml` o `.env.testing`) para apuntar a MariaDB.
  - Asegurar que `php artisan test` pasa dentro del contenedor `app`.
  - Revertir/editar `docs/DECISIONS.md` eliminando la “decisión” de SQLite.
  - Revisar el cambio en `src/tests/TestCase.php`: revertirlo si no es estrictamente necesario.
- Excluye:
  - Cambios funcionales del BalanceService (salvo lo imprescindible para tests).
  - UI, rutas, controllers.

**Archivos a crear/editar**
- (Preferido) `src/.env.testing` (crear si no existe)
- `src/phpunit.xml` (solo si es necesario)
- `docker-compose.yml` (solo si es necesario para crear DB test)
- `docs/DECISIONS.md` (eliminar entrada SQLite)
- `src/tests/TestCase.php` (revertir si no es necesario)

**Reglas**
- Tests deben ejecutarse en Docker sin pasos manuales extra (más allá de `docker compose up -d`).
- La BD de tests NO debe ser la misma que desarrollo.
- Mantener compatibilidad con Laravel 11.
- No introducir nuevas decisiones no aprobadas.

**Checklist de aceptación**
- [ ] Existe una BD de tests (ej: `gestion_bonos_test`) y el usuario tiene permisos.
- [ ] `php artisan test` pasa dentro del contenedor `app` usando MariaDB.
- [ ] No se requiere SQLite ni extensiones adicionales.
- [ ] `docs/DECISIONS.md` no contiene la entrada de SQLite.
- [ ] `src/tests/TestCase.php` solo cambia si es estrictamente necesario y está justificado (si no, revertir).

**Comandos de verificación**
Ejecutar dentro de `src`:
- `php artisan test`
- `php artisan migrate:fresh --env=testing` (si aplica)