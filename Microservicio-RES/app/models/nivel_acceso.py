from typing import TYPE_CHECKING

from sqlalchemy import Integer, String, UniqueConstraint
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base

if TYPE_CHECKING:
    from app.models.persona import Persona


class NivelAcceso(Base):
    __tablename__ = "nivel_acceso"
    __table_args__ = (
        UniqueConstraint("nombre", name="uq_nivel_acceso_nombre"),
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(50), nullable=False)

    personas: Mapped[list["Persona"]] = relationship(back_populates="nivel_acceso")
