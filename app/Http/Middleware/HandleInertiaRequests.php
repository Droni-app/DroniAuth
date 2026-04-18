<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Passport\Client;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    private function resolveOAuthClient(Request $request): ?array
    {
        // On /oauth/authorize the client_id is in the current query string
        $clientId = $request->query('client_id');

        // On login/register the user came from /oauth/authorize, stored as intended URL
        if (!$clientId) {
            $intended = $request->session()->get('url.intended', '');
            parse_str(parse_url($intended, PHP_URL_QUERY) ?? '', $params);
            $clientId = $params['client_id'] ?? null;
        }

        if (!$clientId) return null;

        $client = Client::where('id', $clientId)->where('revoked', false)->first();

        if (!$client) return null;

        return [
            'name' => $client->name,
            'logo' => $client->logo,
            'icon' => $client->icon,
        ];
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->toArray(),
                    ['two_factor_enabled' => $request->user()->hasEnabledTwoFactorAuthentication()]
                ) : null,
            ],
            'csrf_token' => csrf_token(),
            'oauth_client' => fn () => $this->resolveOAuthClient($request),
        ];
    }
}
