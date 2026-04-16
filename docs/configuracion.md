# Configuración

## Variables de entorno (`.env`)

### Aplicación

| Variable | Ejemplo | Descripción |
|---|---|---|
| `APP_NAME` | `DroniAuth` | Nombre de la aplicación (usado en emails y UI) |
| `APP_ENV` | `production` / `local` | Entorno activo; afecta CSP, HSTS, logs y debug |
| `APP_KEY` | `base64:...` | Clave de encriptación (AES-256); generar con `php artisan key:generate` |
| `APP_DEBUG` | `false` | Mostrar excepciones detalladas; debe ser `false` en producción |
| `APP_URL` | `https://auth.droni.co` | URL base de la aplicación (usada en emails y links firmados) |
| `APP_LOCALE` | `en` | Idioma por defecto |
| `BCRYPT_ROUNDS` | `12` | Rondas de bcrypt para hash de contraseñas (más alto = más seguro pero más lento) |

### Base de datos

#### Producción (SQL Server / Azure)

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `sqlsrv` |
| `DB_URL` | URL de conexión completa: `sqlsrv://user:pass@host:1433?database=db&encrypt=true&trust_server_certificate=false` |

#### Desarrollo (MySQL)

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | Nombre de la base de datos |
| `DB_USERNAME` | Usuario MySQL |
| `DB_PASSWORD` | Contraseña MySQL |

### Sesión, caché y cola

| Variable | Valor | Descripción |
|---|---|---|
| `SESSION_DRIVER` | `database` | Almacena sesiones en la tabla `sessions` |
| `SESSION_LIFETIME` | `120` | Minutos de inactividad antes de expirar la sesión |
| `CACHE_STORE` | `database` | Almacena caché en la tabla `cache` |
| `QUEUE_CONNECTION` | `database` | Cola de trabajos en la tabla `jobs` |

### Correo (Mailjet)

| Variable | Descripción |
|---|---|
| `MAIL_MAILER` | `mailjet` — usa el mailer definido en `config/mail.php` |
| `MAIL_FROM_ADDRESS` | Dirección remitente (ej. `noreply@droni.co`) |
| `MAIL_FROM_NAME` | Nombre remitente (ej. `${APP_NAME}`) |
| `MAILJET_APIKEY` | API Key pública de Mailjet |
| `MAILJET_SECRET` | API Secret de Mailjet |

### Google OAuth (Socialite)

| Variable | Descripción |
|---|---|
| `GOOGLE_CLIENT_ID` | Client ID de la app en Google Cloud Console |
| `GOOGLE_CLIENT_SECRET` | Client Secret de la app en Google Cloud Console |
| `GOOGLE_REDIRECT_URI` | URI de callback registrada: `${APP_URL}/auth/google/callback` |

### Logs

| Variable | Valor típico | Descripción |
|---|---|---|
| `LOG_CHANNEL` | `stack` | Canal de log activo |
| `LOG_STACK` | `single` | Canales que componen el stack |
| `LOG_LEVEL` | `debug` / `error` | Nivel mínimo de log a registrar |

---

## Archivos de configuración

### `config/auth.php`

Define los guards de autenticación y el password broker.

```php
'guards' => [
    'web'  => ['driver' => 'session', 'provider' => 'users'],
    'api'  => ['driver' => 'passport', 'provider' => 'users'],  // ← añadido por Passport
],
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table'    => 'password_reset_tokens',
        'expire'   => 60,    // minutos de validez del token de reset
        'throttle' => 60,    // segundos mínimos entre requests de reset
    ],
],
'password_timeout' => 10800,  // 3 horas para confirmación de contraseña
```

### `config/fortify.php`

Configuración de Laravel Fortify.

| Clave | Valor | Descripción |
|---|---|---|
| `guard` | `web` | Guard de autenticación que usa Fortify |
| `home` | `/dashboard` | Redirección tras autenticación exitosa |
| `lowercase_usernames` | `true` | Convierte emails a minúsculas |
| `features` | `[twoFactorAuthentication]` | Solo 2FA habilitado; resto desactivado para no conflictuar con Breeze |
| `limiters.login` | `login` | Rate limiter para login (5/min por email+IP) |
| `limiters.two-factor` | `two-factor` | Rate limiter para desafío 2FA (5/min por session ID) |

La opción `twoFactorAuthentication` está configurada con:
- `confirm: true` — requiere confirmar el código TOTP para activar
- `confirmPassword: true` — requiere contraseña reciente para habilitar/deshabilitar

### `config/passport.php`

Configuración de Laravel Passport. Los valores en runtime se configuran en `AppServiceProvider::boot()`:

| Configuración | Valor |
|---|---|
| Access token TTL | 15 días |
| Refresh token TTL | 30 días |
| Personal access token TTL | 6 meses |
| Scopes disponibles | `profile`, `email`, `roles`, `admin` |
| Scopes por defecto | `profile`, `email` |
| Guard | `api` (definido en `config/auth.php`) |

### `config/mail.php`

Define el mailer `mailjet` para Mailjet:

```php
'mailjet' => [
    'transport' => 'smtp',
    'host'      => 'in-v3.mailjet.com',
    'port'      => 587,
    'scheme'    => 'smtp',       // STARTTLS auto-negociado en port 587
    'username'  => env('MAILJET_APIKEY'),
    'password'  => env('MAILJET_SECRET'),
    'timeout'   => null,
],
```

**Nota:** El valor correcto es `'scheme' => 'smtp'`, **no** `'tls'`. Symfony Mailer no acepta `'tls'` como esquema; el STARTTLS se negocia automáticamente al conectar al puerto 587.

### `config/services.php`

Credenciales de servicios externos:

```php
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],
```

---

## Bootstrap (`bootstrap/app.php`)

Registra providers, middleware y routing:

```php
->withProviders([
    App\Providers\FortifyServiceProvider::class,
])
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);  // global
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);
})
->withRouting(function (Router $router): void {
    $router->middleware('api')->prefix('api')->group(base_path('routes/api.php'));
    // web y console se cargan automáticamente
})
```

El middleware `SecurityHeaders` se aplica a **todas** las respuestas (incluidas las API).

---

## Notas de seguridad en producción

- `APP_DEBUG=false` — nunca exponer stack traces
- `APP_KEY` generado y no compartido
- `DB_URL` con `encrypt=true` en SQL Server
- `BCRYPT_ROUNDS=12` mínimo (aumentar a 14 en servers con más CPU)
- Verificar que `MAIL_FROM_ADDRESS` esté autenticado en Mailjet con SPF/DKIM
- `GOOGLE_REDIRECT_URI` debe coincidir exactamente con lo registrado en Google Cloud Console
