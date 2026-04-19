<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'DroniAuth API',
    description: 'OAuth 2.0 Authorization Server. Provides authentication, user management, and OAuth client management endpoints.',
    contact: new OA\Contact(email: 'dev@droni.co'),
)]
#[OA\Server(
    url: 'https://auth.droni.co',
    description: 'Production',
)]
#[OA\Server(
    url: 'http://localhost',
    description: 'Local development',
)]
#[OA\SecurityScheme(
    securityScheme: 'sessionAuth',
    type: 'apiKey',
    description: 'Session cookie (web authentication)',
    name: 'droniauth_session',
    in: 'cookie',
)]
#[OA\SecurityScheme(
    securityScheme: 'oauth2',
    type: 'oauth2',
    description: 'OAuth 2.0 Authorization Code flow issued by this server',
    flows: [
        new OA\Flow(
            authorizationUrl: '/oauth/authorize',
            tokenUrl: '/oauth/token',
            refreshUrl: '/oauth/token/refresh',
            flow: 'authorizationCode',
            scopes: [
                'profile' => 'Read user profile (name, avatar)',
                'email'   => 'Read email address',
                'roles'   => 'Read roles and permissions',
                'admin'   => 'Full admin access',
            ],
        ),
    ],
)]
#[OA\Tag(name: 'Authentication', description: 'Login, register, logout and password management')]
#[OA\Tag(name: 'OAuth', description: 'OAuth 2.0 authorization and token endpoints')]
#[OA\Tag(name: 'Clients', description: 'OAuth client application management')]
#[OA\Tag(name: 'Profile', description: 'Authenticated user profile')]
#[OA\Tag(name: 'Google OAuth', description: 'Social login via Google')]
class OpenApiSpec {}
