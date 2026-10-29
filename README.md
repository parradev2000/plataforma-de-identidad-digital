# Plataforma de Identidad Digital

La Plataforma de Identidad Digital es un ecosistema de microservicios para la gestión de identidad, autenticación, autorización y seguimiento de solicitudes. Está compuesta por múltiples servicios independientes que se integran entre sí para dar soporte a los procesos de identificación y acceso.

## Arquitectura

El proyecto está organizado en microservicios, cada uno con sus propias responsabilidades:

| Microservicio | Tecnología | Descripción |
|--------------|-------------|-------------|
| **Microservicio-PID** | Laravel 10 (PHP 8.1+) | Servicio principal de Identidad Digital. Gestiona usuarios, roles, permisos, personas, solicitudes, servicios, estados, auditoría y autenticación. Utiliza PostgreSQL con esquemas separados (`autenticacion`, `nomencladores`, `datos`). |
| **Microservicio-RES** | FastAPI (Python 3.11) | Servicio REST para registro y gestión de personas con niveles de acceso. Provee CRUD de personas y niveles de acceso con validaciones, documentación offline (Swagger UI / ReDoc) y migraciones con Alembic. |
| **Microservicio-SIAC** | Laravel 10 (PHP 8.1+) | Microservicio de integración/autenticación complementario. |
| **Microservicio-SUIN** | Laravel 10 (PHP 8.1+) | Microservicio para gestión relacionada con SUIN. |
| **Microservicio-ActivoFar** | Laravel 10 (PHP 8.1+) | Microservicio para gestión de activo farmacéutico. |

## Funcionalidades principales (Microservicio-PID)

- **Autenticación y autorización**: Login/logout, tokens, middleware de autenticación, integración con servidor OAuth/WSO, gestión de blacklist de tokens.
- **Gestión de usuarios**: CRUD de usuarios, cambio/reset de contraseña, registro masivo, actualización masiva, eliminación masiva. Los usuarios se vinculan a personas y roles.
- **Gestión de roles y permisos**: Basado en Spatie Laravel Permission. Asignación/revocación de permisos a roles y usuarios.
- **Gestión de personas**: CRUD de personas con campos `nombres`, `apellidos`, `ci` (carnet de identidad), `id_digital` (único), `usuario` y `contraseña`. Integrado con solicitudes.
- **Gestión de solicitudes**: Seguimiento de solicitudes con estados y servicios, con relaciones a personas.
- **Catálogos**: Gestión de servicios y estados.
- **Auditoría**: Registro de acciones de usuarios con trazabilidad.
- **Integración en tiempo real**: Eventos con broadcasting para actualización de listados de usuarios.

## Estructura de base de datos

El Microservicio-PID utiliza PostgreSQL con múltiples esquemas:

- `autenticacion` - Usuarios, roles, permisos (Spatie), tokens
- `nomencladores` - Personas, catálogos
- `datos` - Solicitudes y datos transaccionales

Las migraciones se encuentran en `Microservicio-PID/database/migrations/`.

## Requisitos

### Para Microservicio-PID (Laravel)
- PHP >= 8.1
- Composer
- PostgreSQL
- Extensiones PHP: pdo_pgsql, mbstring, etc.

### Para Microservicio-RES (FastAPI)
- Python 3.11+
- PostgreSQL
- Ver `Microservicio-RES/requirements.txt`

## Instalación y configuración

### Microservicio-PID

```bash
cd Microservicio-PID
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Microservicio-RES

```bash
cd Microservicio-RES
python -m venv .venv
source .venv/bin/activate  # Linux/Mac
# o .\.venv\Scripts\Activate.ps1 en Windows
pip install -r requirements.txt
alembic upgrade head
uvicorn app.main:app --reload
```

Con Docker:
```bash
cd Microservicio-RES
docker compose up --build
```

## Pruebas

### Microservicio-PID
```bash
cd Microservicio-PID
php artisan test
```

### Microservicio-RES
```bash
cd Microservicio-RES
pytest
```

## Documentación

- **Microservicio-PID**: Sigue las convenciones de Laravel. Ver `Microservicio-PID/README.md`
- **Microservicio-RES**: Documentación Swagger UI/ReDoc disponible localmente en `/local/docs` y `/local/redoc` cuando el servicio está en ejecución
- **Diagramas**: Documentación visual en la carpeta `Doc/` (diagramas Draw.io)

## Repositorio

- GitLab: `http://gitlab.unicom.co.cu/proyectos-cdasi/plataforma-de-identidad-digital.git`
- GitHub: `https://github.com/parradev2000/plataforma-de-identidad-digital.git`
