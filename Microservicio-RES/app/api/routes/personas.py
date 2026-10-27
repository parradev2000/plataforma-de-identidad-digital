from typing import Annotated

from fastapi import APIRouter, Depends, Path, Query, Response, status
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.models import Persona
from app.schemas.persona import (
    PersonaCreate,
    PersonaRead,
    PersonaReplace,
    PersonaUpdate,
)
from app.services import personas as persona_service

router = APIRouter(prefix="/api/v1/personas", tags=["personas"])


@router.get("", response_model=list[PersonaRead], summary="Listar personas")
def list_personas(
    db: Session = Depends(get_db),
    skip: Annotated[int, Query(ge=0)] = 0,
    limit: Annotated[int, Query(ge=1, le=100)] = 100,
    no_pase: Annotated[str | None, Query(pattern=r"^\d{1,6}$")] = None,
    ci: Annotated[str | None, Query(pattern=r"^\d{1,11}$")] = None,
    nivel_acceso_id: Annotated[int | None, Query(gt=0)] = None,
) -> list[Persona]:
    return persona_service.list_personas(
        db,
        skip=skip,
        limit=limit,
        no_pase=no_pase,
        ci=ci,
        nivel_acceso_id=nivel_acceso_id,
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


@router.post(
    "",
    response_model=PersonaRead,
    status_code=status.HTTP_201_CREATED,
    summary="Crear una persona",
)
def create_persona(
    payload: PersonaCreate,
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.create_persona(db, payload)


@router.put(
    "/{persona_id}",
    response_model=PersonaRead,
    summary="Reemplazar una persona",
)
def replace_persona(
    persona_id: Annotated[int, Path(gt=0)],
    payload: PersonaReplace,
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.replace_persona(db, persona_id, payload)


@router.patch(
    "/{persona_id}",
    response_model=PersonaRead,
    summary="Actualizar parcialmente una persona",
)
def update_persona(
    persona_id: Annotated[int, Path(gt=0)],
    payload: PersonaUpdate,
    db: Session = Depends(get_db),
) -> Persona:
    return persona_service.update_persona(db, persona_id, payload)


@router.delete(
    "/{persona_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Eliminar una persona",
)
def delete_persona(
    persona_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> Response:
    persona_service.delete_persona(db, persona_id)
    return Response(status_code=status.HTTP_204_NO_CONTENT)
