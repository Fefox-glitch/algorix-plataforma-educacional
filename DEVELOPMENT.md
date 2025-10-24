# Desarrollo y Organización del Proyecto

Este documento resume cómo ejecutar el proyecto en local, la estructura vigente y decisiones de organización para evitar duplicaciones.

## Ejecución en local
- Requisitos: PHP 8+, extensiones `curl`, `openssl`.
- Variables de entorno: definir `SUPABASE_URL` y `SUPABASE_KEY` en `.env`.
- Servidor de desarrollo: `php -S localhost:8000 router.php`.
- Entrada única: `router.php` delega siempre en `index.php`.

## Estructura vigente
- `index.php`: enrutamiento principal de vistas y endpoints API.
- `init.php`: bootstrap de sesión, helpers `isAuthenticated()` y `hasRole()`.
- `src/Controllers/*`: controladores MVC (Auth, Admin, StartCodes).
- `src/Core/Security.php`: utilidades de seguridad (CSRF, hashing, verificación de rol).
- `views/*`: vistas para `home`, `auth`, `student`, `teacher`, `admin`.
- `supabase/migrations/*`: migraciones SQL para Supabase.
- `scripts/*`: utilidades de CLI (seed, pruebas de conexión).

## Decisiones de organización
- Unificación de routing: usar exclusivamente `router.php` → `index.php`.
- Router legacy movido a `docs/legacy_router.php` para referencia histórica; no se usa en tiempo de ejecución.
- `public/` se mantiene para despliegues en Apache/Nginx, pero el entorno local usa `router.php`.
- Mantener helpers de autenticación centralizados: preferir `init.php` y `src/Core/Security.php`.

## Pruebas rápidas
- Conexión a Supabase: `php test-connection.php` o visitar `http://localhost:8000/test-connection.php`.
- Siembra de usuarios: `php scripts/seed-users.php` (requiere políticas RLS adecuadas).

## Orden sugerido de migraciones (real)
1. `20251019010000_fix_users_rls_and_schema.sql` (columna `password_hash` y políticas mínimas).
2. `20250930233353_update_users_policies_for_public_access.sql` (acceso público a `users`).
3. `20251019003832_create_computer_lab_management.sql` (gestión de laboratorios).
4. `20251022_add_db_indexes_and_constraints.sql`.
5. `20251022_add_soft_delete.sql`.
6. `20251022_add_admin_audit.sql` y `20251022_add_admin_audit_updates_and_triggers.sql`.

## Notas
- OFFLINE_MODE: usar `isOfflineModeEnabled()` (configurable en `.env` o `storage/ai_config.json`).
- No se debe usar `docs/legacy_router.php` en producción; sirve solo como referencia.