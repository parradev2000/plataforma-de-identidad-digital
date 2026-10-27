"""renombra personas.numero_doc a ci

Revision ID: 0004_personas_ci
Revises: 0003_personas_simplificar
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0004_personas_ci"
down_revision: str | None = "0003_personas_simplificar"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None


def upgrade() -> None:
    op.drop_constraint("uq_personas_numero_doc", "personas", type_="unique")
    op.alter_column(
        "personas",
        "numero_doc",
        new_column_name="ci",
        existing_type=sa.String(length=30),
        existing_nullable=False,
    )
    op.create_unique_constraint("uq_personas_ci", "personas", ["ci"])


def downgrade() -> None:
    op.drop_constraint("uq_personas_ci", "personas", type_="unique")
    op.alter_column(
        "personas",
        "ci",
        new_column_name="numero_doc",
        existing_type=sa.String(length=30),
        existing_nullable=False,
    )
    op.create_unique_constraint(
        "uq_personas_numero_doc", "personas", ["numero_doc"]
    )
