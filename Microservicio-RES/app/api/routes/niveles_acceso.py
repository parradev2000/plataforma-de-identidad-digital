from typing import Annotated

from fastapi import APIRouter, Depends, Path, Query, Response, status
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.models import NivelAcceso
from app.schemas.nivel_acceso import (
    NivelAccesoCreate,
    NivelAccesoRead,
    NivelAccesoReplace,
    NivelAccesoUpdate,
)
from app.services import niveles_acceso as nivel_service

router = APIRouter(prefix="/api/v1/niveles-acceso", tags=["niveles-acceso"])


@router.get(
    "",
    response_model=list[NivelAccesoRead],
    summary="Listar niveles de acceso",
)
def list_niveles(
    db: Session = Depends(get_db),
    skip: Annotated[int, Query(ge=0)] = 0,
    limit: Annotated[int, Query(ge=1, le=100)] = 100,
) -> list[NivelAcceso]:
    return nivel_service.list_niveles(db, skip=skip, limit=limit)


@router.get(
    "/{nivel_id}",
    response_model=NivelAccesoRead,
    summary="Obtener un nivel de acceso",
)
def get_nivel(
    nivel_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> NivelAcceso:
    return nivel_service.get_nivel(db, nivel_id)


@router.post(
    "",
    response_model=NivelAccesoRead,
    status_code=status.HTTP_201_CREATED,
    summary="Crear un nivel de acceso",
)
def create_nivel(
    payload: NivelAccesoCreate,
    db: Session = Depends(get_db),
) -> NivelAcceso:
    return nivel_service.create_nivel(db, payload)


@router.put(
    "/{nivel_id}",
    response_model=NivelAccesoRead,
    summary="Reemplazar un nivel de acceso",
)
def replace_nivel(
    nivel_id: Annotated[int, Path(gt=0)],
    payload: NivelAccesoReplace,
    db: Session = Depends(get_db),
) -> NivelAcceso:
    return nivel_service.replace_nivel(db, nivel_id, payload)


@router.patch(
    "/{nivel_id}",
    response_model=NivelAccesoRead,
    summary="Actualizar parcialmente un nivel de acceso",
)
def update_nivel(
    nivel_id: Annotated[int, Path(gt=0)],
    payload: NivelAccesoUpdate,
    db: Session = Depends(get_db),
) -> NivelAcceso:
    return nivel_service.update_nivel(db, nivel_id, payload)


@router.delete(
    "/{nivel_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Eliminar un nivel de acceso",
)
def delete_nivel(
    nivel_id: Annotated[int, Path(gt=0)],
    db: Session = Depends(get_db),
) -> Response:
    nivel_service.delete_nivel(db, nivel_id)
    return Response(status_code=status.HTTP_204_NO_CONTENT)
