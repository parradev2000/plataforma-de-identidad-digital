from unittest.mock import MagicMock

import pytest
from fastapi.testclient import TestClient
from sqlalchemy.exc import OperationalError

from app.db.session import get_db
from app.main import app


@pytest.fixture
def client() -> TestClient:
    with TestClient(app) as test_client:
        yield test_client


def test_root(client: TestClient) -> None:
    response = client.get("/")

    assert response.status_code == 200
    assert response.json()["docs"] == "/local/docs"
    assert response.json()["redoc"] == "/local/redoc"


def test_swagger_ui_uses_local_assets(client: TestClient) -> None:
    response = client.get("/local/docs")

    assert response.status_code == 200
    assert "/local/static/swagger/swagger-ui-bundle.js" in response.text
    assert "/local/static/swagger/swagger-ui.css" in response.text
    assert "cdn.jsdelivr.net" not in response.text


def test_redoc_uses_local_assets(client: TestClient) -> None:
    response = client.get("/local/redoc")

    assert response.status_code == 200
    assert "/local/static/redoc/redoc.standalone.js" in response.text
    assert "cdn.jsdelivr.net" not in response.text
    assert "fonts.googleapis.com" not in response.text


def test_openapi_schema(client: TestClient) -> None:
    response = client.get("/local/openapi.json")

    assert response.status_code == 200
    schema = response.json()
    assert schema["info"]["title"] == app.title
    assert "/health" in schema["paths"]


def test_health_ok(client: TestClient) -> None:
    app.dependency_overrides[get_db] = lambda: MagicMock()

    try:
        response = client.get("/health")
    finally:
        app.dependency_overrides.clear()

    assert response.status_code == 200
    assert response.json() == {"status": "ok", "database": "connected"}


def test_health_degraded(client: TestClient) -> None:
    db = MagicMock()
    db.execute.side_effect = OperationalError("SELECT 1", {}, Exception("down"))
    app.dependency_overrides[get_db] = lambda: db

    try:
        response = client.get("/health")
    finally:
        app.dependency_overrides.clear()

    assert response.status_code == 503
    assert response.json() == {"status": "degraded", "database": "unreachable"}
