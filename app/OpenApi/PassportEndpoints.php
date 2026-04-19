<?php

/**
 * OpenAPI annotations for Laravel Passport endpoints.
 * Passport controllers live in vendor/ so we document them here.
 */

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/oauth/authorize',
    summary: 'OAuth 2.0 Authorization endpoint – shows consent screen',
    description: 'Redirects unauthenticated users to login. For already-authorized users, auto-approves and redirects to the redirect_uri with the authorization code.',
    tags: ['OAuth'],
    parameters: [
        new OA\Parameter(name: 'response_type', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['code']), example: 'code'),
        new OA\Parameter(name: 'client_id', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        new OA\Parameter(name: 'redirect_uri', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'uri'), example: 'https://myapp.com/callback'),
        new OA\Parameter(name: 'scope', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'profile email'),
        new OA\Parameter(name: 'state', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'CSRF protection value – must match the value in the callback'),
        new OA\Parameter(name: 'code_challenge', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'PKCE code challenge (S256)'),
        new OA\Parameter(name: 'code_challenge_method', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['S256'])),
        new OA\Parameter(name: 'prompt', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['consent']), description: 'Pass "consent" to always show the consent screen even if previously authorized'),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Consent screen (HTML/Inertia) shown to the user'),
        new OA\Response(response: 302, description: 'Already authorized – redirects to redirect_uri?code=...&state=...'),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated – redirects to /login',
        ),
    ]
)]
#[OA\Post(
    path: '/oauth/authorize',
    summary: 'Approve the OAuth authorization request',
    description: 'User submits the consent form to grant access. Issues an authorization code and redirects to the client\'s redirect_uri.',
    security: [['sessionAuth' => []]],
    tags: ['OAuth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['auth_token'],
            properties: [
                new OA\Property(property: 'auth_token', type: 'string', description: 'Opaque token embedded in the consent form'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 302, description: 'Redirects to redirect_uri?code=AUTH_CODE&state=STATE'),
        new OA\Response(response: 401, description: 'Unauthenticated'),
    ]
)]
#[OA\Delete(
    path: '/oauth/authorize',
    summary: 'Deny the OAuth authorization request',
    security: [['sessionAuth' => []]],
    tags: ['OAuth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['auth_token'],
            properties: [
                new OA\Property(property: 'auth_token', type: 'string', description: 'Opaque token embedded in the consent form'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 302, description: 'Redirects to redirect_uri?error=access_denied'),
        new OA\Response(response: 401, description: 'Unauthenticated'),
    ]
)]
#[OA\Post(
    path: '/oauth/token',
    summary: 'Exchange authorization code for access token',
    description: 'Token endpoint supporting authorization_code and client_credentials grants.',
    tags: ['OAuth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['grant_type', 'client_id', 'client_secret'],
                properties: [
                    new OA\Property(property: 'grant_type', type: 'string', enum: ['authorization_code', 'client_credentials', 'refresh_token'], example: 'authorization_code'),
                    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'client_secret', type: 'string', description: 'Required for confidential clients'),
                    new OA\Property(property: 'code', type: 'string', description: 'Required for authorization_code grant'),
                    new OA\Property(property: 'redirect_uri', type: 'string', format: 'uri', description: 'Must match the one used in /oauth/authorize'),
                    new OA\Property(property: 'code_verifier', type: 'string', description: 'PKCE verifier – required if code_challenge was used'),
                    new OA\Property(property: 'refresh_token', type: 'string', description: 'Required for refresh_token grant'),
                    new OA\Property(property: 'scope', type: 'string', description: 'Optional subset of original scopes for client_credentials'),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Token issued successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                    new OA\Property(property: 'expires_in', type: 'integer', example: 1296000, description: 'Seconds until access token expiry (15 days)'),
                    new OA\Property(property: 'access_token', type: 'string'),
                    new OA\Property(property: 'refresh_token', type: 'string', description: 'Only for authorization_code grant (valid 30 days)'),
                ]
            )
        ),
        new OA\Response(response: 400, description: 'Invalid request (missing parameters, wrong grant type)'),
        new OA\Response(response: 401, description: 'Invalid client credentials'),
    ]
)]
#[OA\Post(
    path: '/oauth/token/refresh',
    summary: 'Refresh an access token (transient – same client session)',
    security: [['sessionAuth' => []]],
    tags: ['OAuth'],
    responses: [
        new OA\Response(response: 200, description: 'New access token issued'),
        new OA\Response(response: 401, description: 'Unauthenticated'),
    ]
)]
class PassportEndpoints {}
