# Microservicio-PID

Microservicio del **Sistema de Administración de Solicitudes** de la Plataforma
de Identidad Digital (PID). Desarrollado con FastAPI y Python 3.11, con
documentación offline (Swagger UI y ReDoc) y conexión síncrona a PostgreSQL
mediante SQLAlchemy.

Implementa el módulo **RF-SOL** de la especificación (ciclo de vida de la
solicitud de servicios y su aprovisionamiento), el catálogo de servicios
(RF-ADM-03, mínimo), auditoría (RF-AUD-01/02) y la verificación de identidad
**solo como requisito previo** (RN-01): el módulo RF-VER completo (orquestación
de proveedores, liveness, pertenencia, acceso) queda fuera de alcance en esta
iteración.

## Requisitos

- Python 3.11+
- PostgreSQL accesible en `10.10.16.250:5432` (base `bd_solicitudes`)
- Docker (opcional)

## Configuración

Las variables se leen desde `.env` (ver `.env.example`):

| Variable                     | Valor por defecto                                             |
| ---------------------------- | ------------------------------------------------------------- |
| `APP_NAME`                   | `Microservicio-PID`                                           |
| `APP_VERSION`                | `0.1.0`                                                       |
| `DEBUG`                      | `false`                                                       |
| `DATABASE_URL`               | `postgresql+psycopg2://postgres:postgres@10.10.16.250:5432/bd_solicitudes` |
| `VERIFICACION_VIGENCIA_DIAS` | `90` (vigencia de la credencial de identidad verificada, RN-10) |
| `CORREO_DOMINIO`             | `empresa.cu` (dominio del adaptador simulado de correo)        |

## Instalación local

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
alembic upgrade head
uvicorn app.main:app --reload
```

Servicio disponible en `http://127.0.0.1:8000`.

## Modelo de datos

| Entidad                  | Tabla                     | Notas                                                          |
| ------------------------ | ------------------------- | -------------------------------------------------------------- |
| `Persona`                | `personas`                | `ci` de 11 dígitos único, `activo` booleano    |
| `Telefono`               | `telefonos`               | Nomenclador; N:N con personas (`persona_telefonos`) |

> Las tablas `usuarios`, `verificaciones`, `servicios`,
> `solicitudes_servicio`, `solicitud_items`, `cuentas_aprovisionadas` y
> `auditoria` fueron eliminadas por la migración `0006_solo_personas_telefonos`;
> la base solo conserva `personas`, `telefonos` y `persona_telefonos`.

### Estados (sección 7 de la especificación)

- **Solicitud:** `ENVIADA → EN_REVISION → APROBADA | RECHAZADA | CANCELADA →
  EN_PROVISION → COMPLETADA | COMPLETADA_PARCIAL | FALLIDA`
- **Ítem:** `PENDIENTE → EN_PROCESO → CREADO | FALLIDO | REINTENTANDO`
- **Verificación:** `APROBADA` (stub) y `EXPIRADA` al vencer la vigencia.

## Endpoints

