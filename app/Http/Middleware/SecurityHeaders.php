<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Evitar que el navegador detecte el MIME type automáticamente
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Impedir que la app se cargue dentro de un iframe (clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Controlar qué información de referrer se envía
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Forzar HTTPS durante 1 año e incluir subdominios (solo en producción)
        if (app()->isProduction()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // En desarrollo Vite levanta un servidor HMR en un puerto separado
        $viteServer = app()->isProduction() ? '' : ' http://127.0.0.1:5173 ws://127.0.0.1:5173';

        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'{$viteServer}",
            "script-src-elem 'self' 'unsafe-inline'{$viteServer}",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net{$viteServer}",
            "style-src-elem 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net{$viteServer}",
            "font-src 'self' data: https://fonts.bunny.net https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "connect-src 'self' https://cdn.jsdelivr.net{$viteServer}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // Ocultar que el servidor usa PHP/Laravel
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
