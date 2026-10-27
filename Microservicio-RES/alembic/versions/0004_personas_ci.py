"""agrega ci (carnet de identidad) a personas

Revision ID: 0004_personas_ci
Revises: 0003_personas_nombres
Create Date: 2026-10-26

"""

from collections.abc import Sequence

import sqlalchemy as sa
from alembic import op

revision: str = "0004_personas_ci"
down_revision: str | None = "0003_personas_nombres"
branch_labels: str | Sequence[str] | None = None
depends_on: str | Sequence[str] | None = None


def upgrade() -> None:
    op.add_column("personas", sa.Column("ci", sa.String(length=11), nullable=True))
    op.create_check_constraint(
        "ck_personas_ci_digits",
        "personas",
        "ci IS NULL OR ci ~ '^[0-9]{1,11}$'",
    )
    op.create_unique_constraint("uq_personas_ci", "personas", ["ci"])

    # Backfill de los datos existentes: CI de 11 dígitos derivado del no_pase
    op.execute("UPDATE personas SET ci = '8' || no_pase || '4567'")


def downgrade() -> None:
    op.drop_constraint("uq_personas_ci", "personas", type_="unique")
    op.drop_constraint("ck_personas_ci_digits", "personas", type_="check")
    op.drop_column("personas", "ci")
