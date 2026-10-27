from typing import Annotated

from pydantic import BaseModel, ConfigDict, Field, StringConstraints

NumeroTelefono = Annotated[
    str,
    StringConstraints(strip_whitespace=True, pattern=r"^\d{8,15}$", min_length=8, max_length=15),
]


class TelefonoCreate(BaseModel):
    numero: NumeroTelefono


class TelefonoRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    numero: str


class PersonaTelefonosReplace(BaseModel):
    """Reemplaza el conjunto de teléfonos de una persona (se crean si no existen)."""

    telefonos: list[NumeroTelefono] = Field(default_factory=list)
