"""datos iniciales: catálogo de servicios y usuarios demo

Revision ID: 0002_datos_iniciales
Revises: 0001_initial
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0002_datos_iniciales"
down_revision: str | None = "0001_initial"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None

SERVICIOS = [
    {
        "id": 1,
        "codigo": "RED_PRIVADA",
        "nombre": "Red privada (dominio interno)",
        "descripcion": "Alta de usuario en el dominio/sistema interno de la empresa.",
        "sistema_destino": "AD/LDAP",
        "requisitos": "Indicar el grupo o rol a asignar en el dominio.",
        "datos_requeridos": ["grupo"],
        "sla_horas": 8,
        "activo": True,
    },
    {
        "id": 2,
        "codigo": "CORREO_CORPORATIVO",
        "nombre": "Correo corporativo",
        "descripcion": "Alta de buzón en el servicio de correo de la empresa.",
        "sistema_destino": "Microsoft 365",
        "requisitos": "Indicar la cuota del buzón en MB.",
        "datos_requeridos": ["cuota_mb"],
        "sla_horas": 4,
        "activo": True,
    },
    {
        "id": 3,
        "codigo": "RED_PUBLICA",
        "nombre": "Red pública (portal externo)",
        "descripcion": "Alta de usuario en el portal/servicio externo.",
        "sistema_destino": "Portal web",
        "requisitos": "Indicar el perfil de acceso (basico, editor, admin).",
        "datos_requeridos": ["perfil"],
        "sla_horas": 24,
        "activo": True,
    },
]

PERSONAS = [
    {
        "id": 1,
        "tipo_doc": "CI",
        "numero_doc": "90010101234",
        "nombre": "Maria Soporte Perez",
        "email": "maria.soporte@empresa.cu",
        "empresa_area": "Soporte TI",
        "estado": "ACTIVA",
    },
    {
        "id": 2,
        "tipo_doc": "CI",
        "numero_doc": "91020202345",
        "nombre": "Carlos Admin Lopez",
        "email": "carlos.admin@empresa.cu",
        "empresa_area": "Direccion de TI",
        "estado": "ACTIVA",
    },
    {
        "id": 3,
        "tipo_doc": "CI",
        "numero_doc": "92030303456",
        "nombre": "Ana Auditora Ruiz",
        "email": "ana.auditora@empresa.cu",
        "empresa_area": "Auditoria",
        "estado": "ACTIVA",
    },
    {
        "id": 4,
        "tipo_doc": "CI",
        "numero_doc": "93040404567",
        "nombre": "Juan Solicitante Diaz",
        "email": "juan.solicitante@empresa.cu",
        "empresa_area": "Comercial",
        "estado": "ACTIVA",
    },
]

USUARIOS = [
    {"id": 1, "persona_id": 1, "rol": "SOPORTE", "estado": "ACTIVO"},
    {"id": 2, "persona_id": 2, "rol": "ADMIN", "estado": "ACTIVO"},
    {"id": 3, "persona_id": 3, "rol": "AUDITOR", "estado": "ACTIVO"},
    {"id": 4, "persona_id": 4, "rol": "SOLICITANTE", "estado": "ACTIVO"},
    {"id": 5, "persona_id": 1, "rol": "COORDINADOR", "estado": "ACTIVO"},
]


def _reset_sequence(tabla: str) -> None:
    op.execute(
        "SELECT setval("
        f"pg_get_serial_sequence('{tabla}', 'id'), "
        f"(SELECT MAX(id) FROM {tabla}))"
    )


def upgrade() -> None:
    servicios = sa.table(
        "servicios",
        sa.column("id", sa.Integer()),
        sa.column("codigo", sa.String()),
        sa.column("nombre", sa.String()),
        sa.column("descripcion", sa.String()),
        sa.column("sistema_destino", sa.String()),
        sa.column("requisitos", sa.String()),
        sa.column("datos_requeridos", sa.JSON()),
        sa.column("sla_horas", sa.Integer()),
        sa.column("activo", sa.Boolean()),
    )
    personas = sa.table(
        "personas",
        sa.column("id", sa.Integer()),
        sa.column("tipo_doc", sa.String()),
        sa.column("numero_doc", sa.String()),
        sa.column("nombre", sa.String()),
        sa.column("email", sa.String()),
        sa.column("empresa_area", sa.String()),
        sa.column("estado", sa.String()),
    )
    usuarios = sa.table(
        "usuarios",
        sa.column("id", sa.Integer()),
        sa.column("persona_id", sa.Integer()),
        sa.column("rol", sa.String()),
        sa.column("estado", sa.String()),
    )

    op.bulk_insert(servicios, SERVICIOS)
    op.bulk_insert(personas, PERSONAS)
    op.bulk_insert(usuarios, USUARIOS)

    _reset_sequence("servicios")
    _reset_sequence("personas")
    _reset_sequence("usuarios")


def downgrade() -> None:
    op.execute("DELETE FROM usuarios WHERE id IN (1, 2, 3, 4, 5)")
    op.execute("DELETE FROM personas WHERE id IN (1, 2, 3, 4)")
    op.execute("DELETE FROM servicios WHERE id IN (1, 2, 3)")
