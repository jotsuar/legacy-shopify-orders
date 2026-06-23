# Shopify Orders — Legacy (Laravel 11)

Componente legacy en PHP/Laravel que expone materiales con bajo stock a través de una API REST documentada con Swagger/OpenAPI.

## Stack

- **Laravel 11** + PHP 8.2
- **PostgreSQL** (misma BD que el backend, acceso de solo lectura)
- **L5-Swagger** — documentación OpenAPI 3.0 con PHP 8 Attributes
- Swagger UI en `/api/documentation`

## Requisitos

- PHP 8.2+ con extensiones: `pdo`, `pdo_pgsql`, `mbstring`, `xml`, `bcmath`
- Composer 2+
- PostgreSQL 15+ (con tablas ya creadas por el backend NestJS)
- Docker (opcional, para levantar Postgres en desarrollo)

## Instalación

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Configurar variables de entorno
cp .env.example .env
php artisan key:generate      # genera APP_KEY automáticamente

# 3. Editar .env con los datos de tu PostgreSQL
#    (las tablas deben existir — créalas desde el backend NestJS)

# 4. (Opcional) Levantar Postgres con Docker para desarrollo
docker compose -f docker-compose.dev.yml up -d

# 5. Iniciar servidor de desarrollo
php artisan serve --port=8080
```

## Variables de entorno

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `APP_NAME` | Nombre de la aplicación | `Shopify Legacy` |
| `APP_ENV` | Entorno (`local`/`production`) | `local` |
| `APP_KEY` | Clave de cifrado Laravel (generar con `key:generate`) | — |
| `APP_DEBUG` | Mostrar errores detallados | `true` |
| `APP_URL` | URL base del servicio | `http://localhost:8080` |
| `DB_CONNECTION` | Driver de BD | `pgsql` |
| `DB_HOST` | Host de PostgreSQL | `127.0.0.1` |
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_DATABASE` | Nombre de la base de datos | `shopify_orders` |
| `DB_USERNAME` | Usuario de PostgreSQL | `shopify_user` |
| `DB_PASSWORD` | Contraseña de PostgreSQL | — |
| `LOG_CHANNEL` | Canal de logs | `stderr` |
| `LOG_LEVEL` | Nivel de log | `debug` |
| `SESSION_DRIVER` | Driver de sesión | `cookie` |
| `CACHE_STORE` | Driver de caché | `array` |
| `QUEUE_CONNECTION` | Driver de colas | `sync` |
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenerar docs en cada request | `true` |
| `L5_SWAGGER_UI_ASSETS_PATH` | Ruta de assets de Swagger UI | (ver .env.example) |

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/legacy/materiales-bajo-stock` | Materiales con stock < 10 |
| `GET` | `/api/documentation` | Swagger UI |
| `GET` | `/api/documentation/api-docs.json` | OpenAPI JSON |

## Respuesta de ejemplo

```json
GET /api/legacy/materiales-bajo-stock

[
  { "material": "BOX_LARGE", "stock": 2 },
  { "material": "FILLER",    "stock": 3 }
]
```

## Estructura relevante

```
app/Http/Controllers/
  Controller.php           ← Info OpenAPI + Server
  InventoryController.php  ← GET /materiales-bajo-stock con atributos OA\*
routes/
  api.php                  ← GET /legacy/materiales-bajo-stock
```

> **Nota de diseño:** Este servicio conecta a la misma base de datos que el backend NestJS pero solo en modo lectura. No ejecuta migraciones propias — depende de las migraciones del backend para que las tablas existan.
