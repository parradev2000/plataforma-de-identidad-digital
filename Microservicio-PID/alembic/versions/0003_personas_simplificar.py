"""simplifica personas: elimina tipo_doc, email y empresa_area

Revision ID: 0003_personas_simplificar
Revises: 0002_datos_iniciales
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0003_personas_simplificar"
down_revision: str | None = "0002_datos_iniciales"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None


def upgrade() -> None:
    op.drop_constraint("uq_personas_documento", "personas", type_="unique")
    op.drop_column("personas", "tipo_doc")
    op.drop_column("personas", "email")
    op.drop_column("personas", "empresa_area")
    op.create_unique_constraint(
        "uq_personas_numero_doc", "personas", ["numero_doc"]
    )


def downgrade() -> None:
    op.drop_constraint("uq_personas_numero_doc", "personas", type_="unique")
    op.add_column(
        "personas",
        sa.Column("tipo_doc", sa.String(length=20), nullable=False, server_default="CI"),
    )
    op.add_column(
        "personas",
        sa.Column("email", sa.String(length=120), nullable=True),
    )
    op.add_column(
        "personas",
        sa.Column("empresa_area", sa.String(length=100), nullable=True),
    )
    op.alter_column("personas", "tipo_doc", server_default=None)
    op.create_unique_constraint(
        "uq_personas_documento", "personas", ["tipo_doc", "numero_doc"]
    )
