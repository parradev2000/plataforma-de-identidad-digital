from typing import Annotated

from fastapi import APIRouter, Depends, Path, Query, status
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.models import Persona
from app.schemas.persona import PersonaCreate, PersonaRead, PersonaUpdate
from app.services import personas as persona_service

router = APIRouter(prefix="/api/v1/personas", tags=["personas"])


@router.get("", response_model=list[PersonaRead], summary="Listar personas")
def list_personas(
    db: Session = Depends(get_db),
    skip: Annotated[int, Query(ge=0)] = 0,
    limit: Annotated[int, Query(ge=1, le=100)] = 100,
    ci: Annotated[str | None, Query(pattern=r"^\d{11}$")] = None,
    nombre: Annotated[str | None, Query(min_length=1, max_length=150)] = None,
    activo: Annotated[bool | None, Query()] = None,
) -> list[Persona]:
    return persona_service.list_personas(
        db,
        skip=skip,
        limit=limit,
        ci=ci,
        nombre=nombre,
        activo=activo,
    )


@router.get(
    "/{persona_id}",
    response_model=PersonaRead,
    summary="Obtener una persona",
)
def get_persona(
    persona_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.get_persona(db, persona_id)


@router.patch(
    "/{persona_id}",
    response_model=PersonaRead,
    summary="Actualizar parcialmente una persona (ci, activo, nombre, fecha)",
)
def update_persona(
    persona_id: Annotated[int, Path(gt=0)],
    payload: PersonaUpdate,
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.update_persona(db, persona_id, payload)


@router.post(
    "",
    response_model=PersonaRead,
    status_code=status.HTTP_201_CREATED,
    summary="Registrar una persona",
)
def create_persona(
    payload: PersonaCreate,
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.create_persona(db, payload)
