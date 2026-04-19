<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Passport\ClientRepository;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OAuthClient',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'My App'),
        new OA\Property(property: 'logo', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'icon', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'secret', type: 'string', nullable: true, description: 'Only present for confidential clients'),
        new OA\Property(property: 'redirect_uris', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
        new OA\Property(property: 'grant_types', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'revoked', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class ClientController extends Controller
{
    public function __construct(protected ClientRepository $clients) {}

    #[OA\Get(
        path: '/clients',
        summary: 'List OAuth clients owned by the authenticated user',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client list with OAuth endpoint references',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'clients', type: 'array', items: new OA\Items(ref: '#/components/schemas/OAuthClient')),
                        new OA\Property(property: 'oauthEndpoints', type: 'object',
                            properties: [
                                new OA\Property(property: 'authorize', type: 'string', example: 'https://auth.droni.co/oauth/authorize'),
                                new OA\Property(property: 'token', type: 'string', example: 'https://auth.droni.co/oauth/token'),
                                new OA\Property(property: 'revoke', type: 'string', example: 'https://auth.droni.co/oauth/tokens/{token_id}'),
                                new OA\Property(property: 'userinfo', type: 'string', example: 'https://auth.droni.co/api/user'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Get(
        path: '/clients/{client}',
        summary: 'Show an OAuth client and its authorized users',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client details with list of authorized users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'client', ref: '#/components/schemas/OAuthClient'),
                        new OA\Property(property: 'authorizedUsers', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string', format: 'email'),
                                new OA\Property(property: 'avatar', type: 'string', format: 'uri', nullable: true),
                                new OA\Property(property: 'scopes', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'authorized_at', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
                            ]
                        )),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Client not found or not owned by user'),
        ]
    )]
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

    #[OA\Post(
        path: '/clients',
        summary: 'Create a new OAuth client',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'grant_type'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'My App'),
                    new OA\Property(property: 'grant_type', type: 'string', enum: ['authorization_code', 'client_credentials']),
                    new OA\Property(property: 'redirect_uris', type: 'string', description: 'Newline-separated redirect URIs', example: "https://myapp.com/callback"),
                    new OA\Property(property: 'confidential', type: 'boolean', example: true),
                    new OA\Property(property: 'logo', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'icon', type: 'string', format: 'uri', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Client created – redirects to /clients with the plain secret in session flash'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Put(
        path: '/clients/{client}',
        summary: 'Update an OAuth client',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'My App Updated'),
                    new OA\Property(property: 'redirect_uris', type: 'string', description: 'Newline-separated redirect URIs', nullable: true),
                    new OA\Property(property: 'logo', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'icon', type: 'string', format: 'uri', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Client updated – redirects to /clients'),
            new OA\Response(response: 404, description: 'Client not found or not owned by user'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Post(
        path: '/clients/{client}/secret',
        summary: 'Regenerate the client secret',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Secret regenerated – redirects to /clients with the new plain secret in session flash'),
            new OA\Response(response: 404, description: 'Client not found or not owned by user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Delete(
        path: '/clients/{client}',
        summary: 'Delete (revoke) an OAuth client',
        security: [['sessionAuth' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Client deleted – redirects to /clients'),
            new OA\Response(response: 404, description: 'Client not found or not owned by user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $client = $request->user()->oauthApps()->where('revoked', false)->find($id);

        abort_unless($client, 404);

        $this->clients->delete($client);

        return redirect()->route('clients.index');
    }
}
