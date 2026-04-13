<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Deploy to Azure App Service (Docker)

This repository includes a GitHub Actions workflow at `.github/workflows/azure-appservice-docker.yml` that builds a Docker image, pushes it to Azure Container Registry (ACR), and deploys it to Azure App Service.

### 1. GitHub repository variables

Create these Variables in your GitHub repository (`Settings > Secrets and variables > Actions > Variables`):

- `AZURE_WEBAPP_NAME`: Azure App Service name.
- `ACR_LOGIN_SERVER`: ACR login server, for example `myregistry.azurecr.io`.
- `ACR_NAME` (optional): ACR resource name, for example `myregistry`.

### 2. GitHub repository secrets

Create these Secrets in your GitHub repository (`Settings > Secrets and variables > Actions > Secrets`):

- `AZURE_CREDENTIALS`: Service principal JSON generated with `az ad sp create-for-rbac`.

With RBAC authentication, `ACR_USERNAME` and `ACR_PASSWORD` are not required.

### 3. Required RBAC roles

The service principal used in `AZURE_CREDENTIALS` must have:

- `Website Contributor` (or equivalent) on the target App Service scope.
- `AcrPush` on the target ACR scope.

Example role assignment for ACR:

```bash
az role assignment create \
	--assignee <CLIENT_ID_FROM_AZURE_CREDENTIALS> \
	--role AcrPush \
	--scope /subscriptions/<SUBSCRIPTION_ID>/resourceGroups/<RG>/providers/Microsoft.ContainerRegistry/registries/<ACR_NAME>
```

### 4. Azure App Service settings

In your App Service, configure:

- `WEBSITES_PORT=80`
- Laravel runtime settings (`APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `DB_*`, etc.) as Application Settings.

### 5. Trigger deployment

Push to `main` (or run the workflow manually from the Actions tab). The workflow will:

1. Build the Docker image from `Dockerfile`.
2. Push image tags `latest` and commit `sha` to ACR.
3. Update your Web App to use the `latest` image.
