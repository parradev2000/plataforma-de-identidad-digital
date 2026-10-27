from sqlalchemy import Boolean, CheckConstraint, Date, Integer, String, UniqueConstraint
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base
from app.models.telefono import persona_telefonos


class Persona(Base):
    __tablename__ = "personas"
    __table_args__ = (
        UniqueConstraint("ci", name="uq_personas_ci"),
        CheckConstraint(
            "ci ~ '^[0-9]{11}$'",
            name="ck_personas_ci_digits",
        ),
        CheckConstraint(
            "length(btrim(nombre)) > 0",
            name="ck_personas_nombre_not_blank",
        ),
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    ci: Mapped[str] = mapped_column(String(11), nullable=False)
    nombre: Mapped[str] = mapped_column(String(150), nullable=False)
    fecha_nacimiento = mapped_column(Date, nullable=True)
    activo: Mapped[bool] = mapped_column(Boolean, nullable=False, default=True)

    telefonos: Mapped[list["Telefono"]] = relationship(
        secondary=persona_telefonos,
        back_populates="personas",
    )
