# Algorix

[![CI](https://img.shields.io/github/actions/workflow/status/Fefox-glitch/test/ci-cd.yml?branch=main&label=CI)](https://github.com/Fefox-glitch/test/actions/workflows/ci-cd.yml)
[![Coverage](https://codecov.io/gh/Fefox-glitch/test/branch/main/graph/badge.svg)](https://codecov.io/gh/Fefox-glitch/test)
[![GHCR](https://img.shields.io/badge/GHCR-Algorix-blue?logo=github)](https://github.com/Fefox-glitch/test/pkgs/container/algorix)

Plataforma educativa PHP para gestionar laboratorios, sesiones y paneles de estudiante/profesor/administrador.

## Objetivo del proyecto

Ofrecer una solución web para administrar laboratorios de computación y actividades educativas, con paneles dedicados (admin, student, teacher), integración con Supabase y utilidades para gestión de sesiones y recursos.

## Requisitos

- `PHP 8.2+`
- `Composer 2`
- Extensiones: `curl`, `openssl`, `mbstring`, `zip`, `pdo_pgsql`

## Configuración local

1) Clonar e instalar dependencias

```bash
git clone https://github.com/Fefox-glitch/test.git
cd Algorix-main
composer install
```

2) Variables de entorno

- Copia `.env.example` a `.env` y completa `SUPABASE_URL`, `SUPABASE_KEY` y cualquier otra que corresponda.

3) Servidor de desarrollo

```bash
# Opción recomendada con document root
php -S localhost:8000 -t public router.php

# Alternativa usando index.php
php -S localhost:8000 index.php
```

4) Acceso

- `http://localhost:8000/`
- Panel admin: `http://localhost:8000/admin/dashboard`

## Comandos frecuentes

- Lint: `vendor/bin/phpcs --standard=phpcs.xml`
- Análisis estático: `vendor/bin/phpstan analyse`
- Tests: `vendor/bin/phpunit -c phpunit.xml.dist`

## Estructura del proyecto

```
├── public/          # Punto de entrada público y estilos
├── src/             # Código fuente (Core, Controllers, Services, Utils)
├── views/           # Vistas (admin, student, teacher, auth)
├── assets/js/       # JavaScript del frontend
├── tests/           # Unit e integración (PHPUnit)
├── docker/          # Configuración de Nginx para contenedor
├── supabase/        # Migraciones y scripts relacionados
└── .github/workflows/ci-cd.yml  # CI con lint/tests/artifacts
```

## CI/CD

Este repositorio integra GitHub Actions para:
- Lint PHP (PHPCS), análisis estático (PHPStan) y tests (PHPUnit) en cada PR.
- Smoke tests de rutas levantando servidor PHP embebido.
- Build de artefactos (ZIP) y build de imagen Docker (cacheada).
- Escaneo de secretos (TruffleHog) en PRs.
 - Publicación de imágenes:
   - GHCR: en `main` (`latest` y `sha`) y en tags `v*` (semver).
   - Docker Hub: opcional, habilitado si defines `vars.DOCKERHUB_REPO` y secretos `DOCKERHUB_USERNAME`/`DOCKERHUB_TOKEN`.

## Contenedores (Docker)

- Imagen `php:8.2-fpm` con Composer. Nginx se configura con `docker/nginx/default.conf`.
- Build de ejemplo (local):

```bash
docker build -t algorix:local .
```

### Imágenes publicadas

- GHCR:
  - Pull: `docker pull ghcr.io/Fefox-glitch/algorix:latest`
  - Versionado: `docker pull ghcr.io/Fefox-glitch/algorix:vX.Y.Z`
- Docker Hub (si está configurado):
  - Define `Settings > Variables > Actions`: `DOCKERHUB_REPO` (por ejemplo `fefoxglitch/algorix`).
  - Define `Settings > Secrets and variables > Actions`: `DOCKERHUB_USERNAME` y `DOCKERHUB_TOKEN`.
  - Pull: `docker pull $DOCKERHUB_REPO:latest`

## Contribuir

Consulta `CONTRIBUTING.md` para el flujo de ramas, estilo de commits y checklist de PRs.

## Licencia

MIT. Ver `LICENSE`.

## Seguridad

- Evita commitear secretos; usa `.env` (no versionado).
- El CI ejecuta TruffleHog para detectar fugas.

## Contacto

Abre un issue en GitHub para soporte o propuestas.