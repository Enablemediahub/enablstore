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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'alpha_dash', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'pos_pin' => ['nullable', 'digits_between:4,6', 'required_if:role,cashier'],
        ]);

        User::query()->create([
            ...$data,
            'tenant_id' => $request->user()->tenant_id,
            'password' => Hash::make($data['password']),
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