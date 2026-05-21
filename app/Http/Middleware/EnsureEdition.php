<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEdition
{
    public function handle(Request $request, Closure $next, string ...$editions): Response
    {
        $currentEdition = config('app.edition', 'full');

        if (! in_array($currentEdition, $editions, true)) {
            abort(404);
        }

        return $next($request);
    }
}
