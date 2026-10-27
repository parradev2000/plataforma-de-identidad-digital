import random

from fastapi.testclient import TestClient
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models import Persona


def _nuevo_no_pase() -> str:
    return f"{random.randint(100000, 999999):06d}"


def _payload(**extra: object) -> dict:
    datos: dict = {
        "nombres": "Persona Con CI",
        "no_pase": _nuevo_no_pase(),
        "nivel_acceso_id": 1,
    }
    datos.update(extra)
    return datos


def _ci_sembrado(db: Session) -> str:
    ci = db.scalar(select(Persona.ci).where(Persona.ci.is_not(None)).limit(1))
    assert ci is not None, "Las migraciones deberían haber rellenado los CI"
    return ci


def test_crear_filtrar_y_consultar_por_ci(
    api_client: TestClient,
    db_session: Session,
) -> None:
    ci = f"{random.randint(10**10, 10**11 - 1):011d}"

    creado = api_client.post("/api/v1/personas", json=_payload(ci=ci))
    assert creado.status_code == 201, creado.text
    persona = creado.json()
    assert persona["ci"] == ci
    persona_id = persona["id"]

    filtrada = api_client.get("/api/v1/personas", params={"ci": ci})
    assert filtrada.status_code == 200
    assert [p["id"] for p in filtrada.json()] == [persona_id]
    assert filtrada.json()[0]["ci"] == ci

    obtenida = api_client.get(f"/api/v1/personas/{persona_id}")
    assert obtenida.status_code == 200
    assert obtenida.json()["ci"] == ci


def test_ci_es_opcional(api_client: TestClient) -> None:
    creado = api_client.post("/api/v1/personas", json=_payload())

    assert creado.status_code == 201, creado.text
    assert creado.json()["ci"] is None


def test_ci_duplicado(api_client: TestClient, db_session: Session) -> None:
    ci = _ci_sembrado(db_session)

    response = api_client.post("/api/v1/personas", json=_payload(ci=ci))

    assert response.status_code == 409
    assert response.json()["detail"] == "El CI ya existe"


def test_ci_con_formato_invalido(api_client: TestClient) -> None:
    con_letras = api_client.post("/api/v1/personas", json=_payload(ci="12A4567890"))
    assert con_letras.status_code == 422

    demasiado_largo = api_client.post(
        "/api/v1/personas",
        json=_payload(ci="123456789012"),
    )
    assert demasiado_largo.status_code == 422

    filtro_invalido = api_client.get("/api/v1/personas", params={"ci": "ABC"})
    assert filtro_invalido.status_code == 422


def test_patch_y_put_actualizan_el_ci(api_client: TestClient) -> None:
    creado = api_client.post("/api/v1/personas", json=_payload())
    assert creado.status_code == 201, creado.text
    persona = creado.json()

    ci_nuevo = f"{random.randint(10**10, 10**11 - 1):011d}"
    parcheado = api_client.patch(
        f"/api/v1/personas/{persona['id']}",
        json={"ci": ci_nuevo},
    )
    assert parcheado.status_code == 200
    assert parcheado.json()["ci"] == ci_nuevo

    ci_otro = f"{random.randint(10**10, 10**11 - 1):011d}"
    reemplazado = api_client.put(
        f"/api/v1/personas/{persona['id']}",
        json=_payload(ci=ci_otro),
    )
    assert reemplazado.status_code == 200
    assert reemplazado.json()["ci"] == ci_otro


def test_listado_incluye_ci_sembrado(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/personas", params={"limit": 100})

    assert response.status_code == 200
    personas = response.json()
    assert len(personas) >= 20
    # El backfill de la migración 0004 rellena el CI de las 20 sembradas
    assert all(p["ci"] and len(p["ci"]) == 11 for p in personas)
