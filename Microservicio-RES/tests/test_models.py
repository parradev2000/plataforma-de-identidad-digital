from sqlalchemy import CheckConstraint, UniqueConstraint

from app.db.base import Base
from app.models import NivelAcceso, Persona


def test_tables_registered() -> None:
    assert "nivel_acceso" in Base.metadata.tables
    assert "personas" in Base.metadata.tables


def test_nivel_acceso_columns_and_constraints() -> None:
    table = Base.metadata.tables["nivel_acceso"]

    assert set(table.columns.keys()) == {"id", "nombre"}
    assert table.c.id.primary_key is True
    assert table.c.nombre.type.length == 50
    assert table.c.nombre.nullable is False

    unique_names = {
        constraint.name
        for constraint in table.constraints
        if isinstance(constraint, UniqueConstraint)
    }
    assert "uq_nivel_acceso_nombre" in unique_names


def test_personas_columns_and_constraints() -> None:
    table = Base.metadata.tables["personas"]

    assert set(table.columns.keys()) == {
        "id",
        "nombres",
        "no_pase",
        "ci",
        "nivel_acceso_id",
    }
    assert table.c.id.primary_key is True
    assert table.c.nombres.type.length == 150
    assert table.c.nombres.nullable is False
    assert table.c.no_pase.type.length == 6
    assert table.c.no_pase.nullable is False
    assert table.c.ci.type.length == 11
    assert table.c.ci.nullable is True
    assert table.c.nivel_acceso_id.nullable is False

    foreign_keys = list(table.c.nivel_acceso_id.foreign_keys)
    assert len(foreign_keys) == 1
    assert foreign_keys[0].target_fullname == "nivel_acceso.id"
    assert foreign_keys[0].ondelete == "RESTRICT"

    check_names = {
        constraint.name
        for constraint in table.constraints
        if isinstance(constraint, CheckConstraint)
    }
    assert "ck_personas_no_pase_digits" in check_names
    assert "ck_personas_ci_digits" in check_names
    assert "ck_personas_nombres_not_blank" in check_names

    unique_names = {
        constraint.name
        for constraint in table.constraints
        if isinstance(constraint, UniqueConstraint)
    }
    assert "uq_personas_no_pase" in unique_names
    assert "uq_personas_ci" in unique_names


def test_model_relationships() -> None:
    assert Persona.nivel_acceso.property.mapper.class_ is NivelAcceso
    assert NivelAcceso.personas.property.mapper.class_ is Persona
