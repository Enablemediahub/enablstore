<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminUserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Users', [
            'users' => User::query()->with('tenant')->latest()->paginate(20),
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'features']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'business_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:60', 'unique:tenants,id'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        DB::transaction(function () use ($data): void {
            $tenant = Tenant::create([
                'id' => strtolower($data['slug']),
                'name' => $data['business_name'],
                'slug' => strtolower($data['slug']),
                'email' => $data['email'],
                'status' => 'active',
            ]);
            $tenant->subscriptions()->create([
                'plan_id' => $data['plan_id'],
                'provider' => 'internal',
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => now()->addMonth(),
            ]);
            User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'tenant_id' => $tenant->id,
                'password' => $data['password'],
                'role' => 'admin',
            ]);
        });

        return back()->with('status', 'Workspace user created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        $user->fill(collect($data)->except('password')->all());
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('status', 'Workspace user updated.');
    }
}