| Método | Ruta                                    | Descripción                                    |
| ------ | --------------------------------------- | ---------------------------------------------- |
| `GET`  | `/health`                               | Estado del servicio y de PostgreSQL             |
| `GET`  | `/local/docs`, `/local/redoc`            | Documentación offline                          |
| `POST` | `/api/v1/personas`                      | Registra una persona                           |
| `GET`  | `/api/v1/personas`                      | Lista con `skip`, `limit`, `ci`, `nombre`, `activo` |
| `PATCH`| `/api/v1/personas/{id}`                 | Actualiza `activo`, `nombre` o `fecha_nacimiento` |
| `GET`  | `/api/v1/phones`                        | Nomenclador de teléfonos (filtro `numero`)     |
| `POST` | `/api/v1/phones`                        | Registra un teléfono (único)                   |
| `GET`  | `/api/v1/personas/{id}/phones`          | Teléfonos de una persona                       |
| `PUT`  | `/api/v1/personas/{id}/phones`          | Reemplaza los teléfonos (crea los que falten)  |
| `POST` | `/api/v1/personas/{id}/phones/{tel_id}` | Asigna un teléfono existente                   |
| `DELETE`| `/api/v1/personas/{id}/phones/{tel_id}` | Quita el teléfono a la persona                 |
| `GET`  | `/api/v1/personas/{id}`                 | Obtiene una persona                            |
| `DELETE`| `/api/v1/personas/{id}`                | Elimina una persona                            |
| `POST` | `/api/v1/verifications`                 | Inicia verificación (stub APROBADA, ver nota)  |
| `GET`  | `/api/v1/verifications`                 | Lista con `persona_id`, `skip`, `limit`        |
| `GET`  | `/api/v1/verifications/{id}`            | Consulta estado (RF-VER-09)                    |
| `GET`  | `/api/v1/services`                      | Catálogo con filtro `activo`                   |
| `GET`  | `/api/v1/services/{id}`                 | Obtiene un servicio                            |
| `POST` | `/api/v1/services`                      | Crea un servicio (RF-ADM-03)                   |
| `PUT`  | `/api/v1/services/{id}`                 | Reemplaza un servicio                          |
| `PATCH`| `/api/v1/services/{id}`                 | Actualiza parcialmente                         |
| `DELETE`| `/api/v1/services/{id}`                | Elimina (409 si está en uso)                   |
| `POST` | `/api/v1/service-requests`              | Crea solicitud (RF-SOL-03)                     |
| `GET`  | `/api/v1/service-requests`              | Bandeja con `estado`, `prioridad`, `persona_id`, `soporte_id`, `desde`, `hasta` |
| `GET`  | `/api/v1/service-requests/{id}`         | Consulta con items y cuentas (RF-SOL-09)       |
| `PATCH`| `/api/v1/service-requests/{id}/assign`  | Soporte toma la solicitud (RF-SOL-04)          |
| `PATCH`| `/api/v1/service-requests/{id}/decision`| Aprueba/rechaza (RF-SOL-05)                    |
| `POST` | `/api/v1/service-requests/{id}/provision`| Ejecuta el aprovisionamiento (RF-SOL-06/07)    |
| `PATCH`| `/api/v1/service-requests/{id}/cancel`  | Cancela (RF-SOL-08)                            |
| `GET`  | `/api/v1/audit`                         | Auditoría con `entidad`, `entidad_id`, `accion`, `actor`, `desde`, `hasta` |

Códigos de respuesta: `201` al crear, `204` al eliminar, `403` por rol o
separación de funciones, `404` si no existe, `409` ante duplicados o violación
de reglas de negocio, `422` ante datos inválidos o datos obligatorios faltantes.

### Reglas de negocio implementadas

| Regla  | Comportamiento                                                                 |
| ------ | ------------------------------------------------------------------------------ |
| RN-01  | `POST /service-requests` devuelve `409` sin verificación APROBADA y vigente.   |
| RN-06  | Rechazar sin `motivo` devuelve `422` (validación en schema y en servicio).     |
| RN-07  | `assign`, `decision` y `provision` exigen rol SOPORTE o COORDINADOR (`403`).   |
| RN-08  | La credencial temporal solo se devuelve una vez en la respuesta de `provision`; en BD se guarda su hash SHA-256 y se marca `cambio_clave_obligatorio`. |
| RN-10  | `VERIFICACION_VIGENCIA_DIAS` (90 por defecto); al vencer, RN-01 deja de cumplirse y la verificación pasa a `EXPIRADA`. |
| RN-11  | Un usuario cuya `persona_id` coincide con la del solicitante recibe `403` al decidir. |
| RN-12  | Toda mutación relevante genera evento de auditoría con actor, IP y `X-Correlation-Id`. |

### Ejemplo de flujo

```powershell
# 1. Persona + verificación (stub)
curl.exe -X POST http://127.0.0.1:8000/api/v1/personas -H "Content-Type: application/json" `
  -d '{"ci":"99000111222","nombre":"Pedro Prueba","telefonos":["51234567"]}'
curl.exe -X POST http://127.0.0.1:8000/api/v1/verifications -H "Content-Type: application/json" `
  -d '{"persona_id":1}'

