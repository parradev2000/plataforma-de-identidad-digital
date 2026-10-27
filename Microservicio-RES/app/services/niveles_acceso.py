from sqlalchemy import func, select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.models import NivelAcceso, Persona
from app.schemas.nivel_acceso import (
    NivelAccesoCreate,
    NivelAccesoReplace,
    NivelAccesoUpdate,
)
from app.services.exceptions import DuplicateError, NotFoundError, ReferencedError

NIVEL_EN_USO = "No se puede eliminar un nivel de acceso con personas asociadas"


def _get_nivel_or_404(db: Session, nivel_id: int) -> NivelAcceso:
    nivel = db.get(NivelAcceso, nivel_id)
    if nivel is None:
        raise NotFoundError("Nivel de acceso no encontrado")
    return nivel


def _commit(db: Session, duplicate_detail: str) -> None:
    try:
        db.commit()
    except IntegrityError as exc:
        db.rollback()
        pgcode = getattr(exc.orig, "pgcode", None)
        if pgcode == "23505":
            raise DuplicateError(duplicate_detail) from exc
        if pgcode == "23503":
            raise ReferencedError(NIVEL_EN_USO) from exc
        raise DuplicateError(
            "No se pudo guardar el nivel de acceso por un conflicto de datos"
        ) from exc


def list_niveles(
    db: Session,
    *,
    skip: int = 0,
    limit: int = 100,
) -> list[NivelAcceso]:
    stmt = select(NivelAcceso).order_by(NivelAcceso.id)
    return list(db.scalars(stmt.offset(skip).limit(limit)).all())


def get_nivel(db: Session, nivel_id: int) -> NivelAcceso:
    return _get_nivel_or_404(db, nivel_id)


def create_nivel(db: Session, data: NivelAccesoCreate) -> NivelAcceso:
    nivel = NivelAcceso(nombre=data.nombre)
    db.add(nivel)
    _commit(db, "El nombre del nivel de acceso ya existe")
    db.refresh(nivel)
    return nivel


def replace_nivel(
    db: Session,
    nivel_id: int,
    data: NivelAccesoReplace,
) -> NivelAcceso:
    nivel = _get_nivel_or_404(db, nivel_id)
    nivel.nombre = data.nombre
    _commit(db, "El nombre del nivel de acceso ya existe")
    db.refresh(nivel)
    return nivel


def update_nivel(
    db: Session,
    nivel_id: int,
    data: NivelAccesoUpdate,
) -> NivelAcceso:
    nivel = _get_nivel_or_404(db, nivel_id)
    changes = data.model_dump(exclude_unset=True, exclude_none=True)

    for field, value in changes.items():
        setattr(nivel, field, value)

    _commit(db, "El nombre del nivel de acceso ya existe")
    db.refresh(nivel)
    return nivel


def delete_nivel(db: Session, nivel_id: int) -> None:
    nivel = _get_nivel_or_404(db, nivel_id)
    references = db.scalar(
        select(func.count())
        .select_from(Persona)
        .where(Persona.nivel_acceso_id == nivel_id)
    )
    if references:
        raise ReferencedError(NIVEL_EN_USO)

    db.delete(nivel)
    _commit(db, "El nombre del nivel de acceso ya existe")
