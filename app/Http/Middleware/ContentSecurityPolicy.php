<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * Adds Content-Security-Policy headers to prevent XSS attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add CSP headers for HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'text/html') || empty($contentType)) {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self' 'unsafe-inline'", // Allow inline styles for basic styling
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "object-src 'none'",
                "base-uri 'self'",
                "upgrade-insecure-requests",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
