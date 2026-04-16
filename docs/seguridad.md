# Seguridad

## Headers HTTP

Implementados en `app/Http/Middleware/SecurityHeaders.php`, aplicado globalmente en `bootstrap/app.php`.

| Header | Valor | Propósito |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | Previene MIME sniffing |
| `X-Frame-Options` | `SAMEORIGIN` | Previene clickjacking (solo iframes del mismo origen) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limita qué URL se envía como referrer en requests cross-origin |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Fuerza HTTPS por 1 año (solo en producción) |
| `Content-Security-Policy` | Ver tabla abajo | Restringe fuentes de recursos cargables |
| `X-Powered-By` | *(eliminado)* | Oculta que el servidor usa PHP |
| `Server` | *(eliminado)* | Oculta el software del servidor |

### Content Security Policy (CSP)

| Directiva | Fuentes permitidas |
|---|---|
| `default-src` | `'self'` |
| `script-src` | `'self' 'unsafe-inline'` + Vite en dev |
| `script-src-elem` | `'self' 'unsafe-inline'` + Vite en dev |
| `style-src` | `'self' 'unsafe-inline' fonts.bunny.net cdn.jsdelivr.net` + Vite en dev |
| `style-src-elem` | `'self' 'unsafe-inline' fonts.bunny.net cdn.jsdelivr.net` + Vite en dev |
| `font-src` | `'self' data: fonts.bunny.net cdn.jsdelivr.net` |
| `img-src` | `'self' data: https:` (cualquier HTTPS) |
| `connect-src` | `'self' cdn.jsdelivr.net` + Vite en dev |
| `frame-ancestors` | `'self'` |
| `base-uri` | `'self'` |
| `form-action` | `'self'` |
| `object-src` | `'none'` |

**En desarrollo** (`APP_ENV != production`), se agregan automáticamente `http://127.0.0.1:5173` y `ws://127.0.0.1:5173` a las directivas relevantes para permitir el servidor HMR de Vite.

---

## Rate Limiting

### Login (`/login`)

Configurado en `FortifyServiceProvider` usando el rate limiter `login` de Laravel:

- **Límite:** 5 intentos por minuto
- **Clave:** `email|IP` (combinación del email enviado y la IP del cliente)
- **Comportamiento tras exceder:** retorna error 429 con el tiempo de espera restante (`Too Many Attempts`)
- **Liberación:** automática al transcurrir la ventana de 1 minuto

### Desafío 2FA (`/two-factor-challenge`)

- **Límite:** 5 intentos por minuto
- **Clave:** `session[login.id]` (ID del usuario pendiente de autenticación)

### Reenvío de email de verificación (`/email/verification-notification`)

- **Límite:** 6 solicitudes por minuto (middleware `throttle:6,1` en la ruta)

### Verificación de email (`/verify-email/{id}/{hash}`)

- **Límite:** 6 solicitudes por minuto (middleware `throttle:6,1`)
- Adicionalmente requiere firma URL válida (middleware `signed`)

---

## Políticas de contraseña

Las contraseñas se validan usando `Password::defaults()`, configurado en Laravel con los siguientes requisitos:

- Mínimo **8 caracteres**
- Al menos **1 letra mayúscula**
- Al menos **1 número**
- Al menos **1 símbolo**
- No puede ser una contraseña comprometida (si se habilita `uncompromised()`)

El hash se genera con **bcrypt** con 12 rondas (`BCRYPT_ROUNDS=12`).

Las contraseñas nunca se almacenan en texto plano. En el modelo `User`, el campo `password` tiene el cast `'hashed'`, que aplica `Hash::make()` automáticamente al asignarlo.

---

## Autenticación de dos factores (TOTP)

Implementado con **Laravel Fortify** + `pragmarx/google2fa` + `bacon/bacon-qr-code`.

### Algoritmo

| Parámetro | Valor |
|---|---|
| Algoritmo | TOTP (RFC 6238) |
| Dígitos | 6 |
| Periodo | 30 segundos |
| Hash | SHA1 |

### Almacenamiento

El secreto TOTP y los códigos de recuperación se almacenan **encriptados** en la base de datos (Laravel encryption con `APP_KEY`). No son legibles si la base de datos es comprometida sin la clave de aplicación.

| Campo | Contenido |
|---|---|
| `two_factor_secret` | Secreto base32 encriptado |
| `two_factor_recovery_codes` | JSON array de 8 códigos encriptados |
| `two_factor_confirmed_at` | Timestamp de activación confirmada; `null` = no activo |

### Activación con confirmación obligatoria

El feature está configurado con `confirmPassword: true`, lo que significa que activar o desactivar 2FA requiere que el usuario haya confirmado su contraseña en los últimos **3 horas** (`auth.password_timeout = 10800`).

### Códigos de recuperación

- Se generan **8 códigos** al activar 2FA
- Cada código es de un solo uso; al usarse queda marcado internamente como consumido
- Pueden regenerarse desde el perfil (también requiere confirmación de contraseña)

---

## Revocación de tokens OAuth

Cuando un usuario cambia su contraseña (desde el perfil o mediante reset por email), el evento `PasswordReset` dispara el listener `RevokeUserTokens`:

1. Busca todos los `oauth_access_tokens` del usuario con `revoked = false`
2. Los marca como `revoked = true`
3. Marca los `oauth_refresh_tokens` asociados como `revoked = true`

Esto fuerza a todas las aplicaciones conectadas a re-autenticarse vía OAuth.

---

## Sesiones

- **Driver:** `database` (tabla `sessions`)
- **Lifetime:** 120 minutos de inactividad (`SESSION_LIFETIME=120`)
- **Regeneración:** el session ID se regenera en cada login exitoso (`session()->regenerate()`) para prevenir session fixation
- **Confirmación de contraseña:** la sesión registra `auth.password_confirmed_at`; si han pasado más de 3 horas, se requiere reconfirmar para acciones sensibles

---

## UUIDs en lugar de IDs incrementales

Todos los modelos principales usan UUIDs como PK (trait `HasUuids`):

- Previene **enumeración de IDs** (un atacante no puede predecir IDs válidos)
- Permite generar IDs en el cliente si fuera necesario
- Afecta: `users`, `oauth_clients`, `oauth_access_tokens` (indirectamente)
