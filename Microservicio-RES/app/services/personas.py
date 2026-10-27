from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.models import NivelAcceso, Persona
from app.schemas.persona import PersonaCreate, PersonaReplace, PersonaUpdate
from app.services.exceptions import DuplicateError, NotFoundError


def _get_persona_or_404(db: Session, persona_id: int) -> Persona:
    persona = db.get(Persona, persona_id)
    if persona is None:
        raise NotFoundError("Persona no encontrada")
    return persona


def _ensure_nivel_acceso_exists(db: Session, nivel_acceso_id: int) -> None:
    if db.get(NivelAcceso, nivel_acceso_id) is None:
        raise NotFoundError("Nivel de acceso no encontrado")


def _commit(db: Session) -> None:
    try:
        db.commit()
    except IntegrityError as exc:
        db.rollback()
        pgcode = getattr(exc.orig, "pgcode", None)
        if pgcode == "23505":
            constraint = getattr(getattr(exc.orig, "diag", None), "constraint_name", "")
            if constraint == "uq_personas_ci":
                raise DuplicateError("El CI ya existe") from exc
            raise DuplicateError("El número de pase ya existe") from exc
        if pgcode == "23503":
            raise NotFoundError("Nivel de acceso no encontrado") from exc
        raise DuplicateError(
            "No se pudo guardar la persona por un conflicto de datos"
        ) from exc


def list_personas(
    db: Session,
    *,
    skip: int = 0,
    limit: int = 100,
    no_pase: str | None = None,
    ci: str | None = None,
    nivel_acceso_id: int | None = None,
) -> list[Persona]:
    stmt = select(Persona).order_by(Persona.id)

    if no_pase is not None:
        stmt = stmt.where(Persona.no_pase == no_pase)
    if ci is not None:
        stmt = stmt.where(Persona.ci == ci)
    if nivel_acceso_id is not None:
        stmt = stmt.where(Persona.nivel_acceso_id == nivel_acceso_id)

    return list(db.scalars(stmt.offset(skip).limit(limit)).all())


def get_persona(db: Session, persona_id: int) -> Persona:
    return _get_persona_or_404(db, persona_id)


def create_persona(db: Session, data: PersonaCreate) -> Persona:
    _ensure_nivel_acceso_exists(db, data.nivel_acceso_id)

    persona = Persona(
        nombres=data.nombres,
        no_pase=data.no_pase,
        ci=data.ci,
        nivel_acceso_id=data.nivel_acceso_id,
    )
    db.add(persona)
    _commit(db)
    db.refresh(persona)
    return persona


def replace_persona(
    db: Session,
    persona_id: int,
    data: PersonaReplace,
) -> Persona:
    persona = _get_persona_or_404(db, persona_id)
    _ensure_nivel_acceso_exists(db, data.nivel_acceso_id)

    persona.nombres = data.nombres
    persona.no_pase = data.no_pase
    persona.ci = data.ci
    persona.nivel_acceso_id = data.nivel_acceso_id

    _commit(db)
    db.refresh(persona)
    return persona


def update_persona(
    db: Session,
    persona_id: int,
    data: PersonaUpdate,
) -> Persona:
    persona = _get_persona_or_404(db, persona_id)
    changes = data.model_dump(exclude_unset=True, exclude_none=True)

    if "nivel_acceso_id" in changes:
        _ensure_nivel_acceso_exists(db, changes["nivel_acceso_id"])

    for field, value in changes.items():
        setattr(persona, field, value)

    _commit(db)
    db.refresh(persona)
    return persona


def delete_persona(db: Session, persona_id: int) -> None:
    persona = _get_persona_or_404(db, persona_id)
    db.delete(persona)
    _commit(db)
