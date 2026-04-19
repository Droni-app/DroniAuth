<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use OpenApi\Attributes as OA;

class AuthenticatedSessionController extends Controller
{
    #[OA\Get(
        path: '/login',
        summary: 'Show login page',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Login page (HTML/Inertia)'),
        ]
    )]
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Authenticate user and create session',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret'),
                    new OA\Property(property: 'remember', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirects to dashboard or two-factor challenge if 2FA is enabled'),
            new OA\Response(response: 422, description: 'Invalid credentials or validation error'),
        ]
    )]
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Si el usuario tiene 2FA activo y confirmado, iniciamos el desafío
        if ($user->hasEnabledTwoFactorAuthentication()) {
            Auth::logout();

            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Invalidate session and log out',
        security: [['sessionAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 302, description: 'Redirects to /'),
        ]
    )]
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
