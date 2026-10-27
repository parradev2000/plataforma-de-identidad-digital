from datetime import datetime, timedelta

from fastapi.testclient import TestClient

from app.core.config import settings
from tests.helpers import crear_persona, crear_verificacion


def test_crear_verificacion_aprobada(api_client: TestClient) -> None:
    persona = crear_persona(api_client)
    verificacion = crear_verificacion(api_client, persona["id"])

    assert verificacion["estado"] == "APROBADA"
    assert verificacion["persona_id"] == persona["id"]
    assert verificacion["token_credencial"]
    assert len(verificacion["token_credencial"]) == 64

    vigencia = datetime.fromisoformat(verificacion["vigencia_hasta"])
    esperada = datetime.now() + timedelta(days=settings.verificacion_vigencia_dias)
    assert abs((vigencia - esperada).total_seconds()) < 60


def test_consulta_y_filtro_por_persona(api_client: TestClient) -> None:
    persona = crear_persona(api_client)
    verificacion = crear_verificacion(api_client, persona["id"])

    obtenida = api_client.get(f"/api/v1/verifications/{verificacion['id']}")
    assert obtenida.status_code == 200
    assert obtenida.json()["id"] == verificacion["id"]

    filtrada = api_client.get(
        "/api/v1/verifications",
        params={"persona_id": persona["id"]},
    )
    assert filtrada.status_code == 200
    assert [v["id"] for v in filtrada.json()] == [verificacion["id"]]

    assert api_client.get("/api/v1/verifications/999999").status_code == 404


def test_persona_inexistente(api_client: TestClient) -> None:
    response = api_client.post("/api/v1/verifications", json={"persona_id": 999999})
    assert response.status_code == 404
