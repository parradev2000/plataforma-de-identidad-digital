# Microservicio-PID (Plataforma de Identidad Digital)

Microservicio principal del ecosistema de Identidad Digital, desarrollado con [Laravel 10](https://laravel.com) y PHP 8.1+. Gestiona autenticación, autorización, usuarios, roles, permisos, personas, solicitudes, servicios, estados y auditoría.

## Características

- **Autenticación**: Login con `usuario`/contraseña, logout, validación de token, integración con servidor WSO (OAuth).
- **Autorización**: Control de acceso basado en roles y permisos mediante [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission).
- **Usuarios**: CRUD, cambio y reseteo de contraseña, operaciones masivas (registro/actualización/eliminación), vinculación con persona y rol.
- **Personas**: Gestión de personas en `nomencladores.personas` con `nombres`, `apellidos`, `ci`, `id_digital` (único), `usuario`, `contrasena`.
- **Solicitudes**: Gestión de solicitudes vinculadas a personas, con estados y servicios.
- **Catálogos**: Servicios y estados.
- **Auditoría**: Registro de acciones con usuario, IP y fecha/hora.
- **Tiempo real**: Broadcasting con eventos para actualizar listados de usuarios.
- **Múltiples esquemas PostgreSQL**: `autenticacion`, `nomencladores`, `datos`.

## Requisitos

- PHP >= 8.1
- Composer
- PostgreSQL
- Extensiones: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`

## Instalación

```bash
# Clonar e instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# Ejecutar migraciones
php artisan migrate

# (Opcional) Ejecutar seeders
php artisan db:seed

# Iniciar servidor
php artisan serve
```

## Configuración (.env)

Variables principales:

| Variable | Descripción |
|----------|-------------|
| `DB_CONNECTION` | Conexión PostgreSQL (`pgsql`) |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Credenciales de BD |
| `MIDDLEWARE` | Middleware de autenticación a utilizar |
| `PUSHER_HOST`, `PUSHER_PORT`, `PUSHER_APP_KEY` | Configuración para broadcasting/WebSocket |
| `URL_IDENTIDAD_DIGITAL` | URL base del servicio |

## Estructura de esquemas

| Esquema | Tablas principales |
|---------|-------------------|
| `autenticacion` | `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `personal_access_tokens`, `tokens`, `blacklist` |
| `nomencladores` | `personas`, `roles` (catálogo) |
| `datos` | `solicitudes`, `servicios`, `estados`, `auditoria` |

## Rutas principales (API v1)

| Grupo | Prefijo | Descripción |
|-------|---------|-------------|
| Auth | `/v1/login`, `/v1/auth/me`, `/v1/logout` | Autenticación |
| Usuarios | `/v1/users` | CRUD, cambio/reset contraseña, masivos |
| Roles | `/v1/roles` | CRUD y operaciones masivas |
| Permisos | `/v1/permissions` | CRUD, asignación/revocación |
| Personas | `/v1/persons` | CRUD de personas |
| Solicitudes | `/v1/solicitudes` | CRUD de solicitudes |
| Servicios | `/v1/services` | CRUD de servicios |
| Estados | `/v1/states` | CRUD de estados |
| Auditoría | `/v1/auditoria` | Consulta de auditoría |

## Pruebas

```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar con filtro
php artisan test --filter=EndpointsTest
```

## Comandos útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas
php artisan route:list
```

## Dependencias clave

- `laravel/framework` ^10.0
- `spatie/laravel-permission` ^6.0
- `g4t/swagger` (documentación)
- `pusher/pusher-php-server` (broadcasting)
- `guzzlehttp/guzzle`

## Licencia

Este proyecto es software privado. Todos los derechos reservados.
