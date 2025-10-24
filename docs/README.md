# Estructura del Proyecto Algorix

## Organización de Directorios

```
algorix/
├── public/              # Punto de entrada y archivos públicos
│   ├── assets/         # Recursos estáticos
│   │   ├── images/    # Imágenes e iconos
│   │   ├── fonts/     # Fuentes tipográficas
│   │   └── videos/    # Recursos multimedia
│   ├── styles/         # Estilos CSS
│   │   ├── base/      # Estilos base y variables
│   │   ├── components/# Estilos de componentes
│   │   ├── layouts/   # Estilos de layouts
│   │   └── themes/    # Temas y variantes
│   ├── js/            # JavaScript
│   │   ├── core/      # JavaScript principal
│   │   ├── modules/   # Módulos JavaScript
│   │   └── utils/     # Utilidades JavaScript
│   └── dist/          # Archivos compilados
│
├── src/                # Código fuente principal
│   ├── Config/        # Configuraciones
│   ├── Core/          # Clases base
│   ├── Controllers/   # Controladores
│   ├── Models/        # Modelos
│   ├── Services/      # Servicios
│   └── Utils/         # Utilidades
│
├── tests/              # Pruebas
└── docs/               # Documentación
```

## Convenciones de Nomenclatura

1. Archivos y Carpetas:
   - Usar PascalCase para clases: `UserController.php`
   - Usar kebab-case para assets: `main-style.css`
   - Usar snake_case para funciones: `handle_auth.php`

2. Clases y Namespaces:
   - Clases en PascalCase: `class UserController`
   - Namespaces en PascalCase: `namespace App\Controllers`

3. Base de Datos:
   - Tablas en minúsculas y plural: `users`, `courses`
   - Columnas en snake_case: `user_id`, `created_at`

## Arquitectura

El proyecto sigue una arquitectura MVC (Modelo-Vista-Controlador):

- **Modelos**: Manejan la lógica de negocio y acceso a datos
- **Vistas**: Manejan la presentación
- **Controladores**: Coordinan el flujo de la aplicación

### Flujo de la Aplicación

1. Las peticiones entran por `public/index.php`
2. El Router dirige la petición al controlador apropiado
3. El controlador interactúa con los modelos necesarios
4. Los modelos procesan la lógica de negocio
5. El controlador renderiza la vista apropiada
6. La vista se devuelve al usuario

## Dependencias y Assets

### Estilos (CSS)
- Variables en `styles/base/variables.css`
- Componentes en `styles/components/`
- Layouts en `styles/layouts/`
- Temas en `styles/themes/`

### JavaScript
- Módulos core en `js/core/`
- Componentes en `js/modules/`
- Utilidades en `js/utils/`

## Seguridad

1. Autenticación:
   - Sistema basado en sesiones
   - Roles: student, teacher, admin
   - Middleware de autorización

2. Validación:
   - Validación de entrada en controladores
   - Sanitización de salida en vistas
   - Protección CSRF

## Mantenimiento

1. Base de Datos:
   - Esquema en `database/schema.sql`
   - Migraciones en `database/migrations/`

2. Caché:
   - Configuración en `src/Config/cache.php`
   - Sistema de caché para optimización

3. Logs:
   - Logs de errores en `logs/`
   - Logs de acceso en `logs/access/`