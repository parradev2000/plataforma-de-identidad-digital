from typing import TYPE_CHECKING

from sqlalchemy import CheckConstraint, ForeignKey, Integer, String, UniqueConstraint
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base

if TYPE_CHECKING:
    from app.models.nivel_acceso import NivelAcceso


class Persona(Base):
    __tablename__ = "personas"
    __table_args__ = (
        CheckConstraint(
            "no_pase ~ '^[0-9]{1,6}$'",
            name="ck_personas_no_pase_digits",
        ),
        CheckConstraint(
            "ci IS NULL OR ci ~ '^[0-9]{1,11}$'",
            name="ck_personas_ci_digits",
        ),
        CheckConstraint(
            "length(btrim(nombres)) > 0",
            name="ck_personas_nombres_not_blank",
        ),
        UniqueConstraint("no_pase", name="uq_personas_no_pase"),
        UniqueConstraint("ci", name="uq_personas_ci"),
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    nombres: Mapped[str] = mapped_column(String(150), nullable=False)
    no_pase: Mapped[str] = mapped_column(String(6), nullable=False)
    ci: Mapped[str | None] = mapped_column(String(11), nullable=True)
    nivel_acceso_id: Mapped[int] = mapped_column(
        ForeignKey(
            "nivel_acceso.id",
            name="fk_personas_nivel_acceso",
            ondelete="RESTRICT",
        ),
        nullable=False,
        index=True,
    )

    nivel_acceso: Mapped["NivelAcceso"] = relationship(
        back_populates="personas",
    )
