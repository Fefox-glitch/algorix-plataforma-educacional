# Algorix - Configuración del Proyecto

## Resumen de Cambios

Se ha limpiado y configurado el proyecto PHP para funcionar con Supabase como base de datos.

### 1. Limpieza del Proyecto
- Eliminadas todas las dependencias de Node.js y JavaScript
- Removidos archivos no necesarios (controllers, models antiguos)
- Mantenidos solo archivos PHP esenciales y estilos CSS

### 2. Configuración de Base de Datos
- **Base de datos:** Supabase (PostgreSQL)
- **Conexión:** API REST de Supabase
- **Esquema:** Completamente configurado con 9 tablas

#### Tablas Creadas:
1. `users` - Usuarios del sistema (estudiantes, profesores, administradores)
2. `courses` - Cursos disponibles
3. `modules` - Módulos de cada curso
4. `exercises` - Ejercicios de programación
5. `submissions` - Envíos de ejercicios por estudiantes
6. `grades` - Calificaciones
7. `challenges` - Retos de programación
8. `challenge_test_cases` - Casos de prueba para retos
9. `challenge_submissions` - Envíos de retos

### 3. Sistema de Autenticación
- Registro de usuarios con códigos de acceso para profesores y administradores
- Login con email y contraseña
- Sesiones PHP para mantener usuarios autenticados
- Roles: `student`, `teacher`, `admin`

#### Códigos de Acceso:
- **Profesores:** `TEACH2024A1`
- **Administradores:** `ADMIN2024B1`

### 4. Estructura de Archivos

```
/project
├── config.php              # Configuración y conexión a Supabase
├── init.php                # Inicialización de sesión y funciones
├── autoload.php            # Autoloader de clases
├── index.php               # Punto de entrada principal
├── test-connection.php     # Script de prueba de conexión
├── .env                    # Variables de entorno (Supabase)
├── src/
│   └── Controllers/
│       └── AuthController.php  # Controlador de autenticación
├── views/
│   ├── home.php            # Página de inicio
│   ├── auth/
│   │   ├── login.php       # Formulario de login
│   │   └── register.php    # Formulario de registro
│   └── shared/
│       ├── layout.php      # Layout principal
│       └── header.php      # Header compartido
├── styles/                 # Archivos CSS
├── utils/
│   └── render.php         # Utilidades para renderizar vistas
└── database/
    └── schema.sql         # Esquema de base de datos

```

### 5. Flujo de la Aplicación

1. **Página de Inicio** (`/`)
   - Muestra bienvenida si no hay sesión
   - Muestra dashboard si hay sesión activa

2. **Registro** (`/auth/register`)
   - Formulario con nombre, email, contraseña, rol
   - Validación de código de acceso para profesores/admins
   - Crea usuario en Supabase
   - Inicia sesión automáticamente

3. **Login** (`/auth/login`)
   - Formulario con email y contraseña
   - Verifica credenciales contra Supabase
   - Crea sesión PHP
   - Redirige según rol

4. **Dashboards** (protegidos por rol)
   - `/student/dashboard` - Panel de estudiante
   - `/teacher/dashboard` - Panel de profesor
   - `/admin/dashboard` - Panel de administrador

### 6. Seguridad

#### Row Level Security (RLS)
- Habilitado en todas las tablas
- Políticas configuradas según roles
- Registro público permitido para `users`
- Login público permitido (lectura de usuarios)

#### Contraseñas
- Hasheadas con `password_hash()` (bcrypt)
- Almacenadas en campo `password_hash`

#### Sesiones PHP
- Cookie httponly habilitada
- Cookie only cookies habilitada
- Datos sensibles no expuestos

### 7. Pruebas

Para probar la conexión a Supabase:
```
http://localhost/algorix/test-connection.php
```

Este script verificará:
- Variables de entorno
- Conexión a Supabase
- Lectura de tablas

### 8. Próximos Pasos

1. Implementar dashboards completos para cada rol
2. Crear módulos de gestión de cursos
3. Implementar editor de código para ejercicios
4. Agregar sistema de evaluación automática
5. Crear reportes y estadísticas

### 9. Notas Importantes

- **Sin JavaScript:** El proyecto actualmente es 100% PHP
- **API REST:** Todas las operaciones de BD se hacen vía API REST de Supabase
- **RLS Público:** Las políticas de `users` están abiertas para permitir registro/login
- **Producción:** En producción, considerar usar Supabase Auth en lugar de gestión manual de contraseñas

### 10. Mantenimiento

Para actualizar la base de datos, usar:
```php
mcp__supabase__apply_migration($filename, $content)
```

Para consultar datos:
```php
$response = supabaseRequest('GET', 'table_name');
```

Para insertar datos:
```php
$response = supabaseRequest('POST', 'table_name', $data);
```

## Organización del proyecto (actualizada)

- Punto de entrada único en local: `router.php` delega todo a `index.php`.
- El archivo `src/Core/router.php` ha sido movido a `docs/legacy_router.php` porque no participa del flujo actual.
- `public/` queda para despliegues con servidor web; en local usar `php -S localhost:8000 router.php`.

## Supabase (real) — Migraciones imprescindibles

Ejecuta en el Editor SQL de Supabase:

```sql
ALTER TABLE public.users
    ADD COLUMN IF NOT EXISTS password_hash TEXT;

DO $$
DECLARE pol RECORD;
BEGIN
  FOR pol IN
    SELECT polname FROM pg_policies
    WHERE schemaname = 'public' AND tablename = 'users'
  LOOP
    EXECUTE format('DROP POLICY IF EXISTS %I ON public.users', pol.polname);
  END LOOP;
END $$;

ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Allow read for all" ON public.users
    FOR SELECT
    TO anon, authenticated
    USING (true);

CREATE POLICY "Allow insert for all" ON public.users
    FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

CREATE POLICY "Allow update self" ON public.users
    FOR UPDATE
    TO authenticated
    USING (id = auth.uid())
    WITH CHECK (id = auth.uid());

CREATE POLICY "Allow delete self" ON public.users
    FOR DELETE
    TO authenticated
    USING (id = auth.uid());
```

Luego puedes ejecutar `php scripts/seed-users.php` para crear usuarios de prueba y probar login real.
