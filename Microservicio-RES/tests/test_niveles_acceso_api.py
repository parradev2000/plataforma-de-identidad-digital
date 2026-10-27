from uuid import uuid4

from fastapi.testclient import TestClient
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import NivelAcceso, Persona


def _first_nivel(db: Session) -> NivelAcceso:
    nivel = db.scalars(select(NivelAcceso).order_by(NivelAcceso.id)).first()
    assert nivel is not None
    return nivel


def _unique_name() -> str:
    return f"Nivel {uuid4().hex[:8]}"


def test_list_niveles(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/niveles-acceso")

    assert response.status_code == 200
    assert len(response.json()) >= 4


def test_get_nivel_and_404(api_client: TestClient, db_session: Session) -> None:
    nivel = _first_nivel(db_session)

    found = api_client.get(f"/api/v1/niveles-acceso/{nivel.id}")
    assert found.status_code == 200
    assert found.json()["nombre"] == nivel.nombre

    missing = api_client.get("/api/v1/niveles-acceso/999999")
    assert missing.status_code == 404


def test_create_update_and_delete_nivel(api_client: TestClient) -> None:
    nombre = _unique_name()

    created = api_client.post(
        "/api/v1/niveles-acceso",
        json={"nombre": nombre},
    )
    assert created.status_code == 201
    nivel = created.json()
    assert nivel["nombre"] == nombre
    nivel_id = nivel["id"]

    patched = api_client.patch(
        f"/api/v1/niveles-acceso/{nivel_id}",
        json={"nombre": _unique_name()},
    )
    assert patched.status_code == 200
    assert patched.json()["nombre"] != nombre

    replaced = api_client.put(
        f"/api/v1/niveles-acceso/{nivel_id}",
        json={"nombre": _unique_name()},
    )
    assert replaced.status_code == 200
    assert replaced.json()["id"] == nivel_id

    deleted = api_client.delete(f"/api/v1/niveles-acceso/{nivel_id}")
    assert deleted.status_code == 204

    missing = api_client.get(f"/api/v1/niveles-acceso/{nivel_id}")
    assert missing.status_code == 404


def test_nivel_validation_and_duplicates(
    api_client: TestClient,
    db_session: Session,
) -> None:
    nivel = _first_nivel(db_session)

    duplicate = api_client.post(
        "/api/v1/niveles-acceso",
        json={"nombre": nivel.nombre},
    )
    assert duplicate.status_code == 409

    empty_name = api_client.post(
        "/api/v1/niveles-acceso",
        json={"nombre": ""},
    )
    assert empty_name.status_code == 422

    too_long = api_client.post(
        "/api/v1/niveles-acceso",
        json={"nombre": "x" * 51},
    )
    assert too_long.status_code == 422

    empty_patch = api_client.patch(
        f"/api/v1/niveles-acceso/{nivel.id}",
        json={},
    )
    assert empty_patch.status_code == 422


def test_delete_nivel_in_use(
    api_client: TestClient,
    db_session: Session,
) -> None:
    nivel_id = db_session.scalar(
        select(Persona.nivel_acceso_id).order_by(Persona.id)
    )
    assert nivel_id is not None

    response = api_client.delete(f"/api/v1/niveles-acceso/{nivel_id}")
    assert response.status_code == 409

    still_there = api_client.get(f"/api/v1/niveles-acceso/{nivel_id}")
    assert still_there.status_code == 200


def test_missing_nivel_operations(api_client: TestClient) -> None:
    missing_patch = api_client.patch(
        "/api/v1/niveles-acceso/999999",
        json={"nombre": _unique_name()},
    )
    assert missing_patch.status_code == 404

    missing_delete = api_client.delete("/api/v1/niveles-acceso/999999")
    assert missing_delete.status_code == 404
