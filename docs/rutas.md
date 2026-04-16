# API & Rutas

## Rutas públicas (sin autenticación)

| Método | Ruta | Nombre | Descripción |
|---|---|---|---|
| GET | `/` | — | Redirige a `/dashboard` si autenticado, si no a `/login` |
| GET | `/legal` | `legal` | Página de términos y condiciones |

---

## Rutas de autenticación (guest)

Solo accesibles para usuarios **no autenticados** (middleware `guest`). Un usuario ya autenticado es redirigido automáticamente.

| Método | Ruta | Nombre | Controlador | Descripción |
|---|---|---|---|---|
| GET | `/register` | `register` | `RegisteredUserController@create` | Formulario de registro |
| POST | `/register` | — | `RegisteredUserController@store` | Procesar registro |
| GET | `/login` | `login` | `AuthenticatedSessionController@create` | Formulario de login |
| POST | `/login` | — | `AuthenticatedSessionController@store` | Procesar login |
| GET | `/forgot-password` | `password.request` | `PasswordResetLinkController@create` | Formulario de olvidé contraseña |
| POST | `/forgot-password` | `password.email` | `PasswordResetLinkController@store` | Enviar email de reset |
| GET | `/reset-password/{token}` | `password.reset` | `NewPasswordController@create` | Formulario de nueva contraseña |
| POST | `/reset-password` | `password.store` | `NewPasswordController@store` | Guardar nueva contraseña |

---

## Rutas de autenticación (auth)

Requieren estar **autenticado** (middleware `auth`).

| Método | Ruta | Nombre | Controlador | Descripción |
|---|---|---|---|---|
| GET | `/verify-email` | `verification.notice` | `EmailVerificationPromptController` | Página "verifica tu email" |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `VerifyEmailController` | Verificar email vía enlace firmado |
| POST | `/email/verification-notification` | `verification.send` | `EmailVerificationNotificationController@store` | Reenviar email de verificación |
| GET | `/confirm-password` | `password.confirm` | `ConfirmablePasswordController@show` | Formulario de confirmación de contraseña |
| POST | `/confirm-password` | — | `ConfirmablePasswordController@store` | Procesar confirmación de contraseña |
| PUT | `/password` | `password.update` | `PasswordController@update` | Cambiar contraseña desde el perfil |
| POST | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | Cerrar sesión |

---

## Rutas de 2FA (Fortify)

Registradas por Fortify (feature `twoFactorAuthentication`). Requieren estado de sesión especial.

| Método | Ruta | Nombre | Descripción |
|---|---|---|---|
| GET | `/two-factor-challenge` | `two-factor.login` | Formulario del desafío TOTP |
| POST | `/two-factor-challenge` | — | Verificar código TOTP o código de recuperación |
| POST | `/user/two-factor-authentication` | `two-factor.enable` | Activar 2FA (genera secret + recovery codes) |
| DELETE | `/user/two-factor-authentication` | `two-factor.disable` | Desactivar 2FA |
| POST | `/user/confirmed-two-factor-authentication` | `two-factor.confirm` | Confirmar activación de 2FA con código TOTP |
| GET | `/user/two-factor-qr-code` | `two-factor.qr-code` | Obtener SVG del QR code |
| GET | `/user/two-factor-secret-key` | `two-factor.secret-key` | Obtener clave secreta en texto |
| GET | `/user/two-factor-recovery-codes` | `two-factor.recovery-codes` | Listar códigos de recuperación |
| POST | `/user/two-factor-recovery-codes` | — | Regenerar códigos de recuperación |

> Los endpoints `/user/two-factor-*` devuelven JSON. Deben consumirse con `axios`, no con `router` de Inertia.

---

## Rutas protegidas (auth + verified)

Requieren estar autenticado **y** con email verificado.

| Método | Ruta | Nombre | Controlador | Descripción |
|---|---|---|---|---|
| GET | `/dashboard` | `dashboard` | (closure) | Panel principal |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | Editar perfil |
| PATCH | `/profile` | `profile.update` | `ProfileController@update` | Actualizar nombre/email |
| DELETE | `/profile` | `profile.destroy` | `ProfileController@destroy` | Eliminar cuenta |
| GET | `/clients` | `clients.index` | `ClientController@index` | Listar clientes OAuth |
| POST | `/clients` | `clients.store` | `ClientController@store` | Crear cliente OAuth |
| PUT | `/clients/{client}` | `clients.update` | `ClientController@update` | Editar cliente OAuth |
| POST | `/clients/{client}/secret` | `clients.regenerate-secret` | `ClientController@regenerateSecret` | Regenerar client secret |
| DELETE | `/clients/{client}` | `clients.destroy` | `ClientController@destroy` | Revocar cliente OAuth |

---

## Rutas Google OAuth (Socialite)

| Método | Ruta | Nombre | Controlador | Descripción |
|---|---|---|---|---|
| GET | `/auth/google` | `auth.google` | `SocialiteController@redirect` | Redirigir a consent screen de Google |
| GET | `/auth/google/callback` | `auth.google.callback` | `SocialiteController@callback` | Callback tras autenticación con Google |

---

## Endpoints OAuth2 (Passport)

Registrados automáticamente por `Laravel\Passport\Http\Controllers`.

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/oauth/authorize` | Pantalla de consentimiento del usuario |
| POST | `/oauth/authorize` | Aprobar autorización |
| DELETE | `/oauth/authorize` | Denegar autorización |
| POST | `/oauth/token` | Intercambiar código/credenciales por token |
| DELETE | `/oauth/tokens/{token_id}` | Revocar un token específico |
| GET | `/oauth/tokens` | Listar tokens activos del usuario |

### Endpoint de usuario autenticado

| Método | Ruta | Middleware | Descripción |
|---|---|---|---|
| GET | `/api/user` | `auth:api` | Devuelve datos del usuario autenticado vía Bearer token |

**Ejemplo de respuesta:**
```json
{
  "id": "uuid",
  "name": "Nombre Usuario",
  "email": "usuario@ejemplo.com",
  "avatar": "https://lh3.googleusercontent.com/...",
  "email_verified_at": "2026-04-11T..."
}
```

---

## Notas sobre middleware

| Middleware | Comportamiento |
|---|---|
| `guest` | Redirige a `/dashboard` si ya autenticado |
| `auth` | Redirige a `/login` si no autenticado |
| `verified` | Redirige a `/verify-email` si email no verificado |
| `signed` | Valida que la URL tenga firma válida (no modificada) |
| `throttle:6,1` | Máximo 6 requests por minuto |
| `auth:api` | Valida Bearer token OAuth (Passport) |
