<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFoodStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user()?->role === 'admin'
            && $request->user()->tenant_id === tenant()->getTenantKey();
        $cashier = User::query()
            ->whereKey($request->session()->get('pos_cashier_id'))
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('role', 'cashier')
            ->exists();

        if (! $admin && ! $cashier) {
            return redirect()->route('tenant.foodstore.index', ['tenant' => tenant()->getTenantKey()]);
        }

        return $next($request);
    }
}