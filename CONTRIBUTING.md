# Guía de Contribución

Gracias por tu interés en contribuir a Algorix. Este documento define el flujo de trabajo y estándares para mantener el proyecto saludable.

## Flujo de trabajo

- Crea una rama descriptiva desde `main` o la rama activa:
  - `feature/<descripcion-corta>`
  - `fix/<bug-o-issue>`
  - `chore/<tarea-mantenimiento>`

- Haz commits claros y atómicos:
  - `Admin: mejora navegación y estilos panel`
  - `Core: valida entrada en AuthController`

- Abre un Pull Request (PR) hacia `main` con:
  - Descripción de cambios
  - Capturas o ejemplos (si aplica)
  - Checklist de verificación (abajo)

## Checklist de PR

- [ ] Ejecuté `composer install` y el proyecto compila
- [ ] Pasan linters: `vendor/bin/phpcs --standard=phpcs.xml`
- [ ] Pasa PHPStan: `vendor/bin/phpstan analyse`
- [ ] Pasa tests: `vendor/bin/phpunit -c phpunit.xml.dist`
- [ ] Revisé que no se suban secretos (`.env`, tokens, claves)
- [ ] Agregué/actualicé documentación si es necesario

## Estilo de código

- PHP: PSR-12 (configurado vía `phpcs.xml`)
- Tipos y análisis: `phpstan.neon` (nivel establecido en el repo)
- JS: sin reglamentación estricta por ahora (se podrá añadir ESLint)

## Ejecución local

```bash
composer install
cp .env.example .env  # completar variables
php -S localhost:8000 -t public router.php
```

## Tests

```bash
vendor/bin/phpunit -c phpunit.xml.dist
```

## Seguridad

- No subas `.env` ni secretos. Usa variables de entorno.
- El CI ejecuta TruffleHog en PRs para detectar posibles fugas.

## Comunicación

- Usa Issues para reportes y propuestas
- Añade referencias a tickets en los commits y PRs cuando aplique