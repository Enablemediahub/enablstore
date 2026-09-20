<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('pos_cashier_id')) {
            return redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
        }

        return $next($request);
    }
}