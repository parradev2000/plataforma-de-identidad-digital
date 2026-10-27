"""estado booleano, ci de 11 dígitos y nomenclador de teléfonos N:N

Revision ID: 0005_personas_activo_ci11_tel
Revises: 0004_personas_ci
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0005_personas_activo_ci11_tel"
down_revision: str | None = "0004_personas_ci"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None


def upgrade() -> None:
    # 1) estado (ACTIVA/INACTIVA) -> booleano activo
    op.add_column(
        "personas",
        sa.Column("activo", sa.Boolean(), nullable=False, server_default=sa.true()),
    )
    op.execute("UPDATE personas SET activo = FALSE WHERE estado = 'INACTIVA'")
    op.drop_column("personas", "estado")

    # 2) ci estrictamente de 11 dígitos
    op.alter_column(
        "personas",
        "ci",
        existing_type=sa.String(length=30),
        type_=sa.String(length=11),
        existing_nullable=False,
    )
    op.create_check_constraint(
        "ck_personas_ci_digits", "personas", "ci ~ '^[0-9]{11}$'"
    )

    # 3) nomenclador de teléfonos N:N (con backfill del campo anterior)
    op.create_table(
        "telefonos",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("numero", sa.String(length=20), nullable=False),
        sa.CheckConstraint(
            "numero ~ '^[0-9]{8,15}$'",
            name="ck_telefonos_numero_digits",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("numero"),
    )

    op.create_table(
        "persona_telefonos",
        sa.Column("persona_id", sa.Integer(), nullable=False),
        sa.Column("telefono_id", sa.Integer(), nullable=False),
        sa.ForeignKeyConstraint(
            ["persona_id"],
            ["personas.id"],
            name="fk_persona_telefonos_persona",
            ondelete="CASCADE",
        ),
        sa.ForeignKeyConstraint(
            ["telefono_id"],
            ["telefonos.id"],
            name="fk_persona_telefonos_telefono",
            ondelete="CASCADE",
        ),
        sa.PrimaryKeyConstraint("persona_id", "telefono_id"),
    )

    op.execute(
        "INSERT INTO telefonos (numero) "
        "SELECT DISTINCT telefono FROM personas "
        "WHERE telefono IS NOT NULL AND telefono ~ '^[0-9]{8,15}$'"
    )
    op.execute(
        "INSERT INTO persona_telefonos (persona_id, telefono_id) "
        "SELECT p.id, t.id FROM personas p "
        "JOIN telefonos t ON t.numero = p.telefono "
        "WHERE p.telefono IS NOT NULL"
    )
    op.drop_column("personas", "telefono")


def downgrade() -> None:
    op.add_column(
        "personas",
        sa.Column("telefono", sa.String(length=30), nullable=True),
    )
    op.execute(
        "UPDATE personas SET telefono = sub.numero FROM ("
        "  SELECT pt.persona_id, MIN(t.numero) AS numero "
        "  FROM persona_telefonos pt JOIN telefonos t ON t.id = pt.telefono_id "
        "  GROUP BY pt.persona_id"
        ") AS sub WHERE sub.persona_id = personas.id"
    )

    op.drop_constraint("ck_personas_ci_digits", "personas", type_="check")
    op.alter_column(
        "personas",
        "ci",
        existing_type=sa.String(length=11),
        type_=sa.String(length=30),
        existing_nullable=False,
    )

    op.add_column(
        "personas",
        sa.Column(
            "estado",
            sa.String(length=20),
            nullable=False,
            server_default="ACTIVA",
        ),
    )
    op.execute("UPDATE personas SET estado = 'INACTIVA' WHERE activo = FALSE")
    op.alter_column("personas", "estado", server_default=None)
    op.drop_column("personas", "activo")

    op.drop_table("persona_telefonos")
    op.drop_table("telefonos")
