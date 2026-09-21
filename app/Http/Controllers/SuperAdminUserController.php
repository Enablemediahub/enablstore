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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            'username' => ['required', 'string', 'alpha_dash', 'max:52'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'business_name' => ['required', 'string', 'max:120'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $slug = $this->uniqueTenantSlug($data['business_name']);
        $subscriberCode = $this->nextSubscriberCode();
        $username = $subscriberCode.'-'.strtolower($data['username']);

        if (User::query()->where('username', $username)->exists()) {
            return back()->withErrors(['username' => 'This username is already in use for the generated subscriber code.'])->withInput();
        }
        $tenant = Tenant::create([
                'id' => $slug,
                'subscriber_code' => $subscriberCode,
                'name' => $data['business_name'],
                'slug' => $slug,
                'email' => $data['email'],
                'status' => 'active',
        ]);

        DB::transaction(function () use ($data, $tenant): void {
            $tenant->subscriptions()->create([
                'plan_id' => $data['plan_id'],
                'provider' => 'internal',
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => now()->addMonth(),
            ]);
            User::create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'],
                'tenant_id' => $tenant->id,
                'password' => $data['password'],
                'role' => 'admin',
            ]);
        });

        return back()->with('status', 'Workspace user created.');
    }

    private function uniqueTenantSlug(string $businessName): string
    {
        $base = Str::slug($businessName);
        $base = Str::limit($base !== '' ? $base : 'subscriber', 52, '');
        $slug = $base;
        $suffix = 2;

        while (Tenant::query()->whereKey($slug)->exists()) {
            $slug = Str::limit($base, 52 - strlen((string) $suffix), '').'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function nextSubscriberCode(): string
    {
        $number = Tenant::query()->whereNotNull('subscriber_code')->get(['subscriber_code'])->map(fn (Tenant $tenant): int => (int) substr((string) $tenant->subscriber_code, 2))->max() + 1;

        return 'ES'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
        ]);
        if ($user->role === 'admin' && $data['role'] !== 'admin' && User::query()->where('tenant_id', $user->tenant_id)->where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'A subscriber must keep at least one administrator. Add another administrator before changing this role.']);
        }
        $subscriberCode = strtolower((string) $user->tenant?->subscriber_code);
        if ($subscriberCode !== '' && ! str_starts_with(strtolower($data['username']), $subscriberCode.'-')) {
            return back()->withErrors(['username' => "This username must begin with {$user->tenant->subscriber_code}-."]);
        }

        $user->fill([
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'] ?: null,
            'role' => $data['role'],
        ]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('status', 'Workspace user updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'admin' && User::query()->where('tenant_id', $user->tenant_id)->where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['team' => 'A subscriber must keep at least one administrator. Add another administrator before deleting this account.']);
        }

        $user->delete();

        return back()->with('status', 'Workspace user deleted. The tenant and its subscription were kept.');
    }

    public function resetAccess(Request $request, User $user): RedirectResponse
    {
        if ($user->role === 'cashier') {
            $data = $request->validate(['pin' => ['required', 'digits_between:4,6']]);
            $user->update(['pos_pin_hash' => Hash::make($data['pin'])]);

            return back()->with('status', "POS PIN reset for {$user->name}.");
        }

        $data = $request->validate(['password' => ['required', 'string', 'min:8']]);
        $user->update(['password' => $data['password']]);

        return back()->with('status', "Login password reset for {$user->name}.");
    }
}
