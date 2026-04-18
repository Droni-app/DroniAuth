<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleExternalInertiaRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->header('X-Inertia') || ! $response->isRedirect()) {
            return $response;
        }

        $location = $response->headers->get('Location', '');

        // Only intercept redirects to external origins (OAuth callbacks, etc.)
        if (str_starts_with($location, 'http') && ! str_starts_with($location, config('app.url'))) {
            return Inertia::location($location);
        }

        return $response;
    }
}
