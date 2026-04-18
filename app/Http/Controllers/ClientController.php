<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    public function __construct(protected ClientRepository $clients) {}

    public function index(Request $request)
    {
        $clients = $request->user()->oauthApps()->where('revoked', false)->orderBy('name')->get()
            ->map(fn ($client) => [
                'id'            => $client->id,
                'name'          => $client->name,
                'logo'          => $client->logo,
                'icon'          => $client->icon,
                'secret'        => $client->secret,
                'redirect_uris' => $client->redirect_uris ?? [],
                'grant_types'   => $client->grant_types ?? [],
                'revoked'       => $client->revoked,
                'created_at'    => $client->created_at->toDateTimeString(),
            ]);

        $baseUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Clients/Index', [
            'clients'     => $clients,
            'flashSecret' => session('new_client_secret'),
            'flashClient' => session('new_client_name'),
            'oauthEndpoints' => [
                'authorize' => $baseUrl . '/oauth/authorize',
                'token'     => $baseUrl . '/oauth/token',
                'revoke'    => $baseUrl . '/oauth/tokens/{token_id}',
                'userinfo'  => $baseUrl . '/api/user',
            ],
        ]);
    }

    public function show(Request $request, string $id)
    {
        $client = $request->user()->oauthApps()->where('revoked', false)->find($id);

        abort_unless($client, 404);

        $authorizedUsers = User::whereHas('tokens', function ($q) use ($id) {
            $q->where('client_id', $id)->where('revoked', false);
        })
        ->with(['tokens' => function ($q) use ($id) {
            $q->where('client_id', $id)->where('revoked', false)->latest('created_at');
        }])
        ->orderBy('name')
        ->get()
        ->map(fn ($user) => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'avatar'     => $user->avatar,
            'scopes'     => $user->tokens->first()?->scopes ?? [],
            'authorized_at' => $user->tokens->first()?->created_at->toDateTimeString(),
            'expires_at' => $user->tokens->first()?->expires_at?->toDateTimeString(),
        ]);

        return Inertia::render('Clients/Show', [
            'client' => [
                'id'            => $client->id,
                'name'          => $client->name,
                'logo'          => $client->logo,
                'icon'          => $client->icon,
                'grant_types'   => $client->grant_types ?? [],
                'redirect_uris' => $client->redirect_uris ?? [],
                'created_at'    => $client->created_at->toDateTimeString(),
            ],
            'authorizedUsers' => $authorizedUsers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'grant_type'    => 'required|in:authorization_code,client_credentials',
            'redirect_uris' => 'required_if:grant_type,authorization_code|nullable|string',
            'confidential'  => 'boolean',
            'logo'          => 'nullable|url|max:2048',
            'icon'          => 'nullable|url|max:2048',
        ]);

        $redirectUris = array_filter(
            array_map('trim', explode("\n", $data['redirect_uris'] ?? ''))
        );

        $user = $request->user();

        if ($data['grant_type'] === 'authorization_code') {
            $client = $this->clients->createAuthorizationCodeGrantClient(
                name: $data['name'],
                redirectUris: array_values($redirectUris),
                confidential: $data['confidential'] ?? true,
                user: $user,
            );
        } else {
            $client = $this->clients->createClientCredentialsGrantClient(
                name: $data['name'],
            );

            // Assign owner manually for client_credentials
            $client->owner()->associate($user);
            $client->save();
        }

        $client->logo = $data['logo'] ?? null;
        $client->icon = $data['icon'] ?? null;
        $client->save();

        return redirect()->route('clients.index')
            ->with('new_client_secret', $client->plainSecret)
            ->with('new_client_name', $client->name);
    }

    public function update(Request $request, string $id)
    {
        $client = $request->user()->oauthApps()->where('revoked', false)->find($id);

        abort_unless($client, 404);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'redirect_uris' => 'nullable|string',
            'logo'          => 'nullable|url|max:2048',
            'icon'          => 'nullable|url|max:2048',
        ]);

        $redirectUris = array_filter(
            array_map('trim', explode("\n", $data['redirect_uris'] ?? ''))
        );

        $this->clients->update($client, $data['name'], array_values($redirectUris));

        $client->logo = $data['logo'] ?? null;
        $client->icon = $data['icon'] ?? null;
        $client->save();

        return redirect()->route('clients.index');
    }

    public function regenerateSecret(Request $request, string $id)
    {
        $client = $request->user()->oauthApps()->where('revoked', false)->find($id);

        abort_unless($client, 404);

        $this->clients->regenerateSecret($client);

        $plainSecret = $client->plainSecret;

        return redirect()->route('clients.index')
            ->with('new_client_secret', $plainSecret)
            ->with('new_client_name', $client->name);
    }

    public function destroy(Request $request, string $id)
    {
        $client = $request->user()->oauthApps()->where('revoked', false)->find($id);

        abort_unless($client, 404);

        $this->clients->delete($client);

        return redirect()->route('clients.index');
    }
}
