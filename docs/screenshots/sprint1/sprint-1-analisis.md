# Sprint 1: Análisis de arquitectura, framework y stack tecnológico

## 1. Objetivo del sprint

El objetivo del Sprint 1 fue analizar el repositorio base del proyecto **Hospital HIS**, verificar su funcionamiento en un entorno local e identificar la arquitectura, framework y stack tecnológico utilizado. Esta revisión permitió comprender la estructura inicial del sistema antes de documentar o diseñar el módulo asignado.

## 2. Repositorio base analizado

Repositorio base proporcionado por el docente:

`https://github.com/rortizs/project_final_analisis_sistemas_Uno.git`

El proyecto fue clonado y ejecutado en un entorno local para verificar su funcionamiento inicial.

## 3. Arquitectura identificada

El proyecto utiliza una arquitectura cliente-servidor. El backend está construido con Laravel y expone servicios mediante rutas API. El frontend utiliza Vue 3 como aplicación de tipo SPA, consumiendo los servicios del backend mediante peticiones HTTP.

La comunicación entre frontend y backend se realiza mediante una API bajo la ruta `/api/v1`. Además, el proyecto contempla autenticación mediante JWT y manejo de multitenancy por medio de cabeceras HTTP.

## 4. Frameworks utilizados

Durante el análisis se identificaron los siguientes frameworks principales:

* **Laravel 12:** framework PHP utilizado para el backend, rutas, controladores, migraciones, autenticación y lógica del servidor.
* **Vue 3:** framework JavaScript utilizado para la construcción del frontend.
* **Vite:** herramienta utilizada para compilar y servir el frontend durante el desarrollo.

## 5. Stack tecnológico identificado

El stack tecnológico del proyecto base está compuesto por:

* PHP 8.4.21
* Laravel 12.58.0
* Composer
* Node.js
* npm
* Vue 3
* Vite
* SQLite como base de datos local para pruebas
* JWT para autenticación
* API REST bajo `/api/v1`

## 6. Configuración local realizada

Para levantar el proyecto se configuró el archivo `.env` utilizando SQLite como base de datos local. Se creó el archivo `database/database.sqlite` y se ajustó la variable `DB_DATABASE` con la ruta absoluta correspondiente al proyecto.

También se verificó que el proyecto pudiera ejecutarse correctamente mediante Laravel.

Comandos utilizados durante la verificación:

```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate
php artisan serve
php artisan route:list
```

## 7. Verificación aplicada

Se ejecutó el comando:

```bash
php artisan route:list
```

Como resultado, se identificó que el proyecto base actualmente cuenta principalmente con rutas de autenticación:

```text
POST      api/v1/auth/login
POST      api/v1/auth/logout
GET       api/v1/auth/me
POST      api/v1/auth/refresh
POST      api/v1/auth/register
```

También se encontraron rutas generales del sistema, como:

```text
GET       login
GET       up
GET       {any?}
```

## 8. Hallazgos principales

Durante el análisis se verificó que el proyecto base levanta correctamente en el navegador mediante la ruta:

```text
http://127.0.0.1:8000
```

La pantalla inicial muestra el nombre del sistema y una breve descripción de la infraestructura base.

También se determinó que el proyecto no cuenta todavía con módulos funcionales completos como pacientes, alergias, camas, citas, expediente médico o laboratorio. Esto indica que el repositorio funciona como una base inicial para que cada estudiante analice, documente o implemente el módulo asignado.

## 9. Relación con el módulo asignado

El módulo asignado al estudiante Javier Alexander Fajardo López es:

**Módulo 15: Alergias del paciente**

Durante este Sprint 1 se verificó que no existen rutas, modelos, controladores ni vistas específicas relacionadas con alergias del paciente. Por lo tanto, el módulo debe ser diseñado y documentado como parte del avance posterior.

## 10. Decisión humana tomada

Se decidió utilizar SQLite para la verificación local inicial porque permite levantar el proyecto de forma rápida sin depender de una configuración externa de MySQL. Esta decisión facilitó comprobar que Laravel, las migraciones y la estructura base del sistema funcionaran correctamente.

## 11. Conclusión del Sprint 1

El Sprint 1 permitió confirmar que el proyecto base funciona correctamente en el entorno local. Se identificó una arquitectura basada en Laravel y Vue, con API REST, autenticación JWT y estructura inicial para un sistema hospitalario. Además, se comprobó que el módulo de alergias del paciente aún no se encuentra implementado, por lo que será necesario documentar su diseño, comportamiento esperado y relación con el paciente en los siguientes avances.
