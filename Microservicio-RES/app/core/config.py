from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    app_name: str = "Microservicio-RES"
    app_version: str = "0.1.0"
    debug: bool = False
    database_url: str = (
        "postgresql+psycopg2://postgres:postgres@10.10.16.250:5432/bd_res"
    )


@lru_cache
def get_settings() -> Settings:
    return Settings()


settings = get_settings()
