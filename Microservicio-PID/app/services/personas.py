from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session, selectinload

from app.models import Persona
from app.schemas.persona import PersonaCreate, PersonaUpdate
from app.services import telefonos as telefono_service
from app.services.exceptions import DuplicateError, NotFoundError


def _get_persona_o_404(db: Session, persona_id: int) -> Persona:
    persona = db.scalars(
        select(Persona)
        .where(Persona.id == persona_id)
        .options(selectinload(Persona.telefonos))
    ).first()
    if persona is None:
        raise NotFoundError("Persona no encontrada")
    return persona


def _commit(db: Session) -> None:
    """Confirma la transacción y traduce el CI duplicado a un 409."""
    try:
        db.commit()
    except IntegrityError as exc:
        db.rollback()
        if "uq_personas_ci" in str(exc.orig):
            raise DuplicateError("El CI ya existe") from exc
        raise


def list_personas(
    db: Session,
    *,
    skip: int = 0,
    limit: int = 100,
    ci: str | None = None,
    nombre: str | None = None,
    activo: bool | None = None,
) -> list[Persona]:
    stmt = (
        select(Persona)
        .options(selectinload(Persona.telefonos))
        .order_by(Persona.id)
    )

    if ci is not None:
        stmt = stmt.where(Persona.ci == ci)
    if nombre is not None:
        stmt = stmt.where(Persona.nombre.ilike(f"%{nombre}%"))
    if activo is not None:
        stmt = stmt.where(Persona.activo == activo)

    return list(db.scalars(stmt.offset(skip).limit(limit)).all())


def get_persona(db: Session, persona_id: int) -> Persona:
    return _get_persona_o_404(db, persona_id)


def update_persona(db: Session, persona_id: int, data: PersonaUpdate) -> Persona:
    persona = _get_persona_o_404(db, persona_id)
    for campo, valor in data.model_dump(exclude_unset=True).items():
        setattr(persona, campo, valor)
    _commit(db)
    return _get_persona_o_404(db, persona_id)


def create_persona(db: Session, data: PersonaCreate) -> Persona:
    persona = Persona(
        ci=data.ci,
        nombre=data.nombre,
        fecha_nacimiento=data.fecha_nacimiento,
        activo=True,
        telefonos=[
            telefono_service.obtener_o_crear(db, numero) for numero in data.telefonos
        ],
    )
    db.add(persona)
    _commit(db)
    db.refresh(persona)
    return _get_persona_o_404(db, persona.id)
