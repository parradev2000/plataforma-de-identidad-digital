from fastapi.testclient import TestClient


def test_health_ok(api_client: TestClient) -> None:
    response = api_client.get("/health")

    assert response.status_code == 200
    assert response.json() == {"status": "ok", "database": "connected"}


def test_root_informa_documentacion(api_client: TestClient) -> None:
    response = api_client.get("/")

    assert response.status_code == 200
    body = response.json()
    assert body["service"] == "Microservicio-PID"
    assert body["docs"] == "/local/docs"


def test_documentacion_offline_disponible(api_client: TestClient) -> None:
    swagger = api_client.get("/local/docs")
    redoc = api_client.get("/local/redoc")
    openapi = api_client.get("/local/openapi.json")

    assert swagger.status_code == 200
    assert redoc.status_code == 200
    assert openapi.status_code == 200
    rutas = openapi.json()["paths"]
    assert "/api/v1/service-requests" in rutas
    assert "/api/v1/verifications" in rutas
    assert "/api/v1/audit" in rutas