# 2. Solicitud con los tres servicios del MVP
curl.exe -X POST http://127.0.0.1:8000/api/v1/service-requests -H "Content-Type: application/json" `
  -H "X-Correlation-Id: demo-1" `
  -d '{"persona_id":1,"prioridad":"ALTA","servicios":[
        {"servicio_id":1,"datos":{"grupo":"ventas"}},
        {"servicio_id":2,"datos":{"cuota_mb":2048}},
        {"servicio_id":3,"datos":{"perfil":"editor"}}]}'

# 3. Soporte toma, decide y aprueba
curl.exe -X PATCH http://127.0.0.1:8000/api/v1/service-requests/1/assign -H "Content-Type: application/json" -d '{"soporte_id":1}'
curl.exe -X PATCH http://127.0.0.1:8000/api/v1/service-requests/1/decision -H "Content-Type: application/json" `
  -d '{"decision":"APROBAR","soporte_id":1}'

# 4. Aprovisionar (devuelve la credencial temporal de red privada una sola vez)
curl.exe -X POST http://127.0.0.1:8000/api/v1/service-requests/1/provision -H "Content-Type: application/json" `
  -d '{"soporte_id":1}'

# 5. Auditoría
curl.exe "http://127.0.0.1:8000/api/v1/audit?entidad=solicitud&entidad_id=1"
```

## Datos semilla (migración `0002`)

Catálogo: `RED_PRIVADA` (AD/LDAP, requiere `grupo`), `CORREO_CORPORATIVO`
(Microsoft 365, requiere `cuota_mb`), `RED_PUBLICA` (Portal web, requiere
`perfil`).

Usuarios demo: `id 1` SOPORTE, `id 2` ADMIN, `id 3` AUDITOR, `id 4`
SOLICITANTE, `id 5` COORDINADOR (este último sin autenticación real: el rol se
recibe en el cuerpo de la petición, ver *Pendientes*).

## Aprovisionamiento simulado

Los adaptadores de `app/services/aprovisionamiento.py` generan los datos que
entregaría cada sistema destino sin contactar sistemas externos:

- **Red privada:** `usuario`, `grupo`, credencial temporal (la única que se
  devuelve).
- **Correo corporativo:** `direccion`, `cuota_mb`, `alias`.
- **Red pública:** `usuario`, `permisos`.

Para simular un fallo (RF-SOL-07 / HU-11) basta con añadir
`"simular_fallo": true` en los datos del ítem; el ítem queda `FALLIDO` con
motivo `FALLO_SIMULADO_ADAPTADOR` y puede reintentarse con el mismo endpoint
`POST .../provision` sin duplicar cuentas ya creadas.

Para conectar un proveedor real basta con sustituir el cuerpo de cada
adaptador manteniendo la firma.

## Migraciones (Alembic)

```powershell
alembic current
alembic revision --autogenerate -m "descripcion"
alembic upgrade head
```

`0001_initial` crea el esquema completo; `0002_datos_iniciales` siembra el
catálogo de servicios y los usuarios demo.

## Tests

```powershell
pytest
```

25 tests cubren: salud/documentación, personas, catálogo, verificación y el
flujo completo de solicitudes (RN-01, RN-06, RN-07, RN-11, aprovisionamiento
parcial y reintento, cancelaciones, filtros de bandeja y auditoría). Cada test
ejecuta dentro de una transacción que se revierte al finalizar.

## Docker

```powershell
docker compose up --build
```

La API queda expuesta en `http://localhost:8001` (8000 dentro del contenedor).
PostgreSQL no se contenedoriza: se usa el servidor externo de `DATABASE_URL`.

## Pendientes conocidos (fuera de alcance en v1)

- **Autenticación y MFA:** el rol del actor se envía en el cuerpo
  (`soporte_id`, `actor_id`) y se valida contra `usuarios`; sustituir por JWT +
  MFA antes de exponer el servicio.
- **Módulo RF-VER:** `POST /api/v1/verifications` crea directamente una
  verificación `APROBADA` (stub) en lugar de orquestar documento, liveness,
  pertenencia y derecho de acceso.
- **Notificaciones (RF-NOT)** y exportación de reportes (RF-AUD-03).
- **Auditoría de lecturas:** hoy solo se auditan mutaciones (RN-12 exige
  auditar también accesos a datos sensibles).
- **Idempotencia y circuit breaker** hacia proveedores externos reales.
