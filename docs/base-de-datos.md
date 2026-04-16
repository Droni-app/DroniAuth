# Base de datos

## Tecnología

| Entorno | Motor | Host |
|---|---|---|
| Producción | SQL Server (Azure) | dronicluster.database.windows.net:1433 |
| Desarrollo | MySQL | localhost:3306 |

La sesión, caché y cola se almacenan también en la base de datos (`SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`).

---

## Migraciones

| Archivo | Descripción |
|---|---|
| `0001_01_01_000000_create_users_table` | Tablas `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table` | Tablas `cache` y `cache_locks` |
| `0001_01_01_000002_create_jobs_table` | Tablas `jobs`, `job_batches`, `failed_jobs` |
| `2026_04_11_054641_create_oauth_auth_codes_table` | Códigos de autorización OAuth |
| `2026_04_11_054642_create_oauth_access_tokens_table` | Access tokens OAuth |
| `2026_04_11_054643_create_oauth_refresh_tokens_table` | Refresh tokens OAuth |
| `2026_04_11_054644_create_oauth_clients_table` | Clientes OAuth registrados |
| `2026_04_11_054645_create_oauth_device_codes_table` | Códigos de dispositivo OAuth |
| `2026_04_11_072231_add_google_fields_to_users_table` | Campos `google_id` y `avatar` en users |
| `2026_04_16_025845_add_two_factor_columns_to_users_table` | Campos TOTP en users |

---

## Esquema de tablas

### `users`

| Columna | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | UUID | No | Clave primaria (UUID generado automáticamente) |
| `name` | string | No | Nombre completo del usuario |
| `email` | string | No | Email único (lowercase) |
| `email_verified_at` | timestamp | Sí | Fecha de verificación; `null` = no verificado |
| `password` | string | **Sí** | Hash bcrypt; `null` para usuarios solo-OAuth |
| `google_id` | string | Sí | ID de Google OAuth (único si presente) |
| `avatar` | string | Sí | URL de la foto de perfil (de Google) |
| `remember_token` | string(100) | Sí | Token "recordarme" de sesión |
| `two_factor_secret` | text | Sí | Secreto TOTP encriptado |
| `two_factor_recovery_codes` | text | Sí | JSON con códigos de recuperación encriptados |
| `two_factor_confirmed_at` | timestamp | Sí | Fecha de activación de 2FA; `null` = no activo |
| `created_at` / `updated_at` | timestamp | — | Timestamps automáticos |

**Índices:** `email` (unique), `google_id` (unique)

**Nota:** `password` es nullable para permitir usuarios que solo se autentican con Google y nunca establecieron una contraseña.

---

### `oauth_clients`

| Columna | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | UUID | No | Clave primaria |
| `owner_id` | UUID | Sí | ID del usuario propietario |
| `owner_type` | string | Sí | Tipo del propietario (`App\Models\User`) |
| `name` | string | No | Nombre descriptivo del cliente |
| `secret` | string | Sí | Client secret hasheado; `null` para clientes públicos |
| `provider` | string | Sí | Proveedor social si aplica |
| `redirect_uris` | text | No | JSON array de URIs de redirección permitidas |
| `grant_types` | text | No | JSON array con los grant types habilitados |
| `revoked` | boolean | No | `true` = cliente revocado/eliminado (soft delete) |
| `created_at` / `updated_at` | timestamp | — | Timestamps automáticos |

---

### `oauth_access_tokens`

| Columna | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | char(80) | No | Token identifier único |
| `user_id` | UUID | Sí | Usuario al que pertenece; `null` para client_credentials |
| `client_id` | UUID | No | Cliente que emitió el token |
| `name` | string | Sí | Nombre descriptivo del token |
| `scopes` | text | Sí | JSON array de scopes otorgados |
| `revoked` | boolean | No | `true` = token inválido |
| `expires_at` | datetime | Sí | Expiración (15 días desde creación) |
| `created_at` / `updated_at` | timestamp | — | — |

---

### `oauth_refresh_tokens`

| Columna | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | char(80) | No | Refresh token identifier |
| `access_token_id` | char(80) | No | FK al access token asociado |
| `revoked` | boolean | No | `true` = token inválido |
| `expires_at` | datetime | Sí | Expiración (30 días desde creación) |

---

### `oauth_auth_codes`

| Columna | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | char(80) | No | Código de autorización (uso único) |
| `user_id` | UUID | No | Usuario que autorizó |
| `client_id` | UUID | No | Cliente que solicitó |
| `scopes` | text | Sí | JSON array de scopes solicitados |
| `revoked` | boolean | No | `true` = código ya usado o revocado |
| `expires_at` | datetime | Sí | Expiración (segundos) |

---

### `sessions`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | string | Session ID (clave primaria) |
| `user_id` | UUID / null | Usuario autenticado; `null` = sesión de invitado |
| `ip_address` | string | IP de la última petición |
| `user_agent` | text | User-agent del navegador |
| `payload` | longtext | Datos de sesión serializados |
| `last_activity` | integer | Timestamp Unix de la última actividad |

---

### `jobs` / `failed_jobs`

Tablas estándar de Laravel para la cola de trabajos (emails, tareas async).

| Tabla | Uso |
|---|---|
| `jobs` | Trabajos pendientes de procesar |
| `job_batches` | Batches de trabajos agrupados |
| `failed_jobs` | Trabajos que fallaron (para reintento o inspección) |

---

## Relaciones principales

```
users (1) ──────────── (N) oauth_clients           (un usuario puede tener varios clientes)
users (1) ──────────── (N) oauth_access_tokens      (un usuario puede tener varios tokens)
oauth_access_tokens (1) ── (1) oauth_refresh_tokens (cada access token tiene un refresh token)
oauth_clients (1) ────── (N) oauth_access_tokens    (un cliente puede emitir varios tokens)
oauth_auth_codes (N) ─── (1) oauth_clients          (varios códigos por cliente)
```
