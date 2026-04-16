# Autenticación

## Registro con email y contraseña

```
POST /register
  ↓ Valida: name, email (único), password (confirmado)
  ↓ User::create()
  ↓ event(new Registered($user))  → envía VerifyEmailNotification (queued)
  ↓ Auth::login($user)
  → redirect /verify-email
```

**Validaciones del registro:**
- `name`: requerido, string, máx. 255 caracteres
- `email`: requerido, único en `users`, lowercase, formato email
- `password`: requerido, confirmado, cumple `Password::defaults()` (mín. 8 chars, mayúsculas, números, símbolos)

Después del registro el usuario queda autenticado pero sin acceso al dashboard hasta verificar su correo.

---

## Login con email y contraseña

```
POST /login
  ↓ LoginRequest::authenticate()
    ↓ RateLimiter: 5 intentos/min por email|IP
    ↓ Auth::attempt(email, password, remember)
  ↓ ¿hasEnabledTwoFactorAuthentication()?
    SÍ → Auth::logout()
         session[login.id, login.remember]
         → redirect /two-factor-challenge
    NO → session->regenerate()
         → redirect /dashboard
```

**Rate limiting de login:**  
Tras 5 intentos fallidos por el mismo email + IP en un minuto, se bloquea el intento y se retorna el tiempo restante de espera. Se libera automáticamente cuando transcurre la ventana de tiempo.

---

## Verificación de correo electrónico

El modelo `User` implementa `MustVerifyEmail`. Todas las rutas marcadas con el middleware `verified` redirigen a `/verify-email` si el usuario no ha verificado su correo.

**Rutas protegidas con `verified`:**
- `/dashboard`
- `/profile` (y sub-rutas)
- `/clients` (y sub-rutas)

**Flujo de verificación:**
```
1. Usuario recibe email con enlace firmado: /verify-email/{id}/{hash}
2. GET /verify-email/{id}/{hash}
   ↓ Verifica firma y hash del email
   ↓ $user->markEmailAsVerified()
   ↓ event(new Verified($user))
   → redirect /dashboard?verified=1
```

**Reenvío del email:**
```
POST /email/verification-notification
  ↓ Throttle: 6 req/min
  ↓ $user->sendEmailVerificationNotification()  → VerifyEmailNotification (queued)
  → back() with status 'verification-link-sent'
```

**Nota:** Los usuarios que se registran con Google OAuth tienen `email_verified_at` marcado automáticamente en el callback; nunca pasan por este flujo.

---

## Reset de contraseña

```
1. GET  /forgot-password       → formulario de email
2. POST /forgot-password
     ↓ Password::sendResetLink(email)
     ↓ Envía ResetPasswordNotification (queued) con enlace firmado
     → status message

3. GET  /reset-password/{token}  → formulario de nueva contraseña
4. POST /reset-password
     ↓ Password::reset(token, email, password)
     ↓ $user->password = Hash::make(password)
     ↓ $user->remember_token = Str::random(60)
     ↓ event(new PasswordReset($user))
         → RevokeUserTokens: revoca todos los OAuth tokens activos
         → ResetPasswordNotification (queued)
     → redirect /login with status
```

**Expiración del token:** 60 minutos (configurable en `config/auth.php` → `passwords.users.expire`).  
**Throttle:** 60 segundos entre cada solicitud de reset por usuario.

---

## Cambio de contraseña desde el perfil

```
PUT /password
  ↓ Valida: current_password (verifica contra hash actual), password (confirmado, reglas)
  ↓ $user->update(['password' => Hash::make(password)])
  ↓ event(new PasswordReset($user))
      → RevokeUserTokens: revoca todos los OAuth tokens activos
  → back()
```

---

## Login con Google OAuth

```
GET /auth/google
  → Socialite::driver('google')->stateless()->redirect()
  → Google consent screen

GET /auth/google/callback
  ↓ Socialite::driver('google')->stateless()->user()
  ↓ Busca User por email O google_id
  SI existe:
    ↓ update(google_id, avatar, email_verified_at ?? now())
  SI no existe:
    ↓ User::create(name, email, google_id, avatar, email_verified_at: now())
  ↓ Auth::login($user, remember: true)
  → redirect /dashboard
```

**Nota sobre `stateless()`:** Se usa `stateless()` en ambos métodos para evitar el `InvalidStateException` causado por desincronización de sesión durante el redirect OAuth. Es seguro porque Google valida el `client_id` y el `redirect_uri` exacto registrado.

**Datos que se guardan de Google:**
- `google_id`: ID único de Google (para futuros logins)
- `avatar`: URL de la foto de perfil
- `email_verified_at`: marcado como `now()` si no estaba verificado

---

## Autenticación de dos factores (2FA / TOTP)

### Activar 2FA desde el perfil

```
POST /user/two-factor-authentication
  ↓ Requiere confirmación de contraseña reciente (423 si no)
  ↓ Genera two_factor_secret (TOTP)
  ↓ Genera two_factor_recovery_codes (8 códigos)
  ↓ Guarda en BD (sin two_factor_confirmed_at aún)

GET /user/two-factor-qr-code    → SVG del QR code
GET /user/two-factor-secret-key → Clave secreta en texto
GET /user/two-factor-recovery-codes → Listado de códigos

POST /user/confirmed-two-factor-authentication
  ↓ Valida código TOTP contra two_factor_secret
  ↓ $user->two_factor_confirmed_at = now()
  ↓ 2FA activo: hasEnabledTwoFactorAuthentication() = true
```

`hasEnabledTwoFactorAuthentication()` devuelve `true` solo cuando ambos `two_factor_secret` y `two_factor_confirmed_at` no son nulos.

### Desafío 2FA en el login

Una vez activo, el login redirige al desafío antes de completar la autenticación:

```
GET /two-factor-challenge  → página TwoFactorChallenge.vue

POST /two-factor-challenge
  ↓ Lee session[login.id] → carga el usuario pendiente
  ↓ Verifica code (TOTP de 6 dígitos) O recovery_code
  ↓ Si recovery_code: lo marca como usado (no reutilizable)
  ↓ Auth::login($user, session[login.remember])
  ↓ session->regenerate()
  → redirect /dashboard
```

### Deshabilitar 2FA

```
DELETE /user/two-factor-authentication
  ↓ Requiere confirmación de contraseña reciente (423 si no)
  ↓ $user->two_factor_secret = null
  ↓ $user->two_factor_recovery_codes = null
  ↓ $user->two_factor_confirmed_at = null
```

### Regenerar códigos de recuperación

```
POST /user/two-factor-recovery-codes
  ↓ Genera nuevos 8 códigos
  ↓ Reemplaza two_factor_recovery_codes en BD
  → Devuelve array de códigos en JSON
```

---

## Confirmación de contraseña

Algunos endpoints sensibles (como habilitar/deshabilitar 2FA) requieren que el usuario haya confirmado su contraseña recientemente.

```
GET  /confirm-password → página ConfirmPassword.vue
POST /confirm-password
  ↓ Verifica password contra hash actual
  ↓ session[auth.password_confirmed_at] = time()
  → redirect /dashboard
```

La sesión de confirmación dura **3 horas** (`auth.password_timeout = 10800`). Si intenta una acción que requiere confirmación y han pasado más de 3 horas, se redirige a `/confirm-password`.

Si la petición viene de `axios` (AJAX), Fortify devuelve `HTTP 423` en lugar de redirigir. El frontend maneja este caso redirigiendo a `/confirm-password` mediante `router.visit()`.
