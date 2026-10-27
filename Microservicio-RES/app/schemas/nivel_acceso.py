from typing import Annotated

from pydantic import BaseModel, ConfigDict, StringConstraints, model_validator

NombreNivel = Annotated[
    str,
    StringConstraints(strip_whitespace=True, min_length=1, max_length=50),
]


class NivelAccesoCreate(BaseModel):
    nombre: NombreNivel


class NivelAccesoReplace(BaseModel):
    nombre: NombreNivel


class NivelAccesoUpdate(BaseModel):
    nombre: NombreNivel | None = None

    @model_validator(mode="after")
    def require_at_least_one_field(self) -> "NivelAccesoUpdate":
        if not self.model_dump(exclude_unset=True, exclude_none=True):
            raise ValueError("Debe enviar al menos un campo para actualizar")
        return self


class NivelAccesoRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    nombre: str
