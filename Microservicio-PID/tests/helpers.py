import random

from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.core.estados import Rol


def crear_persona(
    client: TestClient,
    *,
    nombre: str = "Persona De Prueba",
    telefonos: list[str] | None = None,
) -> dict:
    ci = str(random.randint(10**10, 10**11 - 1))
    response = client.post(
        "/api/v1/personas",
        json={
            "ci": ci,
            "nombre": nombre,
            "telefonos": telefonos or [],
        },
    )
    assert response.status_code == 201, response.text
    return response.json()


def crear_verificacion(client: TestClient, persona_id: int) -> dict:
    response = client.post(
        "/api/v1/verifications",
        json={"persona_id": persona_id},
    )
    assert response.status_code == 201, response.text
    return response.json()


def catalogo(client: TestClient) -> dict[str, dict]:
    response = client.get("/api/v1/services")
    assert response.status_code == 200, response.text
    return {servicio["codigo"]: servicio for servicio in response.json()}


def crear_solicitud(
    client: TestClient,
    persona_id: int,
    servicios: list[dict],
    *,
    prioridad: str = "NORMAL",
    headers: dict[str, str] | None = None,
) -> dict:
    response = client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona_id,
            "prioridad": prioridad,
            "servicios": servicios,
        },
        headers=headers or {},
    )
    assert response.status_code == 201, response.text
    return response.json()


def usuario_demo(db: Session, persona_id: int, rol: Rol) -> int:
    from app.models import Usuario

    usuario = Usuario(persona_id=persona_id, rol=rol, estado="ACTIVO")
    db.add(usuario)
    db.commit()
    return usuario.id


def aprobar(client: TestClient, solicitud_id: int, soporte_id: int) -> dict:
    response = client.patch(
        f"/api/v1/service-requests/{solicitud_id}/decision",
        json={"decision": "APROBAR", "soporte_id": soporte_id},
    )
    assert response.status_code == 200, response.text
    return response.json()
