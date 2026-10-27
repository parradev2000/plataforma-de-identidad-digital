from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.core.estados import Rol
from tests.helpers import (
    aprobar,
    catalogo,
    crear_persona,
    crear_solicitud,
    crear_verificacion,
    usuario_demo,
)


def _servicios_completos(client: TestClient) -> list[dict]:
    cat = catalogo(client)
    return [
        {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "ventas"}},
        {
            "servicio_id": cat["CORREO_CORPORATIVO"]["id"],
            "datos": {"cuota_mb": 2048},
        },
        {"servicio_id": cat["RED_PUBLICA"]["id"], "datos": {"perfil": "editor"}},
    ]


def _solicitud_con_verificacion(
    client: TestClient,
    servicios: list[dict],
    *,
    prioridad: str = "NORMAL",
) -> tuple[dict, dict]:
    persona = crear_persona(client)
    verificacion = crear_verificacion(client, persona["id"])
    solicitud = crear_solicitud(
        client, persona["id"], servicios, prioridad=prioridad
    )
    return persona, solicitud


def test_rn01_requiere_verificacion_vigente(api_client: TestClient) -> None:
    persona = crear_persona(api_client)
    cat = catalogo(api_client)

    response = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona["id"],
            "servicios": [
                {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "ti"}}
            ],
        },
    )

    assert response.status_code == 409
    assert "RN-01" in response.json()["detail"]


def test_flujo_completo_de_solicitud(
    api_client: TestClient,
    soporte_id: int,
) -> None:
    persona = crear_persona(api_client)
    verificacion = crear_verificacion(api_client, persona["id"])
    headers = {"X-Correlation-Id": "corr-flujo-1"}

    creada = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona["id"],
            "prioridad": "ALTA",
            "servicios": _servicios_completos(api_client),
        },
        headers=headers,
    )
    assert creada.status_code == 201, creada.text
    solicitud = creada.json()

    assert solicitud["estado"] == "ENVIADA"
    assert solicitud["numero"].startswith("SOL-")
    assert solicitud["verificacion_id"] == verificacion["id"]
    assert solicitud["prioridad"] == "ALTA"
    assert len(solicitud["items"]) == 3
    assert all(item["estado"] == "PENDIENTE" for item in solicitud["items"])

    # Bandeja con filtros (RF-SOL-04)
    bandeja = api_client.get(
        "/api/v1/service-requests",
        params={"estado": "ENVIADA", "prioridad": "ALTA"},
    )
    assert bandeja.status_code == 200
    ids_bandeja = [s["id"] for s in bandeja.json()]
    assert solicitud["id"] in ids_bandeja
    assert all(
        s["estado"] == "ENVIADA" and s["prioridad"] == "ALTA"
        for s in bandeja.json()
    )

    # Soporte toma la solicitud
    tomada = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/assign",
        json={"soporte_id": soporte_id},
    )
    assert tomada.status_code == 200, tomada.text
    assert tomada.json()["estado"] == "EN_REVISION"
    assert tomada.json()["soporte_id"] == soporte_id

    # Doble toma no permitida
    otra_vez = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/assign",
        json={"soporte_id": soporte_id},
    )
    assert otra_vez.status_code == 409

    # Aprobación (RF-SOL-05)
    aprobada = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={"decision": "APROBAR", "soporte_id": soporte_id},
    )
    assert aprobada.status_code == 200, aprobada.text
    assert aprobada.json()["estado"] == "APROBADA"
    assert aprobada.json()["fecha_decision"] is not None

    # Aprovisionamiento (RF-SOL-06)
    provision = api_client.post(
        f"/api/v1/service-requests/{solicitud['id']}/provision",
        json={"soporte_id": soporte_id},
        headers=headers,
    )
    assert provision.status_code == 200, provision.text
    resultado = provision.json()

    assert resultado["solicitud"]["estado"] == "COMPLETADA"
    assert all(item["estado"] == "CREADO" for item in resultado["items"])
    assert len(resultado["credenciales"]) == 1
    credencial = resultado["credenciales"][0]
    assert credencial["sistema"] == "AD/LDAP"
    assert credencial["cambio_clave_obligatorio"] is True
    assert credencial["credencial_temporal"]

    # Consulta con las cuentas creadas
    consultada = api_client.get(
        f"/api/v1/service-requests/{solicitud['id']}"
    ).json()
    assert consultada["estado"] == "COMPLETADA"
    for item in consultada["items"]:
        assert len(item["cuentas"]) == 1
        assert item["cuentas"][0]["identificador"]

    # Auditoría extremo a extremo (RF-AUD-01)
    auditoria = api_client.get(
        "/api/v1/audit",
        params={"entidad": "solicitud", "entidad_id": solicitud["id"]},
    ).json()
    acciones = [evento["accion"] for evento in auditoria]
    assert "CREAR_SOLICITUD" in acciones
    assert "DECISION_APROBADA" in acciones
    assert "PROVISION_FINALIZADA" in acciones
    assert any(evento["correlacion"] == "corr-flujo-1" for evento in auditoria)

    items_audit = api_client.get(
        "/api/v1/audit",
        params={"entidad": "solicitud_item", "accion": "PROVISION_ITEM"},
    ).json()
    creados = [e for e in items_audit if e["resultado"] == "CREADO"]
    assert len(creados) >= 3


