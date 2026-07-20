<?php

namespace App\Http\Middleware;

use App\Services\OfficeNetworkChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficeNetwork
{
    public function handle(Request $request, Closure $next, ?OfficeNetworkChecker $checker = null): Response
    {
        $checker ??= app(OfficeNetworkChecker::class);

        if (! $checker->passes($request->ip())) {
            abort(403, 'You must be connected to office WiFi to check in or out.');
        }

        return $next($request);
    }
}
