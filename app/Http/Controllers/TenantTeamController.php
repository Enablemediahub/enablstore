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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        return back()->with('success', 'Team member created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->tenant_id === $request->user()->tenant_id, 404);
        $data = $request->validate(['role' => ['required', Rule::in(['admin', 'cashier'])]]);
        $user->update($data);

        return back()->with('success', 'Team role updated.');
    }
}
