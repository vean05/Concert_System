<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Applies while the site is opened to the internet through the tunnel. The
 * seeded admin password is in the public repository, so the admin area is
 * closed altogether rather than protected by that login.
 */
class PublicDemo
{
    public const TEST_CARD = '4111111111111111';

    public function handle(Request $request, Closure $next)
    {
        abort_if(config('app.public_demo') && $request->is('admin', 'admin/*'), 404);

        return $next($request);
    }
}
