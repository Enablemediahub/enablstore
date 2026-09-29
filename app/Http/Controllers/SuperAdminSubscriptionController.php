<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\SubscriptionPlanManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminSubscriptionController extends Controller
{
    private const FEATURES = ['products', 'inventory', 'pos', 'online_store', 'restaurant_foodstore', 'sales_expenses', 'audit_log', 'whatsapp_orders'];

    public function index(): Response
    {
        return Inertia::render('SuperAdmin/SubscriptionSettings', [
            'plans' => Plan::query()->orderByDesc('is_active')->orderBy('price_minor')->get([
                'id', 'name', 'description', 'price_minor', 'currency', 'billing_interval_months', 'features', 'is_active',
            ]),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request, SubscriptionPlanManager $plans): RedirectResponse
    {
        $plans->create($this->validatedData($request));

        return back()->with('status', 'Subscription plan created.');
    }

    public function update(Request $request, Plan $plan, SubscriptionPlanManager $plans): RedirectResponse
    {
        $plans->update($plan, $this->validatedData($request));

        return back()->with('status', 'Subscription plan updated.');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_ghs' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'billing_interval_months' => ['required', 'integer', 'min:1', 'max:120'],
            'features' => ['present', 'array'],
            'features.*' => ['string', 'in:'.implode(',', self::FEATURES)],
            'is_active' => ['required', 'boolean'],
        ]);
    }

}