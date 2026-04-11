# Changelog

Todos los cambios relevantes del proyecto DroniAuth se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.1.0/).

---

## [Sin versión] - 2026-04-11 (4)

### Agregado
- Página **Legal** (`/legal`) con Política de privacidad, Protección de datos personales y Uso de cookies:
  - `LegalController` con método `index` que renderiza la vista Inertia
  - `Legal.vue` usando `GuestLayout`
  - Ruta pública `GET /legal` registrada en `web.php`
- **Footer** con copyright y enlace a `/legal` en `AuthenticatedLayout` y `GuestLayout`
- Paquete **`@tailwindcss/typography`** agregado a dependencias
- Assets de imagen: logos (`logo.svg`, `logo.png`, `logo-w.svg`, `logo-w.png`), brand (`brand.svg`, `brand.png`, `brand-w.svg`), imágenes para home y demo, nuevo `favicon.ico`

### Cambiado
- **`GuestLayout.vue`:** fondo adaptado a dark/light mode con variantes `dark:`; el texto "DroniAuth" reemplazado por la imagen `brand.svg` / `brand-w.svg` según el tema activo (usa `useTheme`)
- **`AuthenticatedLayout.vue`:** ítem de navegación "Clientes OAuth" renombrado a "Aplicaciones"
- **`SocialiteController`:** lógica de `callback` mejorada — busca usuario por `email` o `google_id` antes de crear uno nuevo, evitando duplicados cuando el usuario ya existe con contraseña

### Corregido
- Duplicación de cuentas al hacer login con Google si el usuario ya tenía cuenta con email/contraseña

---

## [Sin versión] - 2026-04-11 (3)

### Agregado
- Login con **Google OAuth** usando Laravel Socialite:
  - Columnas `google_id` y `avatar` en la tabla `users`
  - `password` pasa a ser nullable para usuarios que solo usan Google
  - `SocialiteController` con métodos `redirect` y `callback`
  - Rutas `GET /auth/google` y `GET /auth/google/callback`
  - Credenciales configurables vía `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y `GOOGLE_REDIRECT_URI`
  - Botón "Continuar con Google" con logo SVG oficial en la pantalla de login

### Cambiado
- **`Clients/Index.vue`:** reemplazado `DuiBadge` (no existe en droni-kit) por `<span>` estilizados equivalentes
- **Navbar:** logo de marca reemplaza el texto `DroniAuth` en el brand, cambia entre `logo.svg` (claro) y `logo-w.svg` (oscuro)
- **Navbar:** iconos MDI agregados a ítems de navegación (`mdi-view-dashboard-outline`, `mdi-apps`) y a acciones de la derecha (`mdi-account-circle-outline`, `mdi-logout`)

---

## [Sin versión] - 2026-04-11 (2)

### Agregado
- Soporte a CDN de **MDI Icons** (`@mdi/font`) en `app.blade.php`
- Toggle de **modo claro/oscuro** en el navbar:
  - Composable `useTheme.js` que lee `localStorage` y aplica la clase `dark` a `<html>`
  - Predeterminado al tema del sistema (`prefers-color-scheme`) cuando no hay preferencia guardada
  - Escucha cambios del sistema en tiempo real si el usuario no ha seleccionado un tema manualmente
  - Script inline en `<head>` para aplicar el tema antes del render y evitar flash
  - Icono `mdi-weather-sunny` / `mdi-weather-night` alterna según el modo activo

### Cambiado
- **`app.css`:** agregado `@custom-variant dark` para activar dark mode por clase `.dark` en Tailwind v4
- **`AuthenticatedLayout.vue`:** fondo del layout usa variantes `dark:` para adaptarse a ambos modos

---

## [Sin versión] - 2026-04-11

### Agregado
- Trait `HasUuids` en el modelo `User` para generación automática de UUIDs
- Soporte a `DuiCheckbox` en formulario de login (`remember me`) y en el formulario de creación de clientes OAuth (`confidencial`)
- Componente `DuiModal` reemplaza el `Modal` de Laravel Breeze en todos los diálogos:
  - Modal de edición de cliente
  - Modal de confirmación de regeneración de secret
  - Modal de confirmación de revocación de cliente
  - Modal de visualización de nuevo client secret (no closeable)
  - Modal de confirmación de eliminación de cuenta

### Cambiado
- **Migraciones:** columnas `user_id` de tipo `bigint` migradas a `uuid` en las tablas `users`, `sessions`, `oauth_access_tokens`, `oauth_auth_codes` y `oauth_device_codes`
- **Migración `oauth_clients`:** relación polimórfica `owner` cambiada de `nullableMorphs` a `nullableUuidMorphs` para compatibilidad con UUIDs
- **`ClientController`:** reemplazados los métodos deprecados `ClientRepository::forUser()` y `findForUser()` por `$user->oauthApps()`, que usa la relación polimórfica `owner` correcta de Passport v13 (el método `clients()` seguía apuntando a la columna inexistente `user_id`)
- **`Clients/Index.vue`:** corregida la sintaxis de slots de `DuiTable` — el componente pasa las propiedades del row directamente al slot (`{ id, name, ... }`), no envueltas en `{ row }`, lo que causaba que las filas renderizaran vacías
- **`@dronico/droni-kit`:** actualizado de `1.13.x` → `1.14.0` (agrega `DuiCheckbox`) → `1.15.0` (agrega `DuiModal`)

### Corregido
- `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'oauth_clients.user_id'` — causado por el método `clients()` deprecado de Passport v13 que aún referenciaba `user_id`
- `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'owner_id'` — causado por `nullableMorphs('owner')` que creaba `owner_id` como `unsignedBigInteger`, incompatible con UUIDs
- Los clientes OAuth no se listaban en la UI a pesar de existir en la base de datos, debido al error silencioso en los slots de `DuiTable`
