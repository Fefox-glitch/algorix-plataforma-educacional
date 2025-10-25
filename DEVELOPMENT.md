# Desarrollo y Organización del Proyecto

Este documento resume cómo ejecutar el proyecto en local, la estructura vigente y decisiones de organización para evitar duplicaciones.

## Ejecución en local
- Requisitos: PHP 8+, extensiones `curl`, `openssl`, `mbstring`, `zip`.
- Variables de entorno: definir `SUPABASE_URL` y `SUPABASE_KEY` en `.env`.
- Servidor de desarrollo: `php -S localhost:8000 router.php`.
- Entrada única: `router.php` delega siempre en `index.php`.

## Estructura vigente
- `index.php`: enrutamiento principal de vistas y endpoints API.
- `init.php`: bootstrap de sesión, helpers `isAuthenticated()` y `hasRole()`.
- `src/Core/Security.php`: utilidades de seguridad (CSRF, hashing, verificación de rol).
- `src/Utils/functions.php`: helpers de render y sanitización (`render_view`, `render_error`, `sanitize_input`, `base_url`).
- `src/Services/ComputerControlService.php`: `checkPortOpen` expuesto público para pruebas.
- `views/*`: vistas para `home`, `auth`, `student`, `teacher`, `admin`.
- `tests/unit`: pruebas unitarias de utilidades y servicios.
- `tests/integration`: pruebas de flujo; contiene un test básico de render.
- `supabase/migrations/*`: migraciones SQL para Supabase.
- `scripts/*`: utilidades de CLI (seed, pruebas de conexión).

## Decisiones de organización
- Unificación de routing: usar exclusivamente `router.php` → `index.php`.
- Router legacy movido a `docs/legacy_router.php` para referencia histórica; no se usa en tiempo de ejecución.
- `public/` se mantiene para despliegues en Apache/Nginx, pero el entorno local usa `router.php`.
- Mantener helpers de autenticación centralizados: preferir `init.php` y `src/Core/Security.php`.

## Pruebas y calidad
- PHPUnit: `vendor\bin\phpunit -c phpunit.xml.dist --testsuite unit` y `--testsuite integration`.
- PHPCS: `vendor\bin\phpcs --standard=phpcs.xml tests\unit`.
- PHPStan: `vendor\bin\phpstan analyse`.
- Estado actual: pruebas en verde (Unit 6/9, Integration 1/4).
- PHPStan puede reportar símbolos no encontrados (p.ej., `supabaseRequest`, `APP_ROOT`) en módulos aún por stubear. Se sugiere añadir stubs o ignorar temporalmente en `phpstan.neon`.

## Flujo de servidor único y validaciones
- Servidor único local: levantar con `php -S localhost:8000 router.php`.
- Orden de validación en API Admin:
  - Método: endpoints `*/restore` solo aceptan `PATCH` → `405` + `Allow: PATCH` si no coincide.
  - Autenticación: sin sesión → `401`.
  - Autorización: rol incorrecto → `403`.
  - CSRF: métodos no‑GET requieren token válido → `403` si falta o es inválido.

## CSRF para clientes
- Fuentes aceptadas del token: `POST`/`GET` parámetro `csrf_token` o header `X-CSRF-TOKEN`.
- Generar token en vistas PHP: `<?php $t = \App\Core\Security::generateCsrfToken(); ?>` y enviarlo en formularios o peticiones.
- Exposición en vistas: el layout `views/shared/layout.php` inyecta `window.CSRF_TOKEN` automáticamente.

## Próximos pasos sugeridos
- Poblar `tests/integration/` con casos de API (login/registro, dashboards por rol, endpoints admin) usando buffers de salida e inyección de `$_SERVER`.
- Añadir tests para servicios (`GroupManagementService`, `LabManagementService`) con dobles/mocks de PDO.
- Documentar decisiones de arquitectura y convenciones de estilo.
- Ejemplos:
  - cURL:
    ```sh
    curl -X PATCH http://localhost:8000/api/admin/labs/restore \
      -H "Content-Type: application/json" \
      -H "X-CSRF-TOKEN: <TOKEN>" \
      -d '{"id":1}'
    ```
  - fetch:
    ```js
    fetch('/api/admin/labs/restore', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
      body: JSON.stringify({ id: 1 })
    });
    ```

## Patrón para nuevos controladores (PHPStan/PHPCS)

- Tipado de respuestas: declara en `stubs/phpstan-stubs.php` el `shape` de helpers que devuelvan arrays. Para `supabaseRequest` usamos `@return array{code:int, data:mixed, error:string|null}`.
- Lectura de códigos HTTP: usa `(int)$res['code']` y compara directamente (`(int)$res['code'] === 201`). Evita `?? 500` y otros null-coalesce para reducir avisos deterministas.
- Validación determinista: evita `empty()` sobre expresiones con valor conocido; prefiere checks explícitos por igualdad/desigualdad.
- `ignoreErrors` focalizados: si aparece un aviso conocido y justificado, añade un patrón mínimo y con alcance por archivo en `phpstan.neon` (por ejemplo `identifier: empty.expr` con `path:` al controlador). Elimina patrones cuando dejen de ser necesarios.
- Configuración de compatibilidad: mantenemos `reportUnmatchedIgnoredErrors: false` para que el pipeline no falle cuando no haya coincidencias en `ignoreErrors`. Revisa estos patrones al subir el `level` de PHPStan.
- Comandos de validación:
  - `vendor\bin\phpcs.bat --standard=phpcs.xml -n --report=summary --extensions=php .`
  - `vendor\bin\phpstan.bat analyse -c phpstan.neon --memory-limit=512M`

Ejemplo mínimo para peticiones a Supabase:

```php
$res = supabaseRequest('POST', '/rest/v1/grades', [/* payload */]);
$code = (int)$res['code'];
if ($code !== 201) {
    // Manejo de error usando $res['error'] y/o $res['data']
}
```

Guía de stubs al añadir nuevos helpers:

- Añade la firma en `stubs/phpstan-stubs.php` con tipos y `shape` si devuelve array.
- Asegura que las claves usadas en el código están documentadas en el `shape`.
- Evita `mixed` cuando puedas especificar tipos concretos.

## Hook pre-commit (PHPCBF + PHPCS)

- Objetivo: asegurar el estilo PHP antes de cada commit, autocorrigiendo y bloqueando si quedan errores.
- Ubicación: `.git/hooks/pre-commit` (ya creado en este repo).
- Flujo:
  - Detecta archivos PHP staged (`ACM`).
  - Ejecuta `vendor\bin\phpcbf --standard=phpcs.xml --extensions=php` sobre los archivos staged.
  - Reagrega cambios al staging (`git add`).
  - Ejecuta `vendor\bin\phpcs --standard=phpcs.xml -n --report=summary --extensions=php` y falla si hay errores.
- Requisitos: haber corrido `composer install` para disponer de `vendor/`.
- Cómo probar:
  - `git add <archivo.php>` y luego `sh .git/hooks/pre-commit`.
  - O realiza `git commit -m "test"` y verifica el resumen que imprime el hook.
- Omitir temporalmente (no recomendado): `git commit --no-verify`.
- Solución de problemas:
  - En Windows, usa Git Bash para ejecutar los hooks o asegúrate de que `vendor\bin` esté disponible en el entorno de Git.
  - Si PHPCS reporta muchos errores, ejecuta `vendor\bin\phpcbf --standard=phpcs.xml --extensions=php src` y reintenta el commit.