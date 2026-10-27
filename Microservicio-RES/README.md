# Microservicio-RES

Microservicio REST desarrollado con FastAPI y Python 3.11. Incluye documentación
offline (Swagger UI y ReDoc) y conexión síncrona a PostgreSQL mediante SQLAlchemy.

## Requisitos

- Python 3.11+
- PostgreSQL accesible en `10.10.16.250:5432`
- Docker (opcional)

## Configuración

Las variables se leen desde `.env` (ver `.env.example`):

| Variable       | Valor por defecto                                                  |
| -------------- | ------------------------------------------------------------------ |
| `APP_NAME`     | `Microservicio-RES`                                                |
| `APP_VERSION`  | `0.1.0`                                                            |
| `DEBUG`        | `false`                                                            |
| `DATABASE_URL` | `postgresql+psycopg2://postgres:postgres@10.10.16.250:5432/bd_res` |

## Instalación local

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
uvicorn app.main:app --reload
```

Servicio disponible en `http://127.0.0.1:8000`.

## Modelo de datos

### `nivel_acceso`

| Columna  | Tipo          | Restricciones        |
| -------- | ------------- | -------------------- |
| `id`     | `SERIAL`      | PK                   |
| `nombre` | `VARCHAR(50)` | NOT NULL, UNIQUE     |

Datos iniciales: `Sin acceso`, `Básico`, `Medio`, `Total`.

### `personas`

| Columna           | Tipo         | Restricciones                                      |
| ----------------- | ------------ | -------------------------------------------------- |
| `id`              | `SERIAL`     | PK                                                 |
| `nombres`         | `VARCHAR(150)` | NOT NULL, solo texto no vacío (`CHECK`)         |
| `no_pase`         | `VARCHAR(6)` | NOT NULL, UNIQUE, solo de 1 a 6 dígitos (`CHECK`)  |
| `ci`              | `VARCHAR(11)` | NULL, UNIQUE, solo de 1 a 11 dígitos (`CHECK`)    |
| `nivel_acceso_id` | `INTEGER`    | NOT NULL, FK → `nivel_acceso.id`, `ON DELETE RESTRICT` |

La migración `0002_personas_nivel_acceso` crea ambas tablas y carga 20 personas
de ejemplo distribuidas entre los cuatro niveles de acceso. La migración
`0003_personas_nombres` agrega el campo `nombres` con datos de ejemplo y
`0004_personas_ci` agrega el carnet de identidad (`ci`), opcional y con
backfill para las personas existentes.

## Endpoints

| Ruta                    | Descripción                              |
| ----------------------- | ---------------------------------------- |
| `GET /`                 | Información básica del servicio          |
| `GET /health`           | Estado del servicio y de PostgreSQL      |
| `GET /local/docs`       | Swagger UI (assets locales, sin CDN)     |
| `GET /local/redoc`      | ReDoc (assets locales, sin CDN)          |
| `GET /local/openapi.json` | Esquema OpenAPI                        |

### CRUD de personas (`/api/v1/personas`)

| Método   | Ruta        | Descripción                                   |
| -------- | ----------- | --------------------------------------------- |
| `GET`    | `/`         | Lista con `skip`, `limit`, `no_pase`, `ci`, `nivel_acceso_id` |
| `GET`    | `/{id}`     | Obtiene una persona                           |
| `POST`   | `/`         | Crea una persona                              |
| `PUT`    | `/{id}`     | Reemplaza `nombres`, `no_pase`, `ci` y `nivel_acceso_id` |
| `PATCH`  | `/{id}`     | Actualiza solo los campos enviados            |
| `DELETE` | `/{id}`     | Elimina una persona                           |

### CRUD de niveles de acceso (`/api/v1/niveles-acceso`)

| Método   | Ruta        | Descripción                                   |
| -------- | ----------- | --------------------------------------------- |
| `GET`    | `/`         | Lista con `skip` y `limit`                    |
| `GET`    | `/{id}`     | Obtiene un nivel de acceso                    |
| `POST`   | `/`         | Crea un nivel de acceso                       |
| `PUT`    | `/{id}`     | Reemplaza el nombre                           |
| `PATCH`  | `/{id}`     | Actualiza parcialmente el nombre              |
| `DELETE` | `/{id}`     | Elimina un nivel sin personas asociadas       |

Códigos de respuesta: `201` al crear, `204` al eliminar, `404` si no existe,
`409` ante duplicados o al eliminar un nivel en uso y `422` ante datos inválidos.

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/v1/personas `
  -H "Content-Type: application/json" `
  -d '{"nombres":"Juan Carlos","no_pase":"123456","nivel_acceso_id":1}'
```

`/health` responde `200` cuando la base de datos responde y `503` cuando no es
alcanzable.

## Migraciones (Alembic)

```powershell
alembic current
alembic revision --autogenerate -m "descripcion"
alembic upgrade head
```

La revisión `0001_initial` es la línea base del proyecto, `0002_personas_nivel_acceso`
agrega las tablas de personas y niveles de acceso, `0003_personas_nombres` agrega el
campo `nombres` a personas y `0004_personas_ci` agrega el campo `ci` (carnet de
identidad) con sus restricciones y datos de ejemplo.

## Tests

```powershell
pytest
```

## Docker

```powershell
docker compose up --build
```

La API queda expuesta en `http://localhost:8000`. PostgreSQL no se contenedoriza:
se usa el servidor externo configurado en `DATABASE_URL`.

## Documentación offline

Los archivos de Swagger UI (`docs/swagger/`) y ReDoc (`docs/redoc/`) están
versionados dentro del proyecto. Se sirven mediante `StaticFiles` en
`/local/static`, por lo que la documentación funciona sin acceso a internet.
