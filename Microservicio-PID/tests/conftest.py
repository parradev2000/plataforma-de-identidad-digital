from collections.abc import Generator

import pytest
from fastapi.testclient import TestClient
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.estados import Rol
from app.db.session import engine, get_db
from app.main import app


@pytest.fixture
def db_session() -> Generator[Session, None, None]:
    connection = engine.connect()
    transaction = connection.begin()
    session = Session(
        bind=connection,
        join_transaction_mode="create_savepoint",
    )

    try:
        yield session
    finally:
        session.close()
        transaction.rollback()
        connection.close()


@pytest.fixture
def api_client(db_session: Session) -> Generator[TestClient, None, None]:
    def override_get_db() -> Generator[Session, None, None]:
        yield db_session

    app.dependency_overrides[get_db] = override_get_db

    try:
        with TestClient(app) as client:
            yield client
    finally:
        app.dependency_overrides.clear()


@pytest.fixture
def soporte_id(db_session: Session) -> int:
    """Primer usuario con rol SOPORTE (sembrado por la migración 0002)."""
    from app.models import Usuario

    usuario_id = db_session.scalar(
        select(Usuario.id)
        .where(Usuario.rol == Rol.SOPORTE)
        .order_by(Usuario.id)
    )
    assert usuario_id is not None, "No hay usuarios SOPORTE sembrados"
    return usuario_id


@pytest.fixture
def admin_id(db_session: Session) -> int:
    """Primer usuario con rol ADMIN (sembrado por la migración 0002)."""
    from app.models import Usuario

    usuario_id = db_session.scalar(
        select(Usuario.id)
        .where(Usuario.rol == Rol.ADMIN)
        .order_by(Usuario.id)
    )
    assert usuario_id is not None, "No hay usuarios ADMIN sembrados"
    return usuario_id
