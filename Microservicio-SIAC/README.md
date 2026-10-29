# Microservicio-SIAC

Microservicio complementario del ecosistema de Identidad Digital, desarrollado con [Laravel 10](https://laravel.com) y PHP 8.1+.

## Descripción

Microservicio de integración y autenticación del sistema Plataforma de Identidad Digital. Forma parte de la arquitectura distribuida para dar soporte a los procesos de identidad y acceso.

## Requisitos

- PHP >= 8.1
- Composer
- PostgreSQL/MySQL (según configuración)
- Extensiones PHP requeridas por Laravel

## Instalación

```bash
# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve
```

## Configuración

Configurar las variables de entorno en `.env` según el ambiente de despliegue. Revisar `config/` para ajustes específicos del microservicio.

## Pruebas

```bash
php artisan test
```

## Comandos útiles

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan route:list
```

## Licencia

Este proyecto es software privado. Todos los derechos reservados.
