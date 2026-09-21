<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PosUnlockRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class TenantPosAccessController extends Controller
{
    public function unlock(PosUnlockRequest $request): RedirectResponse
    {
        $username = strtolower($request->string('username')->toString());
        $subscriberCode = strtolower((string) tenant()->subscriber_code);

        if ($subscriberCode === '' || ! str_starts_with($username, $subscriberCode.'-')) {
            return back()->withErrors(['username' => 'Use the full username including this subscriber code prefix.']);
        }

        $user = User::query()
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('role', 'cashier')
            ->whereRaw('LOWER(username) = ?', [$username])
            ->first();

        if ($user === null || $user->pos_pin_hash === null || ! Hash::check($request->string('pin')->toString(), $user->pos_pin_hash)) {
            return back()->withErrors(['pin' => 'The username or PIN is incorrect.']);
        }

        $request->session()->put('pos_cashier_id', $user->id);

        $destination = $request->session()->pull('workspace_destination', 'pos');

        return $destination === 'store'
            ? redirect()->route('tenant.home', ['tenant' => tenant()->getTenantKey()])
            : redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['pos_cashier_id', 'workspace_destination']);

        return redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
    }
}
