# CI: imágenes en GHCR/Docker Hub, multi-arch y documentación

## Resumen
Este Pull Request integra la publicación de imágenes Docker en **GHCR** y **Docker Hub**, habilita compilación **multi-arquitectura** (`linux/amd64`, `linux/arm64` con Buildx + QEMU), y actualiza la documentación (README) para reflejar el flujo de CI/CD y variables/secrets requeridos.

## Cambios clave
- CI: añade job `docker-publish-dhub` condicionado por `DOCKERHUB_REPO` y secretos `DOCKERHUB_USERNAME`/`DOCKERHUB_TOKEN`.
- CI: habilita multi-arch en jobs de publicación (GHCR y Docker Hub) con `docker/setup-qemu-action` y `platforms: linux/amd64,linux/arm64`.
- Docs: README actualizado con guía de publicación en GHCR y Docker Hub, y explicación del soporte multi-arch.

## Checklist
- [ ] Configurar variables y secretos de Docker Hub en el repo:
  - Variables: `DOCKERHUB_REPO`
  - Secrets: `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`
- [ ] Configurar `CODECOV_TOKEN` (si el repositorio es privado) para subir cobertura sin restricciones.
- [ ] Proteger la rama `main` con checks requeridos en Branch Protection:
  - `build-test` (tests y análisis)
  - `ghcr-publish` (publicación en GHCR)
  - `docker-publish-dhub` (publicación en Docker Hub)
- [ ] Verificar publicación de `v0.1.0` en GHCR y Docker Hub (multi-arch):
  - GHCR: `ghcr.io/Fefox-glitch/algorix:v0.1.0`
  - Docker Hub: `${DOCKERHUB_REPO}:v0.1.0` (si configurado)
- [ ] Confirmar que el README refleja la guía de CI/CD y multi-arch.

## Verificación
1) GitHub Actions: revisar runs del workflow de CI para el tag `v0.1.0`.
2) GHCR: comprobar el paquete en `https://github.com/Fefox-glitch/test/pkgs/container/algorix` y que existan las dos arquitecturas.
3) Docker Hub: comprobar que la imagen `${DOCKERHUB_REPO}:v0.1.0` está disponible y soporta multi-arch.
4) Codecov: verificar que el informe de cobertura se subió correctamente (si procede).

## Notas de release
- Versionado semántico habilitado: crear tags `vX.Y.Z` dispara publicación con etiquetas semver.
- Uso de imágenes:
  - GHCR: `docker pull ghcr.io/Fefox-glitch/algorix:<tag>`
  - Docker Hub: `docker pull ${DOCKERHUB_REPO}:<tag>`