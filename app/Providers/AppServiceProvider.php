<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Passport::authorizationView(fn ($params) => Inertia::render('OAuth/Authorize', $params));

        // OAuth2 scopes disponibles para los clientes del ecosistema Droni
        Passport::tokensCan([
            'profile' => 'Ver información del perfil del usuario',
            'email'   => 'Ver dirección de email del usuario',
            'roles'   => 'Ver roles y permisos asignados',
            'admin'   => 'Acceso administrativo al sistema',
        ]);

        Passport::setDefaultScope(['profile', 'email']);

        // Tokens de acceso válidos por 15 días, refresh por 30 días
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
