<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== $role) {
            abort(403);
        }

        if ($role === 'employee' && $request->user()->employee?->status !== 'active') {
            abort(403, 'គណនីរបស់អ្នកត្រូវបានផ្អាក។ សូមទាក់ទងអ្នកគ្រប់គ្រង។');
        }
        if ($role === 'employee') app()->setLocale('km');

        return $next($request);
    }
}
