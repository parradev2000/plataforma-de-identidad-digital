"""Catálogos de estados y roles (sección 7 de la especificación)."""

from enum import StrEnum


class Rol(StrEnum):
    SOLICITANTE = "SOLICITANTE"
    SOPORTE = "SOPORTE"
    COORDINADOR = "COORDINADOR"
    ADMIN = "ADMIN"
    AUDITOR = "AUDITOR"


class EstadoVerificacion(StrEnum):
    RECIBIDA = "RECIBIDA"
    EN_PROCESO = "EN_PROCESO"
    APROBADA = "APROBADA"
    RECHAZADA = "RECHAZADA"
    PENDIENTE_REVISION = "PENDIENTE_REVISION"
    ERROR_PROVEEDOR = "ERROR_PROVEEDOR"
    EXPIRADA = "EXPIRADA"


class EstadoSolicitud(StrEnum):
    ENVIADA = "ENVIADA"
    EN_REVISION = "EN_REVISION"
    APROBADA = "APROBADA"
    RECHAZADA = "RECHAZADA"
    CANCELADA = "CANCELADA"
    EN_PROVISION = "EN_PROVISION"
    COMPLETADA = "COMPLETADA"
    COMPLETADA_PARCIAL = "COMPLETADA_PARCIAL"
    FALLIDA = "FALLIDA"


class EstadoItem(StrEnum):
    PENDIENTE = "PENDIENTE"
    EN_PROCESO = "EN_PROCESO"
    CREADO = "CREADO"
    FALLIDO = "FALLIDO"
    REINTENTANDO = "REINTENTANDO"


class EstadoCuenta(StrEnum):
    ACTIVA = "ACTIVA"
    FALLIDA = "FALLIDA"


class Prioridad(StrEnum):
    BAJA = "BAJA"
    NORMAL = "NORMAL"
    ALTA = "ALTA"
