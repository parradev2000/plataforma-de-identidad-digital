from fastapi.testclient import TestClient

from app.core.estados import Rol


def test_listar_usuarios(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/users")

    assert response.status_code == 200
    usuarios = response.json()
    assert len(usuarios) >= 4

    por_rol = {usuario["rol"] for usuario in usuarios}
    assert {Rol.SOPORTE, Rol.ADMIN, Rol.AUDITOR, Rol.SOLICITANTE} <= por_rol

    for usuario in usuarios:
        assert usuario["nombre"]
        assert usuario["persona_id"] > 0


def test_filtro_por_rol(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/users", params={"rol": "SOPORTE"})

    assert response.status_code == 200
    usuarios = response.json()
    assert len(usuarios) >= 1
    assert all(u["rol"] == "SOPORTE" for u in usuarios)


def test_rol_invalido_devuelve_422(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/users", params={"rol": "NO_EXISTE"})

    assert response.status_code == 422
