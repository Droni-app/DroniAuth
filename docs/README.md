# DroniAuth — Documentación técnica

DroniAuth es el servidor central de identidad y autorización del ecosistema Droni. Gestiona el registro y autenticación de usuarios, la autorización OAuth2 para aplicaciones de terceros, y sirve como proveedor de identidad (IdP) para todos los servicios de Droni.

## Índice

| Documento | Descripción |
|---|---|
| [Arquitectura](arquitectura.md) | Stack tecnológico, estructura del proyecto y decisiones de diseño |
| [Autenticación](autenticacion.md) | Flujos de login, registro, reset de contraseña, Google OAuth y 2FA |
| [OAuth2](oauth2.md) | Servidor OAuth2, grant types, scopes, endpoints y gestión de clientes |
| [Base de datos](base-de-datos.md) | Esquema de tablas, migraciones y relaciones |
| [Seguridad](seguridad.md) | Headers HTTP, rate limiting, políticas de contraseñas y TOTP |
| [API & Rutas](rutas.md) | Referencia completa de rutas web y endpoints OAuth |
| [Frontend](frontend.md) | Páginas Vue, layouts y componentes |
| [Configuración](configuracion.md) | Variables de entorno y archivos de configuración |
| [Desarrollo](desarrollo.md) | Setup local, comandos, testing y despliegue |

## Resumen rápido

```
Stack:      Laravel 13 + Inertia.js + Vue 3 + Tailwind CSS
Auth:       Laravel Breeze + Fortify (2FA/TOTP)
OAuth2:     Laravel Passport
Social:     Laravel Socialite (Google)
Mail:       Mailjet (SMTP)
DB:         SQL Server (Azure) / MySQL (local)
Queue:      Database driver
UI:         @dronico/droni-kit
```

## Flujos principales

```
Registro ──────────────────► Verificar email ──► Dashboard
Login (email+password) ────► 2FA challenge? ───► Dashboard
Login (Google OAuth) ──────────────────────────► Dashboard
Aplicación externa ────────► /oauth/authorize ──► Token ──► API
```
