# Notas de Release — v0.1.0

## Objetivo
- Consolidar CI/CD con publicación multi‑arquitectura en GHCR y Docker Hub.
- Permitir publicación manual vía GitHub Actions sin Docker local.
- Añadir soporte opcional para Docker Buildx Cloud.

## Alcance de la versión
- CI: PHPCS, PHPStan, PHPUnit, smoke tests de rutas, artefactos ZIP.
- Imágenes Docker cacheadas y publicadas en `main`, tags `v*` y releases.
- Multi‑arch: `linux/amd64` y `linux/arm64` (Buildx + QEMU).

## Artefactos publicados
- GHCR: `ghcr.io/Fefox-glitch/algorix`.
  - Tags: `latest`, `sha` (commit), semver `vX.Y.Z` (ej. `v0.1.0`).
- Docker Hub: `rsfefox/algorix` (si está configurado).
  - Requiere `vars.DOCKERHUB_REPO`, `secrets.DOCKERHUB_USERNAME`, `secrets.DOCKERHUB_TOKEN`.

## Uso rápido
- GHCR:
  - `docker pull ghcr.io/Fefox-glitch/algorix:latest`
  - `docker pull ghcr.io/Fefox-glitch/algorix:v0.1.0`
- Docker Hub:
  - `docker pull rsfefox/algorix:latest`
  - `docker pull rsfefox/algorix:v0.1.0`

## Publicación manual (Actions)
- Enlace directo: https://github.com/Fefox-glitch/algorix-plataforma-educacional/actions/workflows/ci-cd.yml
- Inputs del `workflow_dispatch`:
  - `registry`: `dockerhub` (por defecto) o `ghcr`.
  - `tag`: etiqueta a publicar (ej. `dev`, `v0.1.0`).
- Requisitos para Docker Hub:
  - Variables: `DOCKERHUB_REPO=rsfefox/algorix`.
  - Secrets: `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`.
- Opcional (Buildx Cloud):
  - Variable: `BUILDX_CLOUD_ENDPOINT=rsfefox/algorix`.

## Checklist de verificación
- GHCR:
  - Imagen visible en Packages con tags `latest`, `v0.1.0`, `sha`.
  - `docker manifest inspect ghcr.io/Fefox-glitch/algorix:v0.1.0` muestra `amd64` y `arm64`.
- Docker Hub:
  - Repositorio `rsfefox/algorix` con tags `latest`/`v0.1.0`.
  - Pull exitoso desde entorno sin autenticación (si público).
- CI:
  - Badges de Actions y Codecov reflejan estado verde en `main`.

## Notas adicionales
- Buildx Cloud: si defines `BUILDX_CLOUD_ENDPOINT`, el builder usa `driver: cloud` para acelerar builds; si no, usa builder clásico.
- Apache `RewriteBase`: si usas Apache, asegúrate de que `RewriteBase` en `.htaccess` esté alineado con el subpath de despliegue.