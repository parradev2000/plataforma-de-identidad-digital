from typing import Annotated

from pydantic import (
    BaseModel,
    ConfigDict,
    Field,
    StringConstraints,
    model_validator,
)

Nombres = Annotated[
    str,
    StringConstraints(strip_whitespace=True, min_length=1, max_length=150),
]

NoPase = Annotated[
    str,
    StringConstraints(pattern=r"^\d{1,6}$", min_length=1, max_length=6),
]

Ci = Annotated[
    str,
    StringConstraints(pattern=r"^\d{1,11}$", min_length=1, max_length=11),
]


class PersonaCreate(BaseModel):
    nombres: Nombres
    no_pase: NoPase
    ci: Ci | None = None
    nivel_acceso_id: int = Field(gt=0)


class PersonaReplace(BaseModel):
    nombres: Nombres
    no_pase: NoPase
    ci: Ci | None = None
    nivel_acceso_id: int = Field(gt=0)


class PersonaUpdate(BaseModel):
    nombres: Nombres | None = None
    no_pase: NoPase | None = None
    ci: Ci | None = None
    nivel_acceso_id: int | None = Field(default=None, gt=0)

    @model_validator(mode="after")
    def require_at_least_one_field(self) -> "PersonaUpdate":
        if not self.model_dump(exclude_unset=True, exclude_none=True):
            raise ValueError("Debe enviar al menos un campo para actualizar")
        return self


class PersonaRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    nombres: str
    no_pase: str
    ci: str | None = None
    nivel_acceso_id: int
