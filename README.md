# Shopify Orders — Legacy (Laravel 11)

Componente legacy en PHP/Laravel que expone materiales con bajo stock a través de una API REST documentada con Swagger/OpenAPI.

## Stack

- **Laravel 11** + PHP 8.2
- **PostgreSQL** (misma BD que el backend, acceso de solo lectura)
- **L5-Swagger** — documentación OpenAPI 3.0 con PHP 8 Attributes
- Swagger UI en `/api/documentation`

---

## 🐳 Levante con Docker (recomendado)

Solo necesitas **Docker Desktop** instalado.

### 1. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` generado y establece:

```env
DB_HOST=postgres          # nombre del servicio dentro de la red Docker
DB_PORT=5432
DB_DATABASE=shopify_orders
DB_USERNAME=shopify_user
DB_PASSWORD=shopify_pass
```

> **Nota:** Las tablas deben existir previamente. Si estás usando el repositorio del backend en paralelo, las migraciones ya las habrán creado. Si usas solo este repo, levanta Postgres con `docker-compose.dev.yml` y crea las tablas manualmente o desde el backend.

### 2. Levantar Postgres (solo si no tienes uno ya corriendo)

```bash
docker compose -f docker-compose.dev.yml up -d
```

Esto levanta **PostgreSQL 15** en `localhost:5432`.

Verifica que esté sano:

```bash
docker compose -f docker-compose.dev.yml ps
```

### 3. Construir y levantar el contenedor de Laravel

```bash
docker build -t shopify-legacy .
docker run -d \
  --name shopify_legacy \
  -p 8080:8000 \
  --env-file .env \
  shopify-legacy
```

El servicio queda disponible en:
- **API:** http://localhost:8080/api/legacy/materiales-bajo-stock
- **Swagger UI:** http://localhost:8080/api/documentation

### 4. Generar APP_KEY dentro del contenedor (primera vez)

```bash
docker exec shopify_legacy php artisan key:generate
```

### Detener el contenedor

```bash
docker stop shopify_legacy
docker rm shopify_legacy

# Detener Postgres si lo levantaste con dev compose:
docker compose -f docker-compose.dev.yml down
```

---

## 💻 Levante sin Docker (manual)

Requisitos previos: PHP 8.2+, extensiones `pdo`, `pdo_pgsql`, `mbstring`, `xml`, `bcmath` y Composer 2+.

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate     # genera APP_KEY automáticamente

# 3. Editar .env con tus datos de PostgreSQL

# 4. Iniciar servidor
php artisan serve --port=8080
```

---

## Variables de entorno

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `APP_NAME` | Nombre de la aplicación | `Shopify Legacy` |
| `APP_ENV` | Entorno (`local` / `production`) | `local` |
| `APP_KEY` | Clave de cifrado Laravel — generar con `php artisan key:generate` | — |
| `APP_DEBUG` | Mostrar errores detallados | `true` |
| `APP_URL` | URL base del servicio | `http://localhost:8080` |
| `DB_CONNECTION` | Driver de base de datos | `pgsql` |
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
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenerar docs OpenAPI en cada request | `true` |
| `L5_SWAGGER_UI_ASSETS_PATH` | Ruta de assets de Swagger UI | `vendor/swagger-api/swagger-ui/dist/` |

---

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/legacy/materiales-bajo-stock` | Materiales con stock < 10 |
| `GET` | `/api/documentation` | Swagger UI |
| `GET` | `/api/documentation/api-docs.json` | OpenAPI JSON |

---

## Respuesta de ejemplo

```bash
curl http://localhost:8080/api/legacy/materiales-bajo-stock
```

```json
[
  { "material": "BOX_LARGE", "stock": 2 },
  { "material": "FILLER",    "stock": 3 }
]
```

> Devuelve `[]` cuando todos los materiales tienen stock ≥ 10.

---

## Estructura relevante

```
app/Http/Controllers/
  Controller.php           # Declaración OpenAPI: Info + Server (PHP 8 Attributes)
  InventoryController.php  # GET /materiales-bajo-stock con anotaciones OA\*
routes/
  api.php                  # Registra GET /legacy/materiales-bajo-stock
```

---

> **Nota de diseño:** Este servicio conecta a la misma base de datos que el backend NestJS pero en modo **solo lectura**. No ejecuta migraciones propias — las tablas deben ser creadas por el backend.
