from pathlib import Path

from fastapi import FastAPI
from fastapi.openapi.docs import get_redoc_html, get_swagger_ui_html
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles

from app.api.routes import (
    health,
    personas,
    telefonos,
)
from app.core.config import settings

BASE_DIR = Path(__file__).resolve().parent.parent
STATIC_DIR = BASE_DIR / "docs"
FRONTEND_DIR = BASE_DIR / "frontend" / "dist"
OPENAPI_URL = "/local/openapi.json"

app = FastAPI(
    title=settings.app_name,
    version=settings.app_version,
    description="Microservicio PID (Plataforma de Identidad Digital) sobre FastAPI con documentación offline.",
    docs_url=None,
    redoc_url=None,
    openapi_url=OPENAPI_URL,
)

app.mount("/local/static", StaticFiles(directory=STATIC_DIR), name="local-static")
app.include_router(health.router)
app.include_router(personas.router)
app.include_router(telefonos.router)

# Portal web (frontend compilado por Vite en frontend/dist)
if FRONTEND_DIR.exists():
    app.mount(
        "/app",
        StaticFiles(directory=FRONTEND_DIR, html=True),
        name="frontend",
    )


@app.get("/", include_in_schema=False)
def root() -> dict[str, str]:
    return {
        "service": settings.app_name,
        "version": settings.app_version,
        "docs": "/local/docs",
        "redoc": "/local/redoc",
        "frontend": "/app/",
    }


@app.get("/local/docs", include_in_schema=False, response_class=HTMLResponse)
def swagger_ui() -> HTMLResponse:
    return get_swagger_ui_html(
        openapi_url=OPENAPI_URL,
        title=f"{settings.app_name} - Swagger UI",
        swagger_js_url="/local/static/swagger/swagger-ui-bundle.js",
        swagger_css_url="/local/static/swagger/swagger-ui.css",
        swagger_favicon_url="/local/static/swagger/favicon-32x32.png",
    )


@app.get("/local/redoc", include_in_schema=False, response_class=HTMLResponse)
def redoc_ui() -> HTMLResponse:
    return get_redoc_html(
        openapi_url=OPENAPI_URL,
        title=f"{settings.app_name} - ReDoc",
        redoc_js_url="/local/static/redoc/redoc.standalone.js",
        redoc_favicon_url="/local/static/swagger/favicon-32x32.png",
        with_google_fonts=False,
    )
