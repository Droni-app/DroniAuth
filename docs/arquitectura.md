# Arquitectura

## Stack tecnológico

### Backend
| Paquete | Versión | Función |
|---|---|---|
| `laravel/framework` | ^13.0 | Framework principal |
| `laravel/fortify` | ^1.36 | Backend de autenticación (2FA, vistas de auth) |
| `laravel/passport` | ^13.7 | Servidor OAuth2 |
| `laravel/socialite` | ^5.26 | Login social (Google) |
| `inertiajs/inertia-laravel` | ^2.0 | Adaptador Inertia para Laravel |
| `tightenco/ziggy` | ^2.0 | Named routes en JavaScript |
| `pragmarx/google2fa` | ^9.0 | Generación y verificación TOTP |
| `bacon/bacon-qr-code` | ^3.1 | Generación de QR codes SVG |

### Frontend
| Paquete | Versión | Función |
|---|---|---|
| `vue` | ^3.4 | Framework UI reactivo |
| `@inertiajs/vue3` | ^2.0 | Adaptador Inertia para Vue 3 |
| `vite` | ^8.0 | Bundler y dev server |
| `tailwindcss` | ^4.2 | Estilos utilitarios |
| `@dronico/droni-kit` | ^1.17 | Librería de componentes UI Droni |
| `axios` | ~1.11-1.14 | Cliente HTTP para llamadas AJAX |

### Infraestructura
| Servicio | Uso |
|---|---|
| Azure SQL Server | Base de datos principal en producción |
| MySQL | Base de datos en desarrollo local |
| Mailjet | Envío de correos transaccionales |
| Queue (database) | Cola de trabajos (emails, tareas async) |

---

## Estructura del proyecto

```
DroniAuth/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Controladores de autenticación (Breeze)
│   │   │   ├── ClientController   # Gestión de clientes OAuth2
│   │   │   ├── ProfileController  # Perfil de usuario
│   │   │   ├── SocialiteController# Google OAuth callback
│   │   │   └── LegalController    # Página legal
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests  # Props compartidas Inertia
│   │   │   └── SecurityHeaders        # Headers de seguridad HTTP
│   │   └── Requests/Auth/
│   │       ├── LoginRequest           # Validación + rate limiting del login
│   │       └── ProfileUpdateRequest   # Validación del perfil
│   ├── Listeners/
│   │   └── RevokeUserTokens       # Revoca tokens OAuth al cambiar contraseña
│   ├── Models/
│   │   └── User.php               # Modelo principal con UUID, 2FA, OAuth
│   ├── Notifications/
│   │   ├── VerifyEmailNotification    # Email de verificación (queued)
│   │   └── ResetPasswordNotification # Email de reset de contraseña (queued)
│   └── Providers/
│       ├── AppServiceProvider     # Config Passport, event listeners
│       └── FortifyServiceProvider # Config Fortify, vistas 2FA
├── bootstrap/
│   └── app.php                    # Bootstrap: providers, middleware, routing
├── config/
│   ├── auth.php                   # Guards, providers, password brokers
│   ├── fortify.php                # Features y configuración de Fortify
│   ├── mail.php                   # Config mailer Mailjet
│   └── passport.php               # Config OAuth2
├── database/
│   └── migrations/                # 10 migraciones (ver base-de-datos.md)
├── resources/
│   ├── js/
│   │   ├── Layouts/               # GuestLayout, AuthenticatedLayout
│   │   └── Pages/                 # Páginas Vue (ver frontend.md)
│   └── views/
│       └── app.blade.php          # Shell HTML principal de Inertia
├── routes/
│   ├── web.php                    # Rutas web principales
│   └── auth.php                   # Rutas de autenticación (Breeze)
└── docs/                          # Esta documentación
```

---

## Decisiones de diseño

### Breeze + Fortify (sin conflicto)
El proyecto usa **Laravel Breeze** para las vistas y controladores de autenticación web (login, registro, reset), y **Laravel Fortify** únicamente para el backend de 2FA (TOTP). Fortify solo tiene habilitado el feature `twoFactorAuthentication`; el resto de features están desactivados para evitar conflictos de rutas con Breeze.

El login de Breeze intercepta manualmente la redirección 2FA:
```
LoginRequest::authenticate() → Auth::attempt() OK
  → user->hasEnabledTwoFactorAuthentication()?
    YES → logout + session[login.id] → /two-factor-challenge (Fortify)
    NO  → session->regenerate() → /dashboard
```

### Inertia.js como SPA
Las páginas son componentes Vue renderizados en el servidor vía Inertia. No hay API JSON separada para el frontend — los controladores devuelven `Inertia::render()` con props tipadas. Las peticiones de datos (QR code, recovery codes) que necesitan JSON usan `axios` directamente.

### UUIDs en lugar de auto-increment
Todos los modelos principales (`User`, clientes OAuth, tokens) usan UUIDs como PK mediante el trait `HasUuids`. Esto evita la enumeración de IDs y permite generar IDs en el cliente si fuera necesario.

### Emails en cola
Todas las notificaciones de email implementan `ShouldQueue`. El driver de cola es `database`, por lo que los jobs se almacenan en la tabla `jobs`. El worker debe estar corriendo para procesar los envíos.

### Tokens OAuth revocados al cambiar contraseña
El evento `PasswordReset` es escuchado por `RevokeUserTokens`, que invalida todos los access tokens y refresh tokens activos del usuario. Cubre tanto el cambio desde el perfil como el reset por email.
