<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Without this, mobile Safari/Chrome restore authenticated pages from
 * back-forward cache after logout - the URL bar shows /login but the
 * previous dashboard/profile HTML is still on screen, since no real
 * request was made. Forcing no-store means the browser must always
 * re-fetch (and re-check auth) instead of showing a stale snapshot.
 */
class PreventBackHistoryCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
