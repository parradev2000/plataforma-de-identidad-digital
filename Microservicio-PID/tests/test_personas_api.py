import random

from fastapi.testclient import TestClient


def _ci() -> str:
    return str(random.randint(10**10, 10**11 - 1))


def test_filtro_por_nombre(api_client: TestClient) -> None:
    nombre = "Zoila Filtro"
    creada = api_client.post(
        "/api/v1/personas",
        json={"ci": _ci(), "nombre": nombre},
    )
    assert creada.status_code == 201

    listado = api_client.get("/api/v1/personas", params={"nombre": "Zoila"})
    assert listado.status_code == 200
    assert [p["nombre"] for p in listado.json()] == [nombre]


def test_listar_y_obtener_personas(api_client: TestClient) -> None:
    ci = _ci()
    creado = api_client.post(
        "/api/v1/personas",
        json={
            "ci": ci,
            "nombre": "Persona Listada",
            "telefonos": ["51234567", "55559876"],
        },
    )
    assert creado.status_code == 201
    cuerpo = creado.json()
    persona_id = cuerpo["id"]
    # Campos eliminados en migraciones anteriores
    assert "tipo_doc" not in cuerpo
    assert "email" not in cuerpo
    assert "empresa_area" not in cuerpo
    assert "numero_doc" not in cuerpo
    assert "estado" not in cuerpo
    # Estado como booleano y teléfonos como nomenclador N:N
    assert cuerpo["activo"] is True
    assert sorted(t["numero"] for t in cuerpo["telefonos"]) == [
        "51234567",
        "55559876",
    ]

    listado = api_client.get("/api/v1/personas", params={"ci": ci})
    assert listado.status_code == 200
    assert [p["id"] for p in listado.json()] == [persona_id]
    assert listado.json()[0]["ci"] == ci

    obtenida = api_client.get(f"/api/v1/personas/{persona_id}")
    assert obtenida.status_code == 200
    assert obtenida.json()["nombre"] == "Persona Listada"

    inexistente = api_client.get("/api/v1/personas/999999")
    assert inexistente.status_code == 404


def test_validaciones_de_persona(api_client: TestClient) -> None:
    casos_ci_invalidos = ["12", "1" * 10, "1" * 12, "1234567890A"]
    for ci in casos_ci_invalidos:
        response = api_client.post(
            "/api/v1/personas",
            json={"ci": ci, "nombre": "Persona Válida"},
        )
        assert response.status_code == 422, f"CI debería ser inválido: {ci}"

    sin_nombre = api_client.post(
        "/api/v1/personas",
        json={"ci": _ci(), "nombre": "   "},
    )
    assert sin_nombre.status_code == 422

    sin_ci = api_client.post(
        "/api/v1/personas",
        json={"nombre": "Persona Válida"},
    )
    assert sin_ci.status_code == 422


def test_activar_desactivar_y_filtro(api_client: TestClient) -> None:
    creada = api_client.post(
        "/api/v1/personas",
        json={"ci": _ci(), "nombre": "Persona Desactivable"},
    )
    assert creada.status_code == 201
    persona_id = creada.json()["id"]
    assert creada.json()["activo"] is True

    desactivada = api_client.patch(
        f"/api/v1/personas/{persona_id}",
        json={"activo": False},
    )
    assert desactivada.status_code == 200
    assert desactivada.json()["activo"] is False

    inactivos = api_client.get("/api/v1/personas", params={"activo": "false"})
    assert persona_id in [p["id"] for p in inactivos.json()]

    activos = api_client.get("/api/v1/personas", params={"activo": "true"})
    assert persona_id not in [p["id"] for p in activos.json()]

    vacio = api_client.patch(f"/api/v1/personas/{persona_id}", json={})
    assert vacio.status_code == 422


def test_ci_de_11_digitos_es_obligatorio(api_client: TestClient) -> None:
    valido = api_client.post(
        "/api/v1/personas",
        json={"ci": "12345678901", "nombre": "Persona CI Once"},
    )
    assert valido.status_code == 201
    assert valido.json()["ci"] == "12345678901"


def test_actualizar_ci(api_client: TestClient) -> None:
    creada = api_client.post(
        "/api/v1/personas",
        json={"ci": _ci(), "nombre": "Persona A Renovar CI"},
    )
    assert creada.status_code == 201, creada.text
    persona_id = creada.json()["id"]
    ci_nuevo = _ci()

    parcheado = api_client.patch(
        f"/api/v1/personas/{persona_id}",
        json={"ci": ci_nuevo},
    )
    assert parcheado.status_code == 200, parcheado.text
    assert parcheado.json()["ci"] == ci_nuevo
    assert parcheado.json()["nombre"] == "Persona A Renovar CI"

    # El cambio queda persistido y es localizable por el CI nuevo
    obtenida = api_client.get(f"/api/v1/personas/{persona_id}")
    assert obtenida.json()["ci"] == ci_nuevo
    por_ci = api_client.get("/api/v1/personas", params={"ci": ci_nuevo})
    assert [p["id"] for p in por_ci.json()] == [persona_id]

    # Formato inválido rechazado con 422
    for invalido in ("12", "1" * 12, "1234567890A"):
        rechazado = api_client.patch(
            f"/api/v1/personas/{persona_id}",
            json={"ci": invalido},
        )
        assert rechazado.status_code == 422, f"CI inválido debería dar 422: {invalido}"

    # No se puede vaciar el CI
    nulo = api_client.patch(f"/api/v1/personas/{persona_id}", json={"ci": None})
    assert nulo.status_code == 422

    # El CI de otra persona es un duplicado -> 409
    ocupado = _ci()
    otra = api_client.post(
        "/api/v1/personas",
        json={"ci": ocupado, "nombre": "Persona Con Ese CI"},
    )
    assert otra.status_code == 201, otra.text
    duplicado = api_client.patch(
        f"/api/v1/personas/{persona_id}",
        json={"ci": ocupado},
    )
    assert duplicado.status_code == 409, duplicado.text
    # La persona conserva su CI anterior
    assert api_client.get(f"/api/v1/personas/{persona_id}").json()["ci"] == ci_nuevo


def test_ci_duplicado(api_client: TestClient) -> None:
    ci = _ci()
    payload = {"ci": ci, "nombre": "Primera"}

    assert api_client.post("/api/v1/personas", json=payload).status_code == 201
    duplicada = api_client.post(
        "/api/v1/personas",
        json={"ci": ci, "nombre": "Segunda"},
    )
    assert duplicada.status_code == 409
