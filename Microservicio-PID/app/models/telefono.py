from typing import TYPE_CHECKING

from sqlalchemy import CheckConstraint, ForeignKey, Integer, String, Table, Column
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base

if TYPE_CHECKING:
    from app.models.persona import Persona

# Nomenclador de teléfonos: una persona puede tener varios y un teléfono
# puede pertenecer a varias personas (N:N)
persona_telefonos = Table(
    "persona_telefonos",
    Base.metadata,
    Column(
        "persona_id",
        ForeignKey(
            "personas.id",
            name="fk_persona_telefonos_persona",
            ondelete="CASCADE",
        ),
        primary_key=True,
    ),
    Column(
        "telefono_id",
        ForeignKey(
            "telefonos.id",
            name="fk_persona_telefonos_telefono",
            ondelete="CASCADE",
        ),
        primary_key=True,
    ),
)


class Telefono(Base):
    __tablename__ = "telefonos"
    __table_args__ = (
        CheckConstraint(
            "numero ~ '^[0-9]{8,15}$'",
            name="ck_telefonos_numero_digits",
        ),
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    numero: Mapped[str] = mapped_column(String(20), nullable=False, unique=True)

    personas: Mapped[list["Persona"]] = relationship(
        secondary=persona_telefonos,
        back_populates="telefonos",
    )
