"""agregar nombres a personas

Revision ID: 0003_personas_nombres
Revises: 0002_personas_nivel_acceso
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0003_personas_nombres"
down_revision: str | None = "0002_personas_nivel_acceso"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None

NOMBRES_POR_PASE = {
    "000123": "Juan Carlos",
    "100245": "María Elena",
    "234567": "Pedro Antonio",
    "345678": "Ana Luisa",
    "456789": "Luis Miguel",
    "567890": "Carmen Rosa",
    "678901": "José Manuel",
    "789012": "Laura Beatriz",
    "890123": "Carlos Alberto",
    "901234": "Marta Isabel",
    "012345": "Diego Alejandro",
    "112233": "Sofía Victoria",
    "223344": "Andrés Felipe",
    "334455": "Valentina",
    "445566": "Javier Ernesto",
    "556677": "Daniela Paz",
    "667788": "Ricardo José",
    "778899": "Patricia Jean",
    "889900": "Fernando Raúl",
    "990011": "Claudia Marlene",
}


def upgrade() -> None:
    op.add_column(
        "personas",
        sa.Column("nombres", sa.String(length=150), nullable=True),
    )

    connection = op.get_bind()
    for no_pase, nombres in NOMBRES_POR_PASE.items():
        connection.execute(
            sa.text(
                "UPDATE personas SET nombres = :nombres "
                "WHERE no_pase = :no_pase"
            ),
            {"nombres": nombres, "no_pase": no_pase},
        )

    connection.execute(
        sa.text(
            "UPDATE personas SET nombres = 'Persona ' || id::text "
            "WHERE nombres IS NULL"
        )
    )

    op.alter_column(
        "personas",
        "nombres",
        existing_type=sa.String(length=150),
        nullable=False,
    )
    op.create_check_constraint(
        "ck_personas_nombres_not_blank",
        "personas",
        "length(btrim(nombres)) > 0",
    )


def downgrade() -> None:
    op.drop_constraint(
        "ck_personas_nombres_not_blank",
        "personas",
        type_="check",
    )
    op.drop_column("personas", "nombres")
