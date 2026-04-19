<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;

class SocialiteController extends Controller
{
    #[OA\Get(
        path: '/auth/google',
        summary: 'Redirect to Google OAuth consent screen',
        tags: ['Google OAuth'],
        responses: [
            new OA\Response(response: 302, description: 'Redirects to accounts.google.com for authentication'),
        ]
    )]
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    #[OA\Get(
        path: '/auth/google/callback',
        summary: 'Handle Google OAuth callback – login or register user',
        tags: ['Google OAuth'],
        parameters: [
            new OA\Parameter(name: 'code', in: 'query', required: true, schema: new OA\Schema(type: 'string'), description: 'Authorization code returned by Google'),
            new OA\Parameter(name: 'state', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Authenticated – redirects to intended URL or dashboard'),
            new OA\Response(response: 500, description: 'Google OAuth error'),
        ]
    )]
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())
            ->orWhere('google_id', $googleUser->getId())
            ->first();

        if ($user) {
            $user->update([
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
