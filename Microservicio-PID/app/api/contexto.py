from fastapi import Request


def contexto(request: Request) -> dict[str, str | None]:
    """Extrae IP y correlation_id para los eventos de auditoría (RF-AUD-01)."""
    correlation_id = request.headers.get("X-Correlation-Id")
    ip = request.client.host if request.client else None
    return {"ip": ip, "correlacion": correlation_id}
