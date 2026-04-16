# DroniAuth — Lista de mejoras pendientes

## 🔴 Prioridad alta

### Seguridad
- [x] **Mover envío de emails a la cola** — actualmente el envío es síncrono; si Mailjet tarda, el usuario espera. Cambiar `QUEUE_CONNECTION=database` ya está listo, solo falta que las notificaciones implementen `ShouldQueue`.
- [x] **Revocar todos los tokens al cambiar contraseña** — cuando el usuario cambia su contraseña, los tokens OAuth activos deberían invalidarse para forzar re-autenticación.
- [x] **Configurar headers de seguridad HTTP** — agregar middleware que emita `Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y una política `Content-Security-Policy` básica.
- [x] **Eliminar `laravel/sanctum` si no se usa** — está en `composer.json` pero no tiene función activa; reduce la superficie de ataque y el tamaño del vendor.
- [ ] **Proteger las claves de Passport en producción** — asegurarse de que `PASSPORT_PRIVATE_KEY` y `PASSPORT_PUBLIC_KEY` estén definidas como variables de entorno en producción y que los archivos `storage/oauth-*.key` no existan ni estén en el repositorio.

### OAuth / Passport
- [ ] **Añadir soporte a PKCE** — el grant `authorization_code` debería requerir PKCE (`code_challenge` + `code_verifier`) para clientes públicos (SPAs, apps móviles) y así eliminar la necesidad de un `client_secret`.
- [ ] **Scopes granulares** — definir scopes específicos (`profile:read`, `email:read`, `openid`, etc.) en lugar de usar el scope comodín, y mostrarlos en la pantalla de autorización con descripciones legibles.
- [ ] **Pantalla de autorización mejorada** — la página `OAuth/Authorize.vue` debería mostrar el logo y nombre real del cliente, los scopes solicitados con descripciones, y un aviso si el cliente es de terceros.

---

## 🟡 Prioridad media

### Autenticación y sesiones
- [ ] **Registro de auditoría de seguridad** — loguear eventos importantes: login exitoso/fallido, cambio de contraseña, activación/desactivación de 2FA, creación/revocación de tokens, login con Google. Guardarlos en una tabla `security_logs` con IP, user-agent y timestamp.
- [ ] **Notificación de nuevo acceso** — enviar un email al usuario cuando inicie sesión desde un dispositivo o IP no reconocida.
- [ ] **Sesiones activas en el perfil** — mostrar al usuario sus sesiones activas (tabla `sessions`) con la posibilidad de cerrarlas individualmente.
- [ ] **Bloqueo temporal de cuenta** — tras N intentos fallidos consecutivos (ej. 10), bloquear la cuenta por un tiempo definido además del rate limiting actual.
- [ ] **Verificación de contraseñas comprometidas** — integrar la API de [HaveIBeenPwned Passwords](https://haveibeenpwned.com/API/v3#PwnedPasswords) al registrar o cambiar contraseña para rechazar passwords filtradas.

### Correos electrónicos
- [ ] **Templates HTML personalizados** — publicar y personalizar las vistas de email de Laravel (`php artisan vendor:publish --tag=laravel-notifications`) para que todos los correos (verificación, reset de contraseña, etc.) tengan el branding de DroniAuth.
- [ ] **Email de bienvenida** — enviar un correo de bienvenida cuando el usuario verifica su email por primera vez.
- [ ] **Notificación al activar/desactivar 2FA** — informar al usuario por correo cuando se habilita o deshabilita la autenticación de dos factores en su cuenta.
- [ ] **Notificación al revocar un cliente OAuth** — avisar al usuario cuando una aplicación pierde acceso a su cuenta.

### Gestión de clientes OAuth
- [ ] **Soft deletes en clientes** — en lugar de eliminar permanentemente, marcar como revocados para mantener historial de accesos.
- [ ] **Logs de uso de tokens por cliente** — mostrar al usuario cuándo y desde dónde accedió cada aplicación a su cuenta.
- [ ] **Tokens autorizados en el perfil** — sección donde el usuario vea qué aplicaciones tienen tokens activos y pueda revocarlos individualmente (similar a "Apps con acceso" de Google).
- [ ] **Paginación en la tabla de clientes** — cuando hay muchos clientes, la tabla se vuelve larga. Agregar paginación o scroll infinito.

---

## 🟢 Prioridad baja / Mejoras de calidad

### Proveedores OAuth adicionales
- [ ] **Microsoft / Azure AD** — muchos entornos empresariales usan cuentas de Microsoft.
- [ ] **GitHub** — útil para proyectos técnicos o herramientas para desarrolladores.
- [ ] **Apple Sign In** — requerido si existe o se planea una app iOS.

### Dashboard
- [ ] **Dashboard informativo** — el dashboard actual está vacío. Agregar: número de aplicaciones registradas, último acceso, estado de 2FA, tokens activos, accesos recientes.

### Tests
- [ ] **Tests para el flujo 2FA** — cubrir: habilitar, confirmar y deshabilitar TOTP; desafío 2FA en login; códigos de recuperación.
- [ ] **Tests para el flujo OAuth completo** — authorization code grant, client credentials, revocación de tokens.
- [ ] **Tests para verificación de email** — registrar usuario, recibir correo, verificar, acceder.
- [ ] **Tests para Socialite (Google OAuth)** — mockear el proveedor y cubrir el callback.

### Infraestructura y DevOps
- [ ] **CI/CD con GitHub Actions** — pipeline que ejecute `php artisan test`, `npm run build` y análisis estático en cada PR.
- [ ] **Dockerfile + docker-compose** — facilitar el onboarding local sin depender de configuración manual de PHP/MySQL.
- [ ] **Monitoreo de errores** — integrar Sentry o Flare para capturar excepciones en producción con contexto (usuario, request, stack trace).
- [ ] **Health check mejorado** — el endpoint `/up` solo verifica que el proceso PHP responde; agregar verificación de conexión a BD, cola y caché.
- [ ] **Logs estructurados en producción** — cambiar el canal de log a JSON (`LOG_CHANNEL=json`) para facilitar la ingesta en herramientas como Datadog o CloudWatch.

### Código y arquitectura
- [ ] **Form Requests para los controladores de Clientes** — `ClientController` valida inline; mover a `StoreClientRequest` y `UpdateClientRequest` para mantener los controladores delgados.
- [ ] **Política (Policy) para clientes OAuth** — reemplazar la verificación manual de propiedad con `ClientPolicy` y `Gate::authorize()`.
- [ ] **Internacionalización (i18n)** — los mensajes de error de Laravel están en inglés. Instalar el paquete de traducciones al español y configurar `APP_LOCALE=es`.
- [ ] **OpenAPI / Swagger** — documentar los endpoints OAuth (`/oauth/authorize`, `/oauth/token`, `/oauth/userinfo`) con una especificación OpenAPI para que terceros puedan integrarse más fácilmente.
