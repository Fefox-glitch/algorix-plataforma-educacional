# Changelog

## v0.1.0

### CI/CD
- Añadido soporte opcional de Docker Buildx Cloud mediante `BUILDX_CLOUD_ENDPOINT`.
- Publicación manual vía `workflow_dispatch` con inputs `tag` y `registry` (`ghcr`/`dockerhub`).
- Publicación multi‑arquitectura (`linux/amd64`, `linux/arm64`) para GHCR y Docker Hub.
- `registry` por defecto en el flujo manual: `dockerhub`.

### Imágenes y Repositorios
- GHCR: `ghcr.io/Fefox-glitch/algorix` con tags `latest`, `sha` y semver `vX.Y.Z`.
- Docker Hub: `rsfefox/algorix` (requiere `DOCKERHUB_REPO`, `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`).

### Documentación
- README: guía de Buildx Cloud (`BUILDX_CLOUD_ENDPOINT`), uso local y badge de Docker Hub.
- Instrucciones claras para ejecutar publicación manual desde Actions sin Docker local.

### Notas
- Si defines `BUILDX_CLOUD_ENDPOINT`, los jobs de publicación usarán `driver: cloud`; en caso contrario, usan el builder por defecto.
- Para publicar en Docker Hub, configura: `vars.DOCKERHUB_REPO`, `secrets.DOCKERHUB_USERNAME`, `secrets.DOCKERHUB_TOKEN`.