def test_rechazo_con_motivo_obligatorio(
    api_client: TestClient,
    soporte_id: int,
) -> None:
    _, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )

    sin_motivo = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={"decision": "RECHAZAR", "soporte_id": soporte_id},
    )
    assert sin_motivo.status_code == 422

    rechazada = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={
            "decision": "RECHAZAR",
            "soporte_id": soporte_id,
            "motivo": "Datos de contacto no verificables",
        },
    )
    assert rechazada.status_code == 200, rechazada.text
    assert rechazada.json()["estado"] == "RECHAZADA"
    assert rechazada.json()["motivo_rechazo"] == "Datos de contacto no verificables"

    auditoria = api_client.get(
        "/api/v1/audit",
        params={"entidad": "solicitud", "accion": "DECISION_RECHAZADA"},
    ).json()
    assert any(e["entidad_id"] == str(solicitud["id"]) for e in auditoria)

    # Una solicitud rechazada no admite nuevas decisiones
    despues = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={"decision": "APROBAR", "soporte_id": soporte_id},
    )
    assert despues.status_code == 409


def test_provision_antes_de_aprobar(
    api_client: TestClient,
    soporte_id: int,
) -> None:
    _, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )

    response = api_client.post(
        f"/api/v1/service-requests/{solicitud['id']}/provision",
        json={"soporte_id": soporte_id},
    )
    assert response.status_code == 409
    assert "estado actual: ENVIADA" in response.json()["detail"]


def test_aprovisionamiento_parcial_y_reintento(
    api_client: TestClient,
    soporte_id: int,
) -> None:
    cat = catalogo(api_client)
    servicios = [
        {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "ti"}},
        {
            "servicio_id": cat["CORREO_CORPORATIVO"]["id"],
            "datos": {"cuota_mb": 1024, "simular_fallo": True},
        },
    ]
    _, solicitud = _solicitud_con_verificacion(api_client, servicios)
    aprobar(api_client, solicitud["id"], soporte_id)

    primera = api_client.post(
        f"/api/v1/service-requests/{solicitud['id']}/provision",
        json={"soporte_id": soporte_id},
    )
    assert primera.status_code == 200, primera.text
    resultado = primera.json()

    # RF-SOL-07: aprovisionamiento parcial
    assert resultado["solicitud"]["estado"] == "COMPLETADA_PARCIAL"
    fallido = [i for i in resultado["items"] if i["estado"] == "FALLIDO"]
    creados = [i for i in resultado["items"] if i["estado"] == "CREADO"]
    assert len(fallido) == 1 and len(creados) == 1
    assert fallido[0]["motivo_fallo"] == "FALLO_SIMULADO_ADAPTADOR"

    # HU-11: reintento del ítem fallido sin duplicar cuentas
    segunda = api_client.post(
        f"/api/v1/service-requests/{solicitud['id']}/provision",
        json={"soporte_id": soporte_id},
    )
    assert segunda.status_code == 200, segunda.text
    assert segunda.json()["solicitud"]["estado"] == "COMPLETADA_PARCIAL"
    assert segunda.json()["credenciales"] == []

    # El reintento no duplica cuentas creadas
    assert len(_cuentas_de_solicitud(api_client, solicitud["id"])) == 1


def _cuentas_de_solicitud(client: TestClient, solicitud_id: int) -> list[dict]:
    cuerpo = client.get(f"/api/v1/service-requests/{solicitud_id}").json()
    return [
        cuenta
        for item in cuerpo["items"]
        for cuenta in item["cuentas"]
    ]


def test_cancelacion_por_solicitante(
    api_client: TestClient,
    db_session: Session,
) -> None:
    persona, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )
    actor = usuario_demo(db_session, persona["id"], Rol.SOLICITANTE)

    cancelada = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/cancel",
        json={"actor_id": actor, "motivo": "Ya no lo necesito"},
    )
    assert cancelada.status_code == 200, cancelada.text
    assert cancelada.json()["estado"] == "CANCELADA"
    assert cancelada.json()["motivo_cancelacion"] == "Ya no lo necesito"

    repetida = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/cancel",
        json={"actor_id": actor, "motivo": "Otra vez"},
    )
    assert repetida.status_code == 409


