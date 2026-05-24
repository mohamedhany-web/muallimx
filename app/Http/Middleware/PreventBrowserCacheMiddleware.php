<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * منع تخزين صفحات HTML الديناميكية في المتصفح بعد كل نشر.
 */
class PreventBrowserCacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldPreventCache($request, $response)) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-cache, private, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }

    private function shouldPreventCache(Request $request, Response $response): bool
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return false;
        }

        if ($response->isRedirection()) {
            return true;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        return str_contains($contentType, 'text/html');
    }
}
