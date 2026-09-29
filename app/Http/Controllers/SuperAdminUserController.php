<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriberEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminUserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Users', [
            'users' => User::query()->with('tenant')->latest()->paginate(20),
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'features', 'price_minor', 'currency', 'billing_interval_months']),
        ]);
    }

    public function store(Request $request, SubscriberEnrollmentService $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'alpha_dash', 'max:52'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'business_name' => ['required', 'string', 'max:120'],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'features' => ['present', 'array'],
            'features.*' => ['string', 'in:pos,online_store,restaurant_foodstore,sales_expenses,audit_log,whatsapp_orders'],
        ]);

        $enrollment->create($data);

        return back()->with('status', 'Workspace user created.');
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
