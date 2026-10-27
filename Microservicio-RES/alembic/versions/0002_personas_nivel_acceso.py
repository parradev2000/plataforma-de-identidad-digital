"""personas y nomenclador de nivel de acceso

Revision ID: 0002_personas_nivel_acceso
Revises: 0001_initial
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0002_personas_nivel_acceso"
down_revision: str | None = "0001_initial"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None

NIVELES_ACCESO = [
    {"id": 1, "nombre": "Sin acceso"},
    {"id": 2, "nombre": "Básico"},
    {"id": 3, "nombre": "Medio"},
    {"id": 4, "nombre": "Total"},
]

PERSONAS = [
    {"no_pase": "000123", "nivel_acceso_id": 1},
    {"no_pase": "100245", "nivel_acceso_id": 2},
    {"no_pase": "234567", "nivel_acceso_id": 3},
    {"no_pase": "345678", "nivel_acceso_id": 4},
    {"no_pase": "456789", "nivel_acceso_id": 1},
    {"no_pase": "567890", "nivel_acceso_id": 2},
    {"no_pase": "678901", "nivel_acceso_id": 3},
    {"no_pase": "789012", "nivel_acceso_id": 4},
    {"no_pase": "890123", "nivel_acceso_id": 1},
    {"no_pase": "901234", "nivel_acceso_id": 2},
    {"no_pase": "012345", "nivel_acceso_id": 3},
    {"no_pase": "112233", "nivel_acceso_id": 4},
    {"no_pase": "223344", "nivel_acceso_id": 1},
    {"no_pase": "334455", "nivel_acceso_id": 2},
    {"no_pase": "445566", "nivel_acceso_id": 3},
    {"no_pase": "556677", "nivel_acceso_id": 4},
    {"no_pase": "667788", "nivel_acceso_id": 1},
    {"no_pase": "778899", "nivel_acceso_id": 2},
    {"no_pase": "889900", "nivel_acceso_id": 3},
    {"no_pase": "990011", "nivel_acceso_id": 4},
]


def upgrade() -> None:
    op.create_table(
        "nivel_acceso",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("nombre", sa.String(length=50), nullable=False),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("nombre", name="uq_nivel_acceso_nombre"),
    )
    op.create_table(
        "personas",
        sa.Column("id", sa.Integer(), autoincrement=True, nullable=False),
        sa.Column("no_pase", sa.String(length=6), nullable=False),
        sa.Column("nivel_acceso_id", sa.Integer(), nullable=False),
        sa.CheckConstraint(
            "no_pase ~ '^[0-9]{1,6}$'",
            name="ck_personas_no_pase_digits",
        ),
        sa.ForeignKeyConstraint(
            ["nivel_acceso_id"],
            ["nivel_acceso.id"],
            name="fk_personas_nivel_acceso",
            ondelete="RESTRICT",
        ),
        sa.PrimaryKeyConstraint("id"),
        sa.UniqueConstraint("no_pase", name="uq_personas_no_pase"),
    )
    op.create_index(
        "ix_personas_nivel_acceso_id",
        "personas",
        ["nivel_acceso_id"],
    )

    nivel_acceso = sa.table(
        "nivel_acceso",
        sa.column("id", sa.Integer()),
        sa.column("nombre", sa.String()),
    )
    personas = sa.table(
        "personas",
        sa.column("no_pase", sa.String()),
        sa.column("nivel_acceso_id", sa.Integer()),
    )

    op.bulk_insert(nivel_acceso, NIVELES_ACCESO)
    op.bulk_insert(personas, PERSONAS)

    op.execute(
        "SELECT setval("
        "pg_get_serial_sequence('nivel_acceso', 'id'), "
        "(SELECT MAX(id) FROM nivel_acceso))"
    )


def downgrade() -> None:
    op.drop_index("ix_personas_nivel_acceso_id", table_name="personas")
    op.drop_table("personas")
    op.drop_table("nivel_acceso")
