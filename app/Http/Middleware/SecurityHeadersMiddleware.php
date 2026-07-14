<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=()");

        $imgSrc = "'self' data: https://images.unsplash.com https://unpkg.com https://a.basemaps.cartocdn.com https://b.basemaps.cartocdn.com https://c.basemaps.cartocdn.com https://d.basemaps.cartocdn.com https://server.arcgisonline.com https://gibs.earthdata.nasa.gov https://inarisk1.bnpb.go.id:8443 https://*.tile.openstreetmap.org";

        if (!app()->environment('production')) {
            $imgSrc .= " http://localhost:* http://127.0.0.1:*";
        }

        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://code.jquery.com; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; "
             . "img-src {$imgSrc}; "
             . "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net data:; "
             . "form-action 'self'; frame-ancestors 'none'; base-uri 'self'";
        if (!app()->environment('production')) {
            $csp .= "; connect-src 'self' ws://localhost:* http://localhost:* ws://127.0.0.1:* http://127.0.0.1:*";
        }
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
