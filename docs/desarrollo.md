# Desarrollo

## Requisitos

| Herramienta | Versión mínima |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 22+ |
| MySQL | 8.0+ (desarrollo local) |
| Git | cualquier versión reciente |

---

## Setup inicial

```bash
# 1. Clonar el repositorio
git clone <repo-url> DroniAuth
cd DroniAuth

# 2. Instalar dependencias PHP y JS, crear .env, generar clave y migrar
composer run setup

# El script `setup` ejecuta:
#   composer install
#   cp .env.example .env (si no existe)
#   php artisan key:generate
#   php artisan migrate --force
#   npm install --ignore-scripts
#   npm run build
```

O paso a paso:

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configurar DB_* y demás variables en .env
php artisan migrate

npm install
npm run build
```

---

## Variables de entorno mínimas para desarrollo local

```dotenv
APP_NAME=DroniAuth
APP_ENV=local
APP_KEY=         # generado por artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=droniauth
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log    # en desarrollo usar 'log' para ver emails en storage/logs

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

> Para pruebas de email reales, configurar `MAIL_MAILER=mailjet` con las credenciales de Mailjet.

---

## Comandos de desarrollo

### Servidor completo (recomendado)

```bash
composer run dev
```

Lanza en paralelo (con colores por proceso):
- `php artisan serve` — servidor PHP en `http://localhost:8000`
- `php artisan queue:listen --tries=1 --timeout=0` — worker de cola (emails, jobs)
- `php artisan pail --timeout=0` — tail de logs en tiempo real
- `npm run dev` — Vite HMR en `http://localhost:5173`

### Comandos individuales

| Comando | Descripción |
|---|---|
| `php artisan serve` | Servidor de desarrollo PHP |
| `npm run dev` | Vite en modo desarrollo con HMR |
| `npm run build` | Build de producción en `public/build/` |
| `php artisan queue:listen` | Procesar jobs de la cola (emails) |
| `php artisan queue:work` | Procesar jobs (sin reload automático) |
| `php artisan pail` | Ver logs en tiempo real |
| `php artisan migrate` | Ejecutar migraciones pendientes |
| `php artisan migrate:fresh --seed` | Recrear toda la base de datos |
| `php artisan tinker` | REPL interactivo de Laravel |

---

## Testing

```bash
# Correr todos los tests
composer run test

# El script ejecuta:
#   php artisan config:clear
#   php artisan test

# Con cobertura (requiere Xdebug o PCOV)
php artisan test --coverage

# Filtrar por nombre
php artisan test --filter NombreDelTest
```

Los tests están en `tests/` (Feature y Unit). La configuración está en `phpunit.xml`.

---

## Migraciones

```bash
# Ver estado de migraciones
php artisan migrate:status

# Ejecutar pendientes
php artisan migrate

# Rollback último batch
php artisan migrate:rollback

# Recrear toda la BD (destructivo)
php artisan migrate:fresh

# Con seeders
php artisan migrate:fresh --seed
```

---

## Cola de trabajos

La cola usa el driver `database`. Los emails (verificación, reset de contraseña) son **queued** — se agregan a la tabla `jobs` y los procesa el worker.

```bash
# Modo desarrollo (procesa en loop, recarga código automáticamente)
php artisan queue:listen --tries=1

# Modo producción (no recarga código)
php artisan queue:work --tries=3

# Ver jobs fallidos
php artisan queue:failed

# Reintentar un job fallido por ID
php artisan queue:retry <id>

# Reintentar todos los fallidos
php artisan queue:retry all
```

> Si los emails no llegan en desarrollo, verificar que el worker esté corriendo y revisar `storage/logs/laravel.log`.

---

## Passport (OAuth2)

```bash
# Instalar claves de encriptación de tokens (necesario en setup inicial)
php artisan passport:install

# Generar solo las claves (sin crear clientes por defecto)
php artisan passport:keys

# Listar clientes OAuth existentes
php artisan passport:client --list
```

Las claves se almacenan en `storage/` (`oauth-private.key`, `oauth-public.key`). No commitear estos archivos.

---

## Despliegue a producción

```bash
# 1. Pull del código
git pull origin main

# 2. Instalar dependencias (sin dev)
composer install --no-dev --optimize-autoloader

# 3. Build de assets
npm ci
npm run build

# 4. Optimizaciones de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Migraciones
php artisan migrate --force

# 6. Reiniciar workers de cola
php artisan queue:restart
```

### Variables de entorno en producción

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://auth.droni.co

DB_CONNECTION=sqlsrv
DB_URL="sqlsrv://user:pass@dronicluster.database.windows.net:1433?database=db&encrypt=true"

MAIL_MAILER=mailjet
```

---

## Estructura de almacenamiento relevante

```
storage/
├── logs/
│   └── laravel.log          # Logs de la aplicación
├── app/
│   └── private/             # Archivos privados de la app
└── framework/
    ├── cache/               # Caché del framework
    ├── sessions/            # (no usada — se usa DB)
    └── views/               # Vistas compiladas (Blade)

storage/oauth-private.key    # Clave privada de Passport (no commitear)
storage/oauth-public.key     # Clave pública de Passport (no commitear)
```

---

## Troubleshooting frecuente

| Problema | Solución |
|---|---|
| Emails no se envían | Verificar que el worker esté corriendo (`queue:listen`) y revisar `LOG_LEVEL` |
| `InvalidStateException` en Google OAuth | Ya resuelto con `stateless()` — si persiste, limpiar cookies de sesión |
| `UnsupportedSchemeException` en Mailjet | Verificar que `scheme` sea `'smtp'`, no `'tls'` en `config/mail.php` |
| CSP bloqueando recursos | En dev, verificar que `APP_ENV=local` para incluir Vite en CSP |
| 2FA no activa tras confirmar código | Verificar que el worker procesa jobs (o usar `QUEUE_CONNECTION=sync` para tests) |
| `BindingResolutionException` en 2FA | Verificar que `Fortify::confirmPasswordView()` esté registrado en `FortifyServiceProvider` |
| Tokens Passport inválidos | Verificar que `storage/oauth-private.key` y `oauth-public.key` existen; si no, correr `passport:keys` |
