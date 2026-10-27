import random

from fastapi.testclient import TestClient

from tests.helpers import crear_persona


def _numero() -> str:
    return str(random.randint(10**7, 10**8 - 1))


def _id_por_numero(client: TestClient, numero: str) -> int:
    response = client.get("/api/v1/phones", params={"numero": numero})
    assert response.status_code == 200, response.text
    telefonos = response.json()
    assert len(telefonos) == 1, f"Se esperaba un único teléfono {numero}"
    return telefonos[0]["id"]


def test_nomenclador_crear_duplicado_y_validacion(api_client: TestClient) -> None:
    numero = _numero()

    creado = api_client.post("/api/v1/phones", json={"numero": numero})
    assert creado.status_code == 201, creado.text
    assert creado.json()["numero"] == numero

    duplicado = api_client.post("/api/v1/phones", json={"numero": numero})
    assert duplicado.status_code == 409

    for invalido in ("1234", "1" * 16, "5123AB67"):
        response = api_client.post("/api/v1/phones", json={"numero": invalido})
        assert response.status_code == 422, f"Teléfono debería ser inválido: {invalido}"

    listado = api_client.get("/api/v1/phones", params={"numero": numero})
    assert listado.status_code == 200
    assert [t["numero"] for t in listado.json()] == [numero]


def test_un_telefono_puede_pertenecer_a_varias_personas(api_client: TestClient) -> None:
    numero = _numero()

    persona_a = crear_persona(api_client, nombre="Persona A", telefonos=[numero])
    persona_b = crear_persona(api_client, nombre="Persona B", telefonos=[numero])

    phones_a = api_client.get(f"/api/v1/personas/{persona_a['id']}/phones").json()
    phones_b = api_client.get(f"/api/v1/personas/{persona_b['id']}/phones").json()

    # Relación N:N: mismo teléfono, dos personas
    assert [t["numero"] for t in phones_a] == [numero]
    assert [t["numero"] for t in phones_b] == [numero]
    assert phones_a[0]["id"] == phones_b[0]["id"]

    # El nomenclador no duplica el número
    assert len(api_client.get("/api/v1/phones", params={"numero": numero}).json()) == 1


def test_reemplazar_asignar_y_quitar_telefonos(api_client: TestClient) -> None:
    persona = crear_persona(api_client)
    persona_id = persona["id"]
    numero_a, numero_b = _numero(), _numero()

    reemplazados = api_client.put(
        f"/api/v1/personas/{persona_id}/phones",
        json={"telefonos": [numero_a, numero_b]},
    )
    assert reemplazados.status_code == 200, reemplazados.text
    assert sorted(t["numero"] for t in reemplazados.json()["telefonos"]) == sorted(
        [numero_a, numero_b]
    )

    # Reemplazo del conjunto completo
    solo_b = api_client.put(
        f"/api/v1/personas/{persona_id}/phones",
        json={"telefonos": [numero_b]},
    )
    assert solo_b.status_code == 200
    assert [t["numero"] for t in solo_b.json()["telefonos"]] == [numero_b]

    # Asignación de un teléfono existente del nomenclador
    telefono_a_id = _id_por_numero(api_client, numero_a)
    agregado = api_client.post(
        f"/api/v1/personas/{persona_id}/phones/{telefono_a_id}"
    )
    assert agregado.status_code == 200
    assert numero_a in [t["numero"] for t in agregado.json()["telefonos"]]

    # Quitar el teléfono de la persona
    quitado = api_client.delete(
        f"/api/v1/personas/{persona_id}/phones/{telefono_a_id}"
    )
    assert quitado.status_code == 204
    restantes = api_client.get(f"/api/v1/personas/{persona_id}/phones").json()
    assert [t["numero"] for t in restantes] == [numero_b]

    # Quitar dos veces → 404 (ya no está asignado)
    otra_vez = api_client.delete(
        f"/api/v1/personas/{persona_id}/phones/{telefono_a_id}"
    )
    assert otra_vez.status_code == 404

    # El nomenclador conserva ambos números (no se borran al desasignar)
    assert len(api_client.get("/api/v1/phones", params={"numero": numero_a}).json()) == 1
    assert len(api_client.get("/api/v1/phones", params={"numero": numero_b}).json()) == 1


def test_persona_inexistente(api_client: TestClient) -> None:
    response = api_client.get("/api/v1/personas/999999/phones")
    assert response.status_code == 404
