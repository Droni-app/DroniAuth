# OAuth2

DroniAuth implementa un servidor OAuth2 completo usando **Laravel Passport**. Permite que aplicaciones externas (clientes) soliciten acceso a los recursos de un usuario de Droni de forma segura y con consentimiento explícito.

---

## Endpoints

| Endpoint | Método | Descripción |
|---|---|---|
| `/oauth/authorize` | GET | Pantalla de autorización (consentimiento del usuario) |
| `/oauth/token` | POST | Intercambiar código o credenciales por token |
| `/oauth/tokens/{token_id}` | DELETE | Revocar un token específico |
| `/api/user` | GET | Obtener información del usuario autenticado |

---

## Grant types soportados

### Authorization Code Grant
Para aplicaciones con interfaz de usuario (SPAs, apps móviles, servicios web con usuarios reales). El usuario ve la pantalla de autorización y aprueba el acceso.

```
1. Cliente redirige al usuario:
   GET /oauth/authorize
     ?client_id=xxx
     &redirect_uri=https://miapp.com/callback
     &response_type=code
     &scope=profile email
     &state=random_string

2. Usuario inicia sesión (si no lo está)

3. Usuario ve pantalla de consentimiento con los scopes solicitados

4. Usuario aprueba → DroniAuth redirige:
   https://miapp.com/callback?code=AUTH_CODE&state=random_string

5. Cliente intercambia el código (backend):
   POST /oauth/token
   {
     "grant_type": "authorization_code",
     "client_id": "xxx",
     "client_secret": "yyy",        ← solo para clientes confidenciales
     "redirect_uri": "https://miapp.com/callback",
     "code": "AUTH_CODE"
   }

6. Respuesta:
   {
     "token_type": "Bearer",
     "expires_in": 1296000,         ← 15 días en segundos
     "access_token": "eyJ...",
     "refresh_token": "def..."
   }
```

### Client Credentials Grant
Para comunicación servidor a servidor, sin usuario involucrado. El cliente se autentica con sus propias credenciales.

```
POST /oauth/token
{
  "grant_type": "client_credentials",
  "client_id": "xxx",
  "client_secret": "yyy",
  "scope": "profile"
}

Respuesta:
{
  "token_type": "Bearer",
  "expires_in": 1296000,
  "access_token": "eyJ..."
}
```

---

## Scopes disponibles

| Scope | Descripción |
|---|---|
| `profile` | Ver información del perfil del usuario (nombre, avatar) |
| `email` | Ver dirección de email del usuario |
| `roles` | Ver roles y permisos asignados al usuario |
| `admin` | Acceso administrativo al sistema |

**Scopes por defecto:** `profile`, `email` — si el cliente no especifica scopes, se asignan estos dos.

---

## Expiración de tokens

| Tipo | Duración |
|---|---|
| Access Token | 15 días |
| Refresh Token | 30 días |
| Personal Access Token | 6 meses |
| Authorization Code | Segundos (uso único) |

---

## Usar el access token

Incluir en el header de cada petición a la API:

```http
GET /api/user
Authorization: Bearer eyJ...
```

---

## Renovar el access token

```
POST /oauth/token
{
  "grant_type": "refresh_token",
  "refresh_token": "def...",
  "client_id": "xxx",
  "client_secret": "yyy",
  "scope": "profile email"
}
```

---

## Gestión de clientes desde la UI

Los usuarios autenticados pueden gestionar sus propios clientes OAuth en `/clients`.

### Crear un cliente

```
POST /clients
{
  "name": "Mi aplicación",
  "grant_type": "authorization_code",   // o "client_credentials"
  "redirect_uris": "https://miapp.com/callback\nhttps://miapp.com/callback2",
  "confidential": true
}
```

El `client_secret` se genera automáticamente y se muestra **una sola vez** en un modal al crear el cliente o al regenerarlo. No se puede recuperar después.

### Tipos de clientes

| Tipo | `confidential` | Tiene secret | Uso típico |
|---|---|---|---|
| Confidencial | `true` | Sí | Backend web, server-side apps |
| Público | `false` | No | SPAs, apps móviles (sin backend seguro) |

### Redirect URIs
- Múltiples URIs separadas por salto de línea
- Solo aplican a `authorization_code` grant
- Deben coincidir exactamente con el `redirect_uri` en la petición de autorización

---

## Pantalla de autorización

Cuando una aplicación solicita autorización, el usuario ve la página `OAuth/Authorize.vue` con:
- Nombre de la aplicación solicitante
- Lista de scopes solicitados con sus descripciones
- Botones "Autorizar" y "Denegar"

La pantalla usa el adaptador de Passport registrado en `AppServiceProvider`:
```php
Passport::authorizationView(fn ($params) => Inertia::render('OAuth/Authorize', $params));
```

---

## Revocar tokens al cambiar contraseña

Cuando un usuario cambia su contraseña (ya sea desde el perfil o mediante el flujo de reset), **todos sus access tokens y refresh tokens OAuth se revocan automáticamente**. Esto fuerza a todas las aplicaciones conectadas a re-autenticarse.

Ver implementación en [RevokeUserTokens](../app/Listeners/RevokeUserTokens.php).
