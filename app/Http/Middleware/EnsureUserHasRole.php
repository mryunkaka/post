<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  list<string>|string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($roles === [] || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki role yang diperlukan untuk mengakses area ini.');
    }
}
