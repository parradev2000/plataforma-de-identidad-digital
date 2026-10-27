from typing import Annotated

from fastapi import APIRouter, Depends, Path, Query, Response, status
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.models import Telefono
from app.schemas.persona import PersonaRead
from app.schemas.telefono import (
    PersonaTelefonosReplace,
    TelefonoCreate,
    TelefonoRead,
)
from app.services import telefonos as telefono_service

router = APIRouter(tags=["phones"])


@router.get(
    "/api/v1/phones",
    response_model=list[TelefonoRead],
    summary="Nomenclador de teléfonos",
)
def list_phones(
    db: Session = Depends(get_db),
    numero: Annotated[str | None, Query(pattern=r"^\d{8,15}$")] = None,
    skip: Annotated[int, Query(ge=0)] = 0,
    limit: Annotated[int, Query(ge=1, le=100)] = 100,
) -> list[Telefono]:
    return telefono_service.list_telefonos(
        db, numero=numero, skip=skip, limit=limit
    )


@router.post(
    "/api/v1/phones",
    response_model=TelefonoRead,
    status_code=status.HTTP_201_CREATED,
    summary="Registrar un teléfono en el nomenclador",
)
def create_phone(
    payload: TelefonoCreate,
    db: Session = Depends(get_db),
) -> Telefono:
    return telefono_service.crear_telefono(db, payload)


@router.get(
    "/api/v1/personas/{persona_id}/phones",
    response_model=list[TelefonoRead],
    summary="Teléfonos de una persona",
)
def persona_phones(
    persona_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> list[Telefono]:
    return telefono_service.telefonos_de_persona(db, persona_id)


@router.put(
    "/api/v1/personas/{persona_id}/phones",
    response_model=PersonaRead,
    summary="Reemplazar los teléfonos de una persona (se crean si faltan)",
)
def replace_persona_phones(
    persona_id: Annotated[int, Path(gt=0)],
    payload: PersonaTelefonosReplace,
    db: Session = Depends(get_db),
) -> PersonaRead:
    return _a_read(telefono_service.reemplazar_telefonos_de_persona(
        db, persona_id, payload.telefonos
    ))


@router.post(
    "/api/v1/personas/{persona_id}/phones/{telefono_id}",
    response_model=PersonaRead,
    summary="Asignar un teléfono existente a una persona",
)
def add_persona_phone(
    persona_id: Annotated[int, Path(gt=0)],
    telefono_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> PersonaRead:
    return _a_read(
        telefono_service.agregar_telefono_a_persona(db, persona_id, telefono_id)
    )


@router.delete(
    "/api/v1/personas/{persona_id}/phones/{telefono_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Quitar un teléfono a una persona (no borra el nomenclador)",
)
def remove_persona_phone(
    persona_id: Annotated[int, Path(gt=0)],
    telefono_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> Response:
    telefono_service.quitar_telefono_de_persona(db, persona_id, telefono_id)
    return Response(status_code=status.HTTP_204_NO_CONTENT)


def _a_read(persona) -> PersonaRead:
    return PersonaRead.model_validate(persona)
