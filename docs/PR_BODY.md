# CI/Docs: Buildx Cloud + Publicación manual + Badge Docker Hub

## Resumen
Este PR añade soporte opcional de **Docker Buildx Cloud**, un **flujo manual** de publicación (`workflow_dispatch`) con inputs de `tag` y `registry`, y actualiza el **README** con guía de Cloud y badge de **Docker Hub**. Mantiene publicación **multi‑arquitectura** (`linux/amd64`, `linux/arm64`) tanto en **GHCR** como **Docker Hub**.

## Cambios
- CI: `BUILDX_CLOUD_ENDPOINT` activa builder con `driver: cloud` en jobs de publicación.
- CI: `docker-publish-manual` permite publicar sin Docker local (GHCR/Docker Hub).
- CI: `registry` por defecto: `dockerhub`.
- Docs: README actualizado con Buildx Cloud, uso local y badge Docker Hub.
- Docs: CHANGELOG `v0.1.0` con notas de CI/CD y documentación.

## Cómo probar
- Configura variables/secrets:
  - `vars.DOCKERHUB_REPO=rsfefox/algorix`
  - `secrets.DOCKERHUB_USERNAME`, `secrets.DOCKERHUB_TOKEN`
  - (Opcional) `vars.BUILDX_CLOUD_ENDPOINT=rsfefox/algorix`
- Ejecuta `Actions > PHP CI > Run workflow` en la rama `cleanup/structure`:
  - `registry`: `dockerhub` (por defecto)
  - `tag`: `dev` (o el que quieras)
- Verifica que se publican imágenes multi‑arch en Docker Hub/GHCR.

## Impacto
- Facilita publicaciones sin entorno Docker local.
- Acelera builds con Buildx Cloud cuando está disponible.
- Mejora visibilidad con badge Docker Hub y documentación ampliada.