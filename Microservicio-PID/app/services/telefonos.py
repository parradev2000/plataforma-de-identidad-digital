from sqlalchemy import select
from sqlalchemy.orm import Session


def list_telefonos(
    db: Session,
    *,
    numero: str | None = None,
    skip: int = 0,
    limit: int = 100,
) -> list:
    stmt = select
    return list(db.scalars(stmt).all())
