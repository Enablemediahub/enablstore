<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantTeamController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Tenant/Team/Index', [
            'users' => User::query()->where('tenant_id', $request->user()->tenant_id)->latest()->get(['id', 'name', 'username', 'email', 'role']),
            'subscriberCode' => $request->user()->tenant?->subscriber_code,
            'teamManagementEnabled' => tenant()->teamManagementEnabled(),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTeamManagementEnabled();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'alpha_dash', 'max:52'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['nullable', 'string', 'min:8', 'required_if:role,admin'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'pos_pin' => ['nullable', 'digits_between:4,6', 'required_if:role,cashier'],
        ]);

        $subscriberCode = $request->user()->tenant?->subscriber_code;
        abort_unless(is_string($subscriberCode) && $subscriberCode !== '', 422, 'This subscriber does not have an access code yet.');
        $username = $subscriberCode.'-'.strtolower($data['username']);

        if (User::query()->where('username', $username)->exists()) {
            return back()->withErrors(['username' => 'This username is already in use for this subscriber.'])->withInput();
        }

        User::query()->create([
            ...$data,
            'username' => $username,
            'tenant_id' => $request->user()->tenant_id,
            'email' => $data['email'] ?: null,
            'password' => Hash::make($data['role'] === 'admin' ? $data['password'] : str()->random(48)),
            'pos_pin_hash' => isset($data['pos_pin']) ? Hash::make($data['pos_pin']) : null,
        ]);

        return back()->with('status', 'Team member created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureTeamManagementEnabled();
        abort_unless($user->tenant_id === $request->user()->tenant_id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique(User::class, 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
        ]);
        if ($user->role === 'admin' && $data['role'] !== 'admin' && User::query()->where('tenant_id', $user->tenant_id)->where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'A workspace must keep at least one administrator.']);
        }

        $subscriberCode = strtolower((string) tenant()->subscriber_code);
        if ($subscriberCode !== '' && ! str_starts_with(strtolower($data['username']), $subscriberCode.'-')) {
            return back()->withErrors(['username' => "This username must begin with ".tenant()->subscriber_code.'-.']);
        }

        $username = $subscriberCode !== ''
            ? tenant()->subscriber_code.'-'.strtolower(substr($data['username'], strlen($subscriberCode) + 1))
            : strtolower($data['username']);

        $user->update([
            'name' => $data['name'],
            'username' => $username,
            'email' => $data['email'] ?: null,
            'role' => $data['role'],
        ]);

        return back()->with('status', 'Team member updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureTeamManagementEnabled();
        abort_unless($user->tenant_id === $request->user()->tenant_id, 404);
        if ($user->role === 'admin' && User::query()->where('tenant_id', $user->tenant_id)->where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['team' => 'A workspace must keep at least one administrator.']);
        }

        $user->delete();

        return back()->with('status', 'Team member deleted.');
    }

    public function resetAccess(Request $request, User $user): RedirectResponse
    {
        $this->ensureTeamManagementEnabled();
        abort_unless($user->tenant_id === $request->user()->tenant_id, 404);

        if ($user->role === 'cashier') {
            $data = $request->validate(['pin' => ['required', 'digits_between:4,6']]);
            $user->update(['pos_pin_hash' => Hash::make($data['pin'])]);

            return back()->with('status', "POS PIN reset for {$user->name}.");
        }

        $data = $request->validate(['password' => ['required', 'string', 'min:8']]);
        $user->update(['password' => $data['password']]);

        return back()->with('status', "Login password reset for {$user->name}.");
    }

    private function ensureTeamManagementEnabled(): void
    {
        abort_unless(tenant()->teamManagementEnabled(), 403, 'Team management has been disabled for this workspace.');
    }
}
