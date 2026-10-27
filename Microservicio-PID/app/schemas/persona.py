from datetime import date
from typing import Annotated

from pydantic import BaseModel, ConfigDict, Field, StringConstraints, model_validator

from app.schemas.telefono import NumeroTelefono, TelefonoRead

Ci = Annotated[
    str,
    StringConstraints(strip_whitespace=True, pattern=r"^\d{11}$", min_length=11, max_length=11),
]

Nombre = Annotated[
    str,
    StringConstraints(strip_whitespace=True, min_length=1, max_length=150),
]


class PersonaCreate(BaseModel):
    ci: Ci
    nombre: Nombre
    fecha_nacimiento: date | None = None
    telefonos: list[NumeroTelefono] = Field(default_factory=list)


class PersonaRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    ci: str
    nombre: str
    fecha_nacimiento: date | None = None
    activo: bool
    telefonos: list[TelefonoRead] = Field(default_factory=list)


class PersonaUpdate(BaseModel):
    """Actualización parcial de una persona."""

    ci: Ci | None = None
    nombre: Nombre | None = None
    fecha_nacimiento: date | None = None
    activo: bool | None = None

    @model_validator(mode="after")
    def require_at_least_one_field(self) -> "PersonaUpdate":
        if not self.model_dump(exclude_unset=True):
            raise ValueError("Debe enviar al menos un campo para actualizar")
        return self

    @model_validator(mode="after")
    def rechazar_nulos_en_campos_no_nulos(self) -> "PersonaUpdate":
        # ci, nombre y activo son NOT NULL en la tabla; solo fecha_nacimiento
        # admite un null explícito (para vaciar el dato).
        nulos = [
            campo
            for campo in ("ci", "nombre", "activo")
            if campo in self.model_fields_set and getattr(self, campo) is None
        ]
        if nulos:
            raise ValueError(f"{' y '.join(nulos)} no puede ser nulo")
        return self


class ListadoQuery(BaseModel):
    skip: int = Field(default=0, ge=0)
    limit: int = Field(default=100, ge=1, le=100)
