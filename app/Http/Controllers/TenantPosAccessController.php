<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PosUnlockRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class TenantPosAccessController extends Controller
{
    public function unlock(PosUnlockRequest $request): RedirectResponse
    {
        $user = User::query()
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('role', 'cashier')
            ->whereRaw('LOWER(name) = ?', [strtolower($request->string('name')->toString())])
            ->first();

        if ($user === null || $user->pos_pin_hash === null || ! Hash::check($request->string('pin')->toString(), $user->pos_pin_hash)) {
            return back()->withErrors(['pin' => 'The cashier name or PIN is incorrect.']);
        }

        $request->session()->put('pos_cashier_id', $user->id);

        return redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
    }
}