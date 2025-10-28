# Algorix Plataforma Educacional

[![CI](https://img.shields.io/github/actions/workflow/status/Fefox-glitch/algorix-plataforma-educacional/ci-cd.yml?branch=main&label=CI)](https://github.com/Fefox-glitch/algorix-plataforma-educacional/actions/workflows/ci-cd.yml) [Run workflow](https://github.com/Fefox-glitch/algorix-plataforma-educacional/actions/workflows/ci-cd.yml)
[![Coverage](https://codecov.io/gh/Fefox-glitch/algorix-plataforma-educacional/branch/main/graph/badge.svg)](https://codecov.io/gh/Fefox-glitch/algorix-plataforma-educacional)
[![GHCR](https://img.shields.io/badge/GHCR-Algorix-blue?logo=github)](https://github.com/Fefox-glitch/algorix-plataforma-educacional/pkgs/container/algorix)
[![Docker Hub](https://img.shields.io/badge/Docker%20Hub-rsfefox%2Falgorix-2496ED?logo=docker)](https://hub.docker.com/repository/docker/rsfefox/algorix)

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
git clone https://github.com/Fefox-glitch/algorix-plataforma-educacional.git
cd algorix-plataforma-educacional
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

### Notas de Release
- Detalles de `v0.1.0`: consulta `docs/RELEASE_NOTES_v0.1.0.md`.

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
  - Define `Settings > Variables > Actions`: `DOCKERHUB_REPO` (por ejemplo `rsfefox/algorix`).
  - Define `Settings > Secrets and variables > Actions`: `DOCKERHUB_USERNAME` y `DOCKERHUB_TOKEN`.
  - Pull: `docker pull $DOCKERHUB_REPO:latest`
  - Repo: `https://hub.docker.com/repository/docker/rsfefox/algorix`

#### Plataformas soportadas

- Multi‑arquitectura: `linux/amd64` y `linux/arm64` (Buildx + QEMU).
- Las publicaciones en `main`, tags `v*` y releases generan ambas arquitecturas.

### Buildx Cloud (opcional)

- Para acelerar builds, puedes usar Docker Buildx Cloud en la CI.
- Configura en el repositorio la variable `Settings > Variables > Actions`:
  - `BUILDX_CLOUD_ENDPOINT` (por ejemplo `rsfefox/algorix`).
- El workflow utilizará automáticamente `driver: cloud` con ese endpoint en los jobs de publicación (GHCR y Docker Hub). Si no está definida, usa el builder por defecto.
- Nota: los pasos que realizan `load: true` (como el build de artefacto Docker local) no cargan en el daemon cuando se usa Cloud; los jobs de publicación hacen `push` y funcionan correctamente.

Uso local de Buildx Cloud (opcional):

```bash
# Requiere Docker Desktop instalado y sesión iniciada (docker login)
docker buildx create --driver cloud rsfefox/algorix --name cloud-rsfefox-algorix
docker buildx use cloud-rsfefox-algorix
docker buildx ls

# Ejemplo de build multi‑arch con push
docker buildx build --platform linux/amd64,linux/arm64 -t ghcr.io/Fefox-glitch/algorix:dev . --push
```

### Publicación manual (Actions)

Sin Docker local, puedes publicar imágenes vía GitHub Actions usando el flujo manual:

- Navega a `Actions > PHP CI > Run workflow`.
- Selecciona la rama que contiene el workflow (ej. `cleanup/structure`).
- Inputs:
  - `registry`: `dockerhub` (por defecto) o `ghcr`.
  - `tag`: el tag a publicar (ej. `dev`, `v0.1.0`, `latest`).
- Requisitos para Docker Hub:
  - Variables: `DOCKERHUB_REPO=rsfefox/algorix`.
  - Secrets: `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`.
- Opcional: `BUILDX_CLOUD_ENDPOINT=rsfefox/algorix` para usar Buildx Cloud.

Ejemplo:

```
# Actions > PHP CI > Run workflow
branch: cleanup/structure
registry: dockerhub
tag: dev
```

Resultado:
- Publicación multi‑arch (`linux/amd64`, `linux/arm64`) en `${DOCKERHUB_REPO}:dev`.
- Si eliges `ghcr`, se publica en `ghcr.io/Fefox-glitch/algorix:dev`.

## Contribuir

Consulta `CONTRIBUTING.md` para el flujo de ramas, estilo de commits y checklist de PRs.

## Licencia

MIT. Ver `LICENSE`.

## Seguridad

- Evita commitear secretos; usa `.env` (no versionado).
- El CI ejecuta TruffleHog para detectar fugas.

## Contacto

Abre un issue en GitHub para soporte o propuestas.