"""esquema inicial del sistema de administración de solicitudes

Revision ID: 0001_initial
Revises:
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0001_initial"
down_revision: str | None = None
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None


def upgrade() -> None:
    op.create_table(
        "personas",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("tipo_doc", sa.String(length=20), nullable=False),
        sa.Column("numero_doc", sa.String(length=30), nullable=False),
        sa.Column("nombre", sa.String(length=150), nullable=False),
        sa.Column("fecha_nacimiento", sa.Date(), nullable=True),
        sa.Column("email", sa.String(length=120), nullable=True),
        sa.Column("telefono", sa.String(length=30), nullable=True),
        sa.Column("empresa_area", sa.String(length=100), nullable=True),
        sa.Column("estado", sa.String(length=20), nullable=False),
        sa.CheckConstraint(
            "length(btrim(nombre)) > 0",
            name="ck_personas_nombre_not_blank",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("tipo_doc", "numero_doc", name="uq_personas_documento"),
    )

    op.create_table(
        "usuarios",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("persona_id", sa.Integer(), nullable=False),
        sa.Column("rol", sa.String(length=20), nullable=False),
        sa.Column("estado", sa.String(length=20), nullable=False),
        sa.Column("ultimo_acceso", sa.DateTime(), nullable=True),
        sa.ForeignKeyConstraint(
            ["persona_id"],
            ["personas.id"],
            name="fk_usuarios_persona",
            ondelete="RESTRICT",
        ),
        sa.PrimaryKeyConstraint("id"),
    )
    op.create_index("ix_usuarios_persona_id", "usuarios", ["persona_id"])

    op.create_table(
        "verificaciones",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("persona_id", sa.Integer(), nullable=False),
        sa.Column("estado", sa.String(length=30), nullable=False),
        sa.Column("score", sa.Numeric(precision=5, scale=2), nullable=True),
        sa.Column("fecha_inicio", sa.DateTime(), nullable=False),
        sa.Column("fecha_fin", sa.DateTime(), nullable=True),
        sa.Column("motivo", sa.String(length=255), nullable=True),
        sa.Column("token_credencial", sa.String(length=64), nullable=True),
        sa.Column("vigencia_hasta", sa.DateTime(), nullable=True),
        sa.ForeignKeyConstraint(
            ["persona_id"],
            ["personas.id"],
            name="fk_verificaciones_persona",
            ondelete="RESTRICT",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("token_credencial"),
    )
    op.create_index(
        "ix_verificaciones_persona_id", "verificaciones", ["persona_id"]
    )

    op.create_table(
        "servicios",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("codigo", sa.String(length=30), nullable=False),
        sa.Column("nombre", sa.String(length=100), nullable=False),
        sa.Column("descripcion", sa.Text(), nullable=False),
        sa.Column("sistema_destino", sa.String(length=100), nullable=False),
        sa.Column("requisitos", sa.Text(), nullable=True),
        sa.Column("datos_requeridos", sa.JSON(), nullable=False),
        sa.Column("sla_horas", sa.Integer(), nullable=False),
        sa.Column("activo", sa.Boolean(), nullable=False),
        sa.CheckConstraint(
            "length(btrim(nombre)) > 0",
            name="ck_servicios_nombre_not_blank",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("codigo", name="uq_servicios_codigo"),
    )

    op.create_table(
        "solicitudes_servicio",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("numero", sa.String(length=20), nullable=False),
        sa.Column("persona_id", sa.Integer(), nullable=False),
        sa.Column("verificacion_id", sa.Integer(), nullable=False),
        sa.Column("estado", sa.String(length=30), nullable=False),
        sa.Column("prioridad", sa.String(length=10), nullable=False),
        sa.Column("fecha_creacion", sa.DateTime(), nullable=False),
        sa.Column("fecha_decision", sa.DateTime(), nullable=True),
        sa.Column("soporte_id", sa.Integer(), nullable=True),
        sa.Column("motivo_rechazo", sa.Text(), nullable=True),
        sa.Column("motivo_cancelacion", sa.Text(), nullable=True),
        sa.ForeignKeyConstraint(
            ["persona_id"],
            ["personas.id"],
            name="fk_solicitudes_persona",
            ondelete="RESTRICT",
        ),
        sa.ForeignKeyConstraint(
            ["soporte_id"],
            ["usuarios.id"],
            name="fk_solicitudes_soporte",
            ondelete="SET NULL",
        ),
        sa.ForeignKeyConstraint(
            ["verificacion_id"],
            ["verificaciones.id"],
            name="fk_solicitudes_verificacion",
            ondelete="RESTRICT",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("numero", name="uq_solicitudes_numero"),
    )
    op.create_index(
        "ix_solicitudes_servicio_persona_id", "solicitudes_servicio", ["persona_id"]
    )
    op.create_index(
        "ix_solicitudes_servicio_verificacion_id",
        "solicitudes_servicio",
        ["verificacion_id"],
    )
    op.create_index(
        "ix_solicitudes_servicio_soporte_id", "solicitudes_servicio", ["soporte_id"]
    )

    op.create_table(
        "solicitud_items",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("solicitud_id", sa.Integer(), nullable=False),
        sa.Column("servicio_id", sa.Integer(), nullable=False),
        sa.Column("estado", sa.String(length=20), nullable=False),
        sa.Column("datos_especificos", sa.JSON(), nullable=False),
        sa.Column("motivo_fallo", sa.String(length=255), nullable=True),
        sa.ForeignKeyConstraint(
            ["servicio_id"],
            ["servicios.id"],
            name="fk_items_servicio",
            ondelete="RESTRICT",
        ),
        sa.ForeignKeyConstraint(
            ["solicitud_id"],
            ["solicitudes_servicio.id"],
            name="fk_items_solicitud",
            ondelete="CASCADE",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint(
            "solicitud_id", "servicio_id", name="uq_items_solicitud_servicio"
        ),
    )
    op.create_index("ix_solicitud_items_solicitud_id", "solicitud_items", ["solicitud_id"])
    op.create_index("ix_solicitud_items_servicio_id", "solicitud_items", ["servicio_id"])

    op.create_table(
        "cuentas_aprovisionadas",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("solicitud_item_id", sa.Integer(), nullable=False),
        sa.Column("sistema", sa.String(length=50), nullable=False),
        sa.Column("identificador", sa.String(length=120), nullable=False),
        sa.Column("estado", sa.String(length=20), nullable=False),
        sa.Column("datos_cuenta", sa.JSON(), nullable=True),
        sa.Column("credencial_hash", sa.String(length=64), nullable=True),
        sa.Column("fecha_creacion", sa.DateTime(), nullable=False),
        sa.Column("responsable_id", sa.Integer(), nullable=True),
        sa.ForeignKeyConstraint(
            ["responsable_id"],
            ["usuarios.id"],
            name="fk_cuentas_responsable",
            ondelete="SET NULL",
        ),
        sa.ForeignKeyConstraint(
            ["solicitud_item_id"],
            ["solicitud_items.id"],
            name="fk_cuentas_item",
            ondelete="CASCADE",
        ),
        sa.PrimaryKeyConstraint("id"),
    )
    op.create_index(
        "ix_cuentas_aprovisionadas_solicitud_item_id",
        "cuentas_aprovisionadas",
        ["solicitud_item_id"],
    )

    op.create_table(
        "auditoria",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("actor", sa.String(length=100), nullable=False),
        sa.Column("accion", sa.String(length=50), nullable=False),
        sa.Column("entidad", sa.String(length=50), nullable=False),
        sa.Column("entidad_id", sa.String(length=50), nullable=False),
        sa.Column("resultado", sa.String(length=30), nullable=False),
        sa.Column("ip", sa.String(length=45), nullable=True),
        sa.Column("correlacion", sa.String(length=64), nullable=True),
        sa.Column("timestamp", sa.DateTime(), nullable=False),
        sa.PrimaryKeyConstraint("id"),
    )
    op.create_index(
        "ix_auditoria_entidad_entidad_id", "auditoria", ["entidad", "entidad_id"]
    )
    op.create_index("ix_auditoria_fecha", "auditoria", ["timestamp"])


def downgrade() -> None:
    op.drop_index("ix_auditoria_fecha", table_name="auditoria")
    op.drop_index("ix_auditoria_entidad_entidad_id", table_name="auditoria")
    op.drop_table("auditoria")
    op.drop_index(
        "ix_cuentas_aprovisionadas_solicitud_item_id",
        table_name="cuentas_aprovisionadas",
    )
    op.drop_table("cuentas_aprovisionadas")
    op.drop_index("ix_solicitud_items_servicio_id", table_name="solicitud_items")
    op.drop_index("ix_solicitud_items_solicitud_id", table_name="solicitud_items")
    op.drop_table("solicitud_items")
    op.drop_index("ix_solicitudes_servicio_soporte_id", table_name="solicitudes_servicio")
    op.drop_index(
        "ix_solicitudes_servicio_verificacion_id", table_name="solicitudes_servicio"
    )
    op.drop_index("ix_solicitudes_servicio_persona_id", table_name="solicitudes_servicio")
    op.drop_table("solicitudes_servicio")
    op.drop_table("servicios")
    op.drop_index("ix_verificaciones_persona_id", table_name="verificaciones")
    op.drop_table("verificaciones")
    op.drop_index("ix_usuarios_persona_id", table_name="usuarios")
    op.drop_table("usuarios")
    op.drop_table("personas")
