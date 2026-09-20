<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = tenant();

        if ($user === null || $tenant === null || $user->tenant_id !== $tenant->getTenantKey()) {
            abort(403, 'You do not have access to this workspace.');
        }

        return $next($request);
    }
}