def test_cancelar_por_soporte_requiere_justificacion(
    api_client: TestClient,
    soporte_id: int,
) -> None:
    _, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )

    sin_justificacion = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/cancel",
        json={"actor_id": soporte_id},
    )
    assert sin_justificacion.status_code == 422

    con_justificacion = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/cancel",
        json={"actor_id": soporte_id, "motivo": "Solicitud duplicada"},
    )
    assert con_justificacion.status_code == 200
    assert con_justificacion.json()["estado"] == "CANCELADA"


def test_no_cancelable_despues_de_aprobar(
    api_client: TestClient,
    db_session: Session,
    soporte_id: int,
) -> None:
    persona, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )
    aprobar(api_client, solicitud["id"], soporte_id)
    actor = usuario_demo(db_session, persona["id"], Rol.SOLICITANTE)

    response = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/cancel",
        json={"actor_id": actor, "motivo": "Cambio de opinión"},
    )
    assert response.status_code == 409


def test_rn11_separacion_de_funciones(
    api_client: TestClient,
    db_session: Session,
) -> None:
    persona, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )
    soporte_propio = usuario_demo(db_session, persona["id"], Rol.SOPORTE)

    response = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={"decision": "APROBAR", "soporte_id": soporte_propio},
    )
    assert response.status_code == 403
    assert "RN-11" in response.json()["detail"]


def test_rol_sin_permiso_no_decide(
    api_client: TestClient,
    admin_id: int,
) -> None:
    _, solicitud = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
    )

    asignar = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/assign",
        json={"soporte_id": admin_id},
    )
    assert asignar.status_code == 403

    decidir = api_client.patch(
        f"/api/v1/service-requests/{solicitud['id']}/decision",
        json={"decision": "APROBAR", "soporte_id": admin_id},
    )
    assert decidir.status_code == 403


def test_validaciones_de_creacion(api_client: TestClient) -> None:
    cat = catalogo(api_client)
    persona = crear_persona(api_client)
    crear_verificacion(api_client, persona["id"])

    # Datos obligatorios del servicio (RF-SOL-03)
    sin_datos = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona["id"],
            "servicios": [
                {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {}}
            ],
        },
    )
    assert sin_datos.status_code == 422
    assert "Faltan datos obligatorios" in sin_datos.json()["detail"]

    # Servicio inexistente
    inexistente = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona["id"],
            "servicios": [{"servicio_id": 999999, "datos": {"grupo": "ti"}}],
        },
    )
    assert inexistente.status_code == 404

    # Servicio repetido en la misma solicitud
    repetido = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": persona["id"],
            "servicios": [
                {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "ti"}},
                {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "otro"}},
            ],
        },
    )
    assert repetido.status_code == 422

    # Sin servicios
    vacia = api_client.post(
        "/api/v1/service-requests",
        json={"persona_id": persona["id"], "servicios": []},
    )
    assert vacia.status_code == 422

    # Persona inexistente
    sin_persona = api_client.post(
        "/api/v1/service-requests",
        json={
            "persona_id": 999999,
            "servicios": [
                {"servicio_id": cat["RED_PRIVADA"]["id"], "datos": {"grupo": "ti"}}
            ],
        },
    )
    assert sin_persona.status_code == 404


def test_bandeja_filtros_por_persona_y_fechas(
    api_client: TestClient,
) -> None:
    persona_a, solicitud_a = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PUBLICA"]["id"],
          "datos": {"perfil": "basico"}}],
        prioridad="ALTA",
    )
    persona_b, solicitud_b = _solicitud_con_verificacion(
        api_client,
        [{"servicio_id": catalogo(api_client)["RED_PRIVADA"]["id"],
          "datos": {"grupo": "comercial"}}],
        prioridad="BAJA",
    )

    por_persona = api_client.get(
        "/api/v1/service-requests",
        params={"persona_id": persona_a["id"]},
    )
    assert solicitud_a["id"] in [s["id"] for s in por_persona.json()]
    assert all(s["persona_id"] == persona_a["id"] for s in por_persona.json())

    por_prioridad = api_client.get(
        "/api/v1/service-requests",
        params={"prioridad": "BAJA"},
    )
    assert solicitud_b["id"] in [s["id"] for s in por_prioridad.json()]
    assert all(s["prioridad"] == "BAJA" for s in por_prioridad.json())

    por_fecha = api_client.get(
        "/api/v1/service-requests",
        params={"desde": "2000-01-01T00:00:00"},
    )
    ids = [s["id"] for s in por_fecha.json()]
    assert solicitud_a["id"] in ids and solicitud_b["id"] in ids

    fuera_de_rango = api_client.get(
        "/api/v1/service-requests",
        params={"hasta": "2000-01-01T00:00:00"},
    )
    ids_fuera = [s["id"] for s in fuera_de_rango.json()]
    assert solicitud_a["id"] not in ids_fuera
    assert solicitud_b["id"] not in ids_fuera

    estado_invalido = api_client.get(
        "/api/v1/service-requests",
        params={"estado": "NO_EXISTE"},
    )
    assert estado_invalido.status_code == 422
