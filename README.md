# Collabtales

Collab Tales es una plataforma web diseñada para que todas las personas puedan crear y desarrollar cuentos de manera colaborativa. Cada usuario puede iniciar un cuento y generar un código único que permitirá invitar a otros participantes para continuar la historia. El objetivo es fomentar la creatividad, el trabajo en equipo, la escritura y la lectura.

## Contenido

- [Características](#características)
- [Requisitos previos](#requisitos-previos)
- [Instalación](#instalación)
- [Uso](#uso)
- [Scripts disponibles](#scripts-disponibles)
- [Configuración del entorno de desarrollo](#configuración-del-entorno-de-desarrollo)
- [Personalización de la configuración](#personalización-de-la-configuración)
- [Funcionalidades](#funcionalidades)
- [Contribuir](#contribuir)
- [Recursos](#recursos)
- [Licencia](#licencia)

## Características

- **Vue 3** para crear interfaces de usuario reactivas.
- **Vite** como herramienta de bundling, lo que permite un arranque rápido y recargas instantáneas durante el desarrollo.
- **TypeScript** (soporte mediante `vue-tsc`) para mejorar la calidad del código y la experiencia del desarrollador.
- Configuración básica de ESLint para mantener un código limpio y consistente.
- Plantilla lista para incorporar pruebas unitarias utilizando **Vitest**.

## Requisitos previos

- Php para backend (local)
- [npm](https://www.npmjs.com/) o [Yarn](https://yarnpkg.com/)

## Instalación

1. Clona el repositorio:

   ```bash
   git clone https://github.com/jd-becerra/collabtales.git
   cd collabtales
   ```

2. Instala las dependencias:

   ```bash
   npm install
   ```

## Uso

### Desarrollo

Para iniciar el servidor de desarrollo y ver la aplicación en el navegador:

```bash
npm run dev
```

La aplicación se ejecutará en [http://localhost:3000](http://localhost:3000) (o en el puerto configurado por Vite).

### Construcción para producción

Para compilar la aplicación y generar los archivos optimizados:

```bash
npm run build
```

### Pruebas unitarias

Para ejecutar las pruebas unitarias con **Vitest**:

```bash
npm run test:unit
```

### Linting

Para revisar el código y asegurarte de que sigue las convenciones establecidas:

```bash
npm run lint
```

## Scripts disponibles

- **npm run dev**: Inicia el servidor de desarrollo con recarga en caliente.
- **npm run build**: Compila la aplicación para producción.
- **npm run test:unit**: Ejecuta pruebas unitarias utilizando Vitest.
- **npm run lint**: Realiza análisis estático del código con ESLint.

## Configuración del entorno de desarrollo

Se recomienda utilizar [Visual Studio Code (VSCode)](https://code.visualstudio.com/) junto con la extensión [Volar](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar) para aprovechar al máximo el soporte de Vue 3 y TypeScript.

> **Nota:** TypeScript no maneja de forma nativa la información de tipos para archivos `.vue`, por lo que se utiliza `vue-tsc` para el chequeo de tipos.

## Personalización de la configuración

- **Vite:** Puedes personalizar la configuración de Vite según las necesidades de tu proyecto. Consulta la [documentación de Vite](https://vite.dev) para más detalles.
- **ESLint:** La configuración de ESLint se encuentra en el archivo `eslint.config.ts`. Puedes modificarla para adaptarla a tu estilo de codificación.
- **TypeScript:** La configuración de TypeScript se encuentra en los archivos `tsconfig.json`, `tsconfig.app.json`, etc.

## Funcionalidades

Collabtales incluye las siguientes funcionalidades:

- **Creación y edición de historias**: Permite a los usuarios escribir y modificar historias colaborativas.
- **Colaboración en tiempo real**: Múltiples usuarios pueden contribuir a la misma historia simultáneamente.
- **Autenticación de usuarios**: Registro e inicio de sesión mediante autenticación segura.
- **Gestor de roles y permisos**: Asigna diferentes niveles de acceso a los usuarios.
- **Interfaz amigable y responsiva**: Diseño moderno y adaptable a dispositivos móviles..

## Contribuir

Si deseas contribuir al proyecto, sigue estos pasos:

1. Haz un fork del repositorio.
2. Crea una nueva rama para tu funcionalidad o arreglo:
   ```bash
   git checkout -b mi-nueva-funcionalidad
   ```
3. Realiza los cambios y haz commit:
   ```bash
   git commit -m "Agrega nueva funcionalidad X"
   ```
4. Envía un pull request para revisión.

Cualquier contribución es bienvenida.

## Recursos

- [Documentación de Vue 3](https://vuejs.org/)
- [Guía de Vite](https://vite.dev)
- [Vitest](https://vitest.dev)
- [ESLint](https://eslint.org/)
- [Volar para VSCode](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar)

## Licencia

*Este proyecto no especifica una licencia. Revisa el repositorio para más información o contacta al autor para definir los términos de uso.*
