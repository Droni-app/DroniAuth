# Frontend

## Tecnología

El frontend es una **SPA con Inertia.js** usando Vue 3 como framework de componentes. No hay una API JSON separada para el frontend — los controladores de Laravel devuelven `Inertia::render()` con props tipadas. El bundle lo genera **Vite** con soporte HMR en desarrollo.

Los estilos usan **Tailwind CSS v4** en modo utility-first, con la librería de componentes interna `@dronico/droni-kit` que provee los componentes base de UI.

---

## Layouts

### `GuestLayout.vue`

Usado para páginas de autenticación (login, registro, forgot-password, etc.). Presenta un contenedor centrado con el logo, sin navegación.

Páginas que lo usan: `Login`, `Register`, `ForgotPassword`, `ResetPassword`, `VerifyEmail`, `TwoFactorChallenge`, `ConfirmPassword`.

### `AuthenticatedLayout.vue`

Usado para páginas de usuarios autenticados. Contiene:

- **`DuiNavbar`** con los ítems de navegación:
  - Dashboard (`/dashboard`) — icono `mdi-view-dashboard-outline`
  - Aplicaciones (`/clients`) — icono `mdi-apps`
- **Acciones en navbar:** toggle de tema claro/oscuro, nombre del usuario (link al perfil), botón Salir
- **Logo:** adapta imagen según tema (`/img/logo.svg` en claro, `/img/logo-w.svg` en oscuro)
- **Main content:** `max-w-7xl` centrado con padding responsive
- **Footer:** créditos + link a `/legal`

El toggle de tema usa el composable `useTheme` (`Composables/useTheme.js`) que persiste la preferencia en `localStorage` y aplica la clase `dark` al `<html>`.

---

## Páginas

### Auth

| Archivo | Ruta | Descripción |
|---|---|---|
| `Auth/Login.vue` | `/login` | Formulario email + password + "recordarme" + link a Google OAuth |
| `Auth/Register.vue` | `/register` | Formulario nombre + email + password + confirmación |
| `Auth/ForgotPassword.vue` | `/forgot-password` | Formulario para solicitar reset de contraseña |
| `Auth/ResetPassword.vue` | `/reset-password/{token}` | Formulario para nueva contraseña con token |
| `Auth/VerifyEmail.vue` | `/verify-email` | Pantalla de aviso con botón "Reenviar email" |
| `Auth/TwoFactorChallenge.vue` | `/two-factor-challenge` | Input para código TOTP o código de recuperación |
| `Auth/ConfirmPassword.vue` | `/confirm-password` | Confirmación de contraseña para acciones sensibles |

### Dashboard

| Archivo | Ruta | Descripción |
|---|---|---|
| `Dashboard.vue` | `/dashboard` | Panel principal; muestra saludo y accesos directos |

### Perfil

| Archivo | Ruta | Descripción |
|---|---|---|
| `Profile/Edit.vue` | `/profile` | Contenedor con tres secciones de formulario |
| `Profile/Partials/UpdateProfileInformationForm.vue` | — | Actualizar nombre y email |
| `Profile/Partials/UpdatePasswordForm.vue` | — | Cambiar contraseña |
| `Profile/Partials/TwoFactorAuthenticationForm.vue` | — | Gestión completa de 2FA |
| `Profile/Partials/DeleteUserForm.vue` | — | Eliminar cuenta permanentemente |

#### `TwoFactorAuthenticationForm.vue` — flujo de estados

El formulario maneja tres estados reactivos principales:

| Estado | Condición | UI mostrada |
|---|---|---|
| 2FA no activo | `!twoFactorEnabled && !confirming` | Botón "Activar 2FA" |
| Activando (confirmación pendiente) | `confirming = true` | QR code + clave secreta + input código + botones "Confirmar" / "Cancelar" |
| 2FA activo | `twoFactorEnabled && !confirming` | Códigos de recuperación + botón "Desactivar 2FA" |

`twoFactorEnabled` se lee de `auth.user.two_factor_enabled` (prop compartida por `HandleInertiaRequests`).

Todos los endpoints Fortify (`/user/two-factor-*`) se consumen con **`axios`** (no con `router` de Inertia), ya que Fortify devuelve JSON, no respuestas Inertia.

### Clientes OAuth

| Archivo | Ruta | Descripción |
|---|---|---|
| `Clients/Index.vue` | `/clients` | Lista de clientes + formulario de creación + modales |

La página recibe tres props del controlador:
- `clients` — array de clientes del usuario
- `flashSecret` — client secret recién generado (solo al crear/regenerar)
- `oauthEndpoints` — URLs de los endpoints OAuth para mostrar en el panel de referencia

Funcionalidades:
- Crear cliente (formulario expandible inline)
- Editar nombre y redirect URIs (modal)
- Copiar Client ID al portapapeles
- Regenerar client secret (modal de confirmación → modal con el nuevo secret)
- Revocar/eliminar cliente (modal de confirmación)
- Copiar endpoints OAuth al portapapeles

El `flashSecret` se muestra en un modal al cargar la página si está presente. El secret **no se puede recuperar** después de cerrar el modal.

### OAuth

| Archivo | Ruta | Descripción |
|---|---|---|
| `OAuth/Authorize.vue` | `/oauth/authorize` | Pantalla de consentimiento OAuth2 |

Muestra el nombre de la aplicación solicitante, la lista de scopes con sus descripciones, y botones "Autorizar" / "Denegar". Los parámetros los inyecta Passport vía el adaptador registrado en `AppServiceProvider`.

### Otros

| Archivo | Ruta | Descripción |
|---|---|---|
| `Welcome.vue` | (sin uso activo) | Página de bienvenida de Laravel (no usada, `/` redirige) |
| `Legal.vue` | `/legal` | Página de términos y condiciones |

---

## Componentes de Droni Kit (`@dronico/droni-kit`)

| Componente | Uso principal |
|---|---|
| `DuiNavbar` | Barra de navegación en `AuthenticatedLayout` |
| `DuiCard` | Contenedor de secciones de formulario |
| `DuiButton` | Todos los botones de acción |
| `DuiInput` | Campos de texto en formularios |
| `DuiModal` | Modales de confirmación y secretos |
| `DuiTooltip` | Tooltips en el navbar y botones |
| `DuiBadge` | Etiquetas de estado (ej. tipo de cliente) |
| `DuiAlert` | Mensajes de estado (éxito, error) |

Los componentes de Droni Kit están registrados globalmente (no requieren import en cada archivo `.vue`).

---

## Props compartidas (Inertia)

El middleware `HandleInertiaRequests` comparte las siguientes props en todas las páginas:

```javascript
{
  auth: {
    user: {
      id, name, email, avatar, email_verified_at,
      two_factor_enabled  // true si two_factor_secret && two_factor_confirmed_at
    }
  },
  flash: {
    message, error    // mensajes flash de sesión
  },
  ziggy: { ... }    // rutas nombradas de Laravel para uso en JS (via tightenco/ziggy)
}
```

### Rutas nombradas en JavaScript

Con `tightenco/ziggy`, todas las rutas nombradas de Laravel están disponibles en JavaScript:

```javascript
route('dashboard')           // → '/dashboard'
route('clients.index')       // → '/clients'
route('two-factor.enable')   // → '/user/two-factor-authentication'
```
