from fastapi.testclient import TestClient
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import NivelAcceso, Persona


def _first_persona(db: Session) -> Persona:
    persona = db.scalars(select(Persona).order_by(Persona.id)).first()
    assert persona is not None
    return persona


def _first_nivel_id(db: Session) -> int:
    nivel_id = db.scalar(select(NivelAcceso.id).order_by(NivelAcceso.id))
    assert nivel_id is not None
    return nivel_id


def _free_no_pase(db: Session) -> str:
    used = set(db.scalars(select(Persona.no_pase)).all())
    for value in range(1_000_000):
        candidate = f"{value:06d}"
        if candidate not in used:
            return candidate
    raise AssertionError("No quedan números de pase libres para el test")


def test_list_personas(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/personas")

    assert response.status_code == 200
    assert len(response.json()) >= 20


def test_list_personas_with_filters_and_pagination(
    api_client: TestClient,
    db_session: Session,
) -> None:
    persona = _first_persona(db_session)

    filtered = api_client.get(
        "/api/v1/personas",
        params={"no_pase": persona.no_pase},
    )
    assert filtered.status_code == 200
    assert [item["id"] for item in filtered.json()] == [persona.id]

    paginated = api_client.get(
        "/api/v1/personas",
        params={"skip": 1, "limit": 2},
    )
    assert paginated.status_code == 200
    assert len(paginated.json()) == 2


def test_get_persona_and_404(api_client: TestClient, db_session: Session) -> None:
    persona = _first_persona(db_session)

    found = api_client.get(f"/api/v1/personas/{persona.id}")
    assert found.status_code == 200
    assert found.json()["no_pase"] == persona.no_pase

    missing = api_client.get("/api/v1/personas/999999")
    assert missing.status_code == 404


def test_create_update_and_delete_persona(
    api_client: TestClient,
    db_session: Session,
) -> None:
    nivel_id = _first_nivel_id(db_session)
    no_pase = _free_no_pase(db_session)

    created = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "Persona de Prueba",
            "no_pase": no_pase,
            "nivel_acceso_id": nivel_id,
        },
    )
    assert created.status_code == 201
    persona = created.json()
    assert persona["nombres"] == "Persona de Prueba"
    assert persona["no_pase"] == no_pase
    assert persona["nivel_acceso_id"] == nivel_id
    persona_id = persona["id"]

    patched = api_client.patch(
        f"/api/v1/personas/{persona_id}",
        json={
            "nombres": "Persona Actualizada",
            "no_pase": _free_no_pase(db_session),
        },
    )
    assert patched.status_code == 200
    assert patched.json()["nombres"] == "Persona Actualizada"
    assert patched.json()["no_pase"] != no_pase

    replaced = api_client.put(
        f"/api/v1/personas/{persona_id}",
        json={
            "nombres": "Persona Reemplazada",
            "no_pase": _free_no_pase(db_session),
            "nivel_acceso_id": nivel_id,
        },
    )
    assert replaced.status_code == 200
    assert replaced.json()["id"] == persona_id
    assert replaced.json()["nombres"] == "Persona Reemplazada"

    deleted = api_client.delete(f"/api/v1/personas/{persona_id}")
    assert deleted.status_code == 204

    missing = api_client.get(f"/api/v1/personas/{persona_id}")
    assert missing.status_code == 404


def test_create_persona_validation(
    api_client: TestClient,
    db_session: Session,
) -> None:
    nivel_id = _first_nivel_id(db_session)

    invalid_format = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "Persona Válida",
            "no_pase": "12AB",
            "nivel_acceso_id": nivel_id,
        },
    )
    assert invalid_format.status_code == 422

    too_long = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "Persona Válida",
            "no_pase": "1234567",
            "nivel_acceso_id": nivel_id,
        },
    )
    assert too_long.status_code == 422

    empty_nombres = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "   ",
            "no_pase": _free_no_pase(db_session),
            "nivel_acceso_id": nivel_id,
        },
    )
    assert empty_nombres.status_code == 422

    too_long_nombres = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "x" * 151,
            "no_pase": _free_no_pase(db_session),
            "nivel_acceso_id": nivel_id,
        },
    )
    assert too_long_nombres.status_code == 422

    missing_nivel = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "Persona Válida",
            "no_pase": _free_no_pase(db_session),
            "nivel_acceso_id": 999999,
        },
    )
    assert missing_nivel.status_code == 404


def test_duplicate_no_pase(
    api_client: TestClient,
    db_session: Session,
) -> None:
    persona = _first_persona(db_session)

    response = api_client.post(
        "/api/v1/personas",
        json={
            "nombres": "Otra Persona",
            "no_pase": persona.no_pase,
            "nivel_acceso_id": persona.nivel_acceso_id,
        },
    )
    assert response.status_code == 409


def test_empty_patch_and_missing_persona(
    api_client: TestClient,
    db_session: Session,
) -> None:
    persona = _first_persona(db_session)

    empty_patch = api_client.patch(
        f"/api/v1/personas/{persona.id}",
        json={},
    )
    assert empty_patch.status_code == 422

    missing_patch = api_client.patch(
        "/api/v1/personas/999999",
        json={"no_pase": _free_no_pase(db_session)},
    )
    assert missing_patch.status_code == 404

    missing_delete = api_client.delete("/api/v1/personas/999999")
    assert missing_delete.status_code == 404
