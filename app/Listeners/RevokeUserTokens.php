<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class RevokeUserTokens
{
    public function handle(PasswordReset $event): void
    {
        $user = $event->user;

        // Revocar todos los access tokens activos
        $tokenIds = $user->tokens()->where('revoked', false)->pluck('id');

        if ($tokenIds->isEmpty()) {
            return;
        }

        $user->tokens()->update(['revoked' => true]);

        // Revocar también los refresh tokens asociados
        \DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', $tokenIds)
            ->update(['revoked' => true]);
    }
}
