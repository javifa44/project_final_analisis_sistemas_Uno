# Proyecto base — Evaluación Final Análisis de Sistemas I

Proyecto **Laravel 12 + Vue 3 (Vite)** con **JWT**, **Spatie Laravel Permission** y **Stancl Tenancy** (tenant identificado por cabecera `X-Tenant-ID`). Esta base se entrega para que el estudiante analice la estructura existente y desarrolle el módulo asignado por el docente.

---

## Arquitectura construida

La aplicación sigue un modelo **SPA + API REST**: el navegador carga una única vista Blade que monta Vue; el backend expone JSON bajo `/api/v1`.

### Vista general

| Capa | Tecnología | Para qué sirve |
|------|------------|----------------|
| **Backend / API** | Laravel 12 | Punto único de negocio, persistencia, seguridad y contratos HTTP JSON. |
| **Autenticación API** | `tymon/jwt-auth` | Emite y valida tokens JWT en el guard `api`; no usa sesiones para el API. |
| **Autorización (RBAC)** | `spatie/laravel-permission` | Roles y permisos sobre el modelo `User` (guard `api`). |
| **Multitenancy base** | `stancl/tenancy` + tabla `tenants` | Modelo `Tenant` y columna `tenant_id` en usuarios. El tenant activo se **indica en cada petición** con `X-Tenant-ID` (sin bases de datos separadas en esta fase). |
| **Middleware propio** | `TenantMiddleware`, `JwtAuth` | `TenantMiddleware` resuelve y valida el tenant por cabecera; `JwtAuth` protege rutas con JWT y coherencia tenant–token. |
| **Frontend** | Vue 3 + Vue Router + Pinia | SPA: rutas del lado cliente, estado global (p. ej. sesión / token) y pantallas como login. |
| **Build frontend** | Vite 7 + `@vitejs/plugin-vue` | Empaqueta JS/CSS; alias `@` apunta a `resources/js`. |
| **Cliente HTTP** | Axios (`resources/js/plugins/axios.js`) | Llama al API con `Authorization: Bearer` y `X-Tenant-ID` según lo guardado en `localStorage`. |
| **Vista shell** | `resources/views/app.blade.php` | Inyecta el bundle Vite y el `<div id="app">` donde Vue se monta. |
| **Rutas web** | `routes/web.php` | Cualquier ruta devuelve la misma SPA (fallback) para que Vue Router maneje `/`, `/login`, etc. |

### Flujo típico de una petición

1. El usuario (o el formulario de login) fija el **ID del tenant**; Axios envía `X-Tenant-ID` y, si hay sesión, el **JWT** en `Authorization`.
2. Laravel aplica `TenantMiddleware` donde corresponda: si el tenant no existe, responde 404 JSON.
3. En rutas protegidas, `jwt.auth` valida el token; opcionalmente se compara el tenant del header con el del usuario del token.
4. Las respuestas del API son siempre **JSON**.

### Estructura relevante en el repo

```
app/Http/Controllers/Api/V1/AuthController.php   # registro, login, me, refresh, logout
app/Http/Middleware/TenantMiddleware.php         # cabecera X-Tenant-ID
app/Http/Middleware/JwtAuth.php                  # JWT + coherencia tenant
app/Models/User.php                              # JWT + HasRoles + tenant_id
app/Models/Tenant.php                            # modelo Stancl / tabla tenants
resources/js/                                    # Vue: router, stores, páginas, Axios
routes/api.php                                   # rutas bajo prefijo api/v1 (ver bootstrap/app.php)
```

---

## Qué se necesita para correr el proyecto

### Software instalado en tu máquina

| Requisito | Uso |
|-----------|-----|
| **PHP ≥ 8.2** | Ejecutar Laravel y Composer scripts (`artisan`, migraciones). |
| **Composer ≥ 2.x** | Instalar dependencias PHP (`vendor/`). |
| **Node.js ≥ 20** y **npm** | Instalar dependencias JS y ejecutar Vite (`npm run dev` / `npm run build`). |
| **Extensiones PHP habituales** | `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` (según tu stack). |
| **Base de datos** | **SQLite** (rápido en desarrollo, archivo `database/database.sqlite`) o **MySQL 8** en entornos más cercanos a producción. |

### Variables de entorno imprescindibles

Tras copiar `.env.example` a `.env`:

- **`APP_KEY`** — `php artisan key:generate`
- **`JWT_SECRET`** — `php artisan jwt:secret`
- **Conexión a BD** — según elijas SQLite o MySQL en `.env`
- **`VITE_API_URL`** — URL base del API que usará el frontend en desarrollo (p. ej. `http://localhost:8000/api/v1`) si el navegador sirve la SPA desde otro puerto (Vite).

Sin PHP/Composer/Node o sin BD configurada, el proyecto no podrá migrar ni compilar el frontend.

---

## Instalación y ejecución

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configura la base de datos en `.env` (SQLite o MySQL). Luego:

```bash
php artisan migrate
npm install
npm run dev
```

En **otra terminal**, el servidor HTTP de Laravel:

```bash
php artisan serve
```

Abre el frontend según la URL que muestre Vite (típicamente `http://localhost:5173`) y asegúrate de que `VITE_API_URL` apunte al backend (`php artisan serve` suele ser `http://127.0.0.1:8000`).

### Variables `.env` más usadas

| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL pública del backend (p. ej. `http://localhost:8000`). |
| `FRONTEND_URL` | URL del frontend en desarrollo (referencia / CORS si aplica). |
| `JWT_SECRET` | Secreto de firma JWT (generado con `jwt:secret`). |
| `JWT_TTL` | Minutos de vida del access token (por defecto 60). |
| `VITE_API_URL` | Base URL del API para Axios desde Vite. |

