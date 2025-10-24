# Algorix

Plataforma educativa para aprender programación de manera interactiva y efectiva.

## Descripción

Algorix es una plataforma web educativa diseñada para ayudar a estudiantes a aprender programación a través de desafíos interactivos, cursos estructurados y retroalimentación en tiempo real. La plataforma incorpora elementos de gamificación y aprendizaje adaptativo para mejorar la experiencia educativa.

## Características principales

- 🎮 Desafíos interactivos de programación
- 📚 Cursos estructurados por niveles
- 📊 Seguimiento del progreso del estudiante
- 🎯 Sistema de retroalimentación en tiempo real
- 🌙 Modo oscuro/claro
- 🔄 Funcionamiento offline (PWA)
- 👥 Perfiles de estudiante y profesor
- 📱 Diseño responsivo

## Tecnologías utilizadas

- JavaScript (ES6+)
- HTML5
- CSS3
- Jest (Testing)
- Supabase (Base de datos)
- PWA (Progressive Web App)

## Instalación

1. Clona el repositorio:
\`\`\`bash
git clone https://github.com/[usuario]/algorix.git
cd algorix
\`\`\`

2. Instala las dependencias:
\`\`\`bash
npm install
\`\`\`

3. Configura las variables de entorno:
   - Crea un archivo \`.env\` en la raíz del proyecto
   - Añade las variables necesarias (ver \`.env.example\`)

4. Inicia el servidor de desarrollo:
\`\`\`bash
npm start
\`\`\`

## Uso

1. Accede a la aplicación a través del navegador
2. Crea una cuenta o inicia sesión
3. Selecciona un curso o desafío para comenzar
4. Sigue las instrucciones en pantalla

## Tests

Para ejecutar los tests:

\`\`\`bash
# Ejecutar todos los tests
npm test

# Ejecutar tests con watch mode
npm run test:watch

# Ver cobertura de tests
npm run test:coverage
\`\`\`

## Estructura del proyecto

\`\`\`
├── app/              # Núcleo de la aplicación
├── controllers/      # Controladores MVC
├── models/          # Modelos de datos
├── services/        # Servicios de la aplicación
├── views/           # Vistas y componentes UI
├── styles/          # Archivos CSS
└── __tests__/       # Tests
\`\`\`

## Contribuir

1. Fork el proyecto
2. Crea una rama para tu característica (\`git checkout -b feature/nueva-caracteristica\`)
3. Realiza tus cambios y haz commit (\`git commit -am 'Añade nueva característica'\`)
4. Push a la rama (\`git push origin feature/nueva-caracteristica\`)
5. Crea un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo \`LICENSE\` para más detalles.

## Contacto

Para soporte o consultas, por favor abre un issue en el repositorio.