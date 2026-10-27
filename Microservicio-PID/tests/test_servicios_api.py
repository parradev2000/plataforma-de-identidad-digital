from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from tests.helpers import (
    catalogo,
    crear_persona,
    crear_solicitud,
    crear_verificacion,
    usuario_demo,
)


def test_catalogo_sembrado(api_client: TestClient) -> None:
    servicios = catalogo(api_client)

    assert {"RED_PRIVADA", "CORREO_CORPORATIVO", "RED_PUBLICA"} <= set(servicios)
    assert servicios["RED_PRIVADA"]["sistema_destino"] == "AD/LDAP"
    assert servicios["RED_PRIVADA"]["datos_requeridos"] == ["grupo"]
    assert all(
        servicios[codigo]["activo"]
        for codigo in ("RED_PRIVADA", "CORREO_CORPORATIVO", "RED_PUBLICA")
    )


def test_crud_de_servicio(api_client: TestClient) -> None:
    creado = api_client.post(
        "/api/v1/services",
        json={
            "codigo": "VPN_EXTERNA",
            "nombre": "Acceso VPN",
            "descripcion": "Alta de perfil VPN",
            "sistema_destino": "Gateway VPN",
            "datos_requeridos": ["perfil"],
            "sla_horas": 12,
        },
    )
    assert creado.status_code == 201
    servicio = creado.json()
    assert servicio["codigo"] == "VPN_EXTERNA"
    servicio_id = servicio["id"]

    duplicado = api_client.post(
        "/api/v1/services",
        json={
            "codigo": "VPN_EXTERNA",
            "nombre": "Otra VPN",
            "sistema_destino": "Gateway VPN",
        },
    )
    assert duplicado.status_code == 409

    parcheado = api_client.patch(
        f"/api/v1/services/{servicio_id}",
        json={"sla_horas": 6},
    )
    assert parcheado.status_code == 200
    assert parcheado.json()["sla_horas"] == 6

    vacio = api_client.patch(f"/api/v1/services/{servicio_id}", json={})
    assert vacio.status_code == 422

    reemplazado = api_client.put(
        f"/api/v1/services/{servicio_id}",
        json={
            "codigo": "VPN_EXTERNA",
            "nombre": "Acceso VPN remoto",
            "sistema_destino": "Gateway VPN",
        },
    )
    assert reemplazado.status_code == 200
    assert reemplazado.json()["nombre"] == "Acceso VPN remoto"

    eliminado = api_client.delete(f"/api/v1/services/{servicio_id}")
    assert eliminado.status_code == 204
    assert api_client.get(f"/api/v1/services/{servicio_id}").status_code == 404


def test_no_se_puede_eliminar_servicio_en_uso(
    api_client: TestClient,
    db_session: Session,
    soporte_id: int,
) -> None:
    creado = api_client.post(
        "/api/v1/services",
        json={
            "codigo": "TEMPORAL",
            "nombre": "Servicio temporal",
            "sistema_destino": "Sistema temporal",
            "datos_requeridos": [],
        },
    )
    assert creado.status_code == 201
    servicio_id = creado.json()["id"]

    persona = crear_persona(api_client)
    crear_verificacion(api_client, persona["id"])
    crear_solicitud(
        api_client,
        persona["id"],
        [{"servicio_id": servicio_id, "datos": {}}],
    )

    eliminado = api_client.delete(f"/api/v1/services/{servicio_id}")
    assert eliminado.status_code == 409


def test_filtro_por_activo(api_client: TestClient) -> None:
    api_client.post(
        "/api/v1/services",
        json={
            "codigo": "INACTIVO_DEMO",
            "nombre": "Inactivo",
            "sistema_destino": "Ninguno",
            "activo": False,
        },
    )

    activos = api_client.get("/api/v1/services", params={"activo": True})
    inactivos = api_client.get("/api/v1/services", params={"activo": False})

    assert activos.status_code == 200
    assert inactivos.status_code == 200
    assert all(s["activo"] for s in activos.json())
    assert all(not s["activo"] for s in inactivos.json())
    assert len(inactivos.json()) >= 1