## API (`/api/v1`)

Todas las rutas del API requieren la cabecera **`X-Tenant-ID`** (UUID del tenant).

| Método | Ruta | Auth |
|--------|------|------|
| POST | `/auth/register` | No (devuelve JWT al registrar) |
| POST | `/auth/login` | No |
| GET | `/auth/me` | Bearer JWT |
| POST | `/auth/refresh` | Middleware `jwt.refresh` (renovación con ventana de refresh) |
| POST | `/auth/logout` | Bearer JWT |

Respuestas siempre en **JSON**.

---

## Validación recomendada

```bash
php artisan route:list --path=api
php artisan config:clear
npm run build
php artisan test
```

---

## Entrega esperada

El estudiante debe trabajar sobre su propio fork del repositorio y entregar en Canvas el enlace al repositorio forkeado, junto con una breve descripción del módulo implementado y los commits principales que evidencian su avance.

# Sprint 1: Análisis de arquitectura y stack tecnológico

## Objetivo

Analizar el repositorio base del proyecto **Hospital HIS**, verificar su ejecución local e identificar la arquitectura, frameworks y tecnologías utilizadas antes de documentar el módulo asignado.

## Repositorio analizado

Repositorio base proporcionado por el docente:

```text
https://github.com/rortizs/project_final_analisis_sistemas_Uno.git
```

El proyecto fue clonado y ejecutado localmente para validar su funcionamiento inicial.

## Arquitectura identificada

El proyecto utiliza una arquitectura **cliente-servidor**:

* **Backend:** Laravel, encargado de las rutas API, controladores, migraciones y lógica del servidor.
* **Frontend:** Vue 3 como aplicación SPA.
* **Comunicación:** API REST bajo la ruta `/api/v1`.
* **Autenticación:** JWT.
* **Multitenancy:** uso de cabeceras HTTP como `X-Tenant-ID`.

## Frameworks y tecnologías

Stack identificado:

* PHP 8.4.21
* Laravel 12.58.0
* Composer
* Node.js
* npm
* Vue 3
* Vite
* SQLite para pruebas locales
* JWT
* API REST `/api/v1`

## Configuración local

Se configuró el archivo `.env` para utilizar SQLite como base de datos local. También se creó el archivo:

```text
database/database.sqlite
```

Comandos utilizados:

```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate
php artisan serve
php artisan route:list
```

## Rutas verificadas

Mediante el comando:

```bash
php artisan route:list
```

Se identificaron principalmente rutas de autenticación:

```text
POST      api/v1/auth/login
POST      api/v1/auth/logout
GET       api/v1/auth/me
POST      api/v1/auth/refresh
POST      api/v1/auth/register
```

También se encontraron rutas generales:

```text
GET       login
GET       up
GET       {any?}
```

## Hallazgos principales

* El proyecto levanta correctamente en:

```text
http://127.0.0.1:8000
```

* La pantalla inicial muestra el nombre del sistema y una descripción base.
* El repositorio funciona como estructura inicial del sistema hospitalario.
* No se encontraron módulos funcionales completos como pacientes, alergias, camas, citas, expediente médico o laboratorio.

## Módulo asignado

Módulo asignado:

```text
Módulo 15: Alergias del paciente
```

Durante este sprint se verificó que aún no existen rutas, modelos, controladores ni vistas específicas para alergias del paciente. Por lo tanto, este módulo debe ser diseñado y documentado en los siguientes avances.

## Decisión tomada

Se utilizó SQLite para la verificación local inicial, ya que permite levantar el proyecto rápidamente sin depender de una configuración externa de MySQL.

## Conclusión

El Sprint 1 permitió confirmar que el proyecto base funciona correctamente en el entorno local. También se identificó que utiliza Laravel, Vue 3, API REST, JWT y una estructura inicial para un sistema hospitalario. El módulo de alergias del paciente aún no está implementado, por lo que deberá diseñarse y documentarse posteriormente.

## Sprint 2: Implementación del módulo Alergias del Paciente

En este sprint se implementó el módulo asignado **Módulo 15: Alergias del paciente**.
Se creó la estructura backend y frontend necesaria para consultar, registrar, editar y desactivar alergias asociadas a un paciente.

Cambios principales:

* Creación de modelos `Patient` y `PatientAllergy`.
* Creación de migraciones para `patients` y `patient_allergies`.
* Implementación del controlador `PatientAllergyController`.
* Registro de rutas API para el módulo.
* Creación de la pantalla Vue `PatientAllergiesPage.vue`.
* Uso de ruta dinámica `/patients/:patientId/allergies`.
* Visualización destacada de alergias activas mediante alerta en pantalla.

Evidencia:

* Rutas visibles con `php artisan route:list`.
* Módulo funcionando en navegador.
* Datos reflejados desde la base de datos.
* Capturas ubicadas en `docs/screenshots/sprint-2/`.

Commit principal:

`Sprint 2: implement patient allergies module`

---

## Sprint 3: Diagramas UML y documentación final

En este sprint se agregaron los diagramas UML correspondientes al módulo **Alergias del paciente** y se completó la documentación final para la entrega.

Diagramas incluidos:

* Diagrama de caso de uso.
* Diagrama de clases.
* Diagrama de secuencia.

Los diagramas se encuentran en:

`docs/uml/`

Este sprint documenta el diseño del módulo, la relación entre paciente y alergias, y el flujo de registro de una alergia desde el frontend hasta la base de datos.

Commit principal:

`Sprint 3: add UML diagrams for patient allergies module`

