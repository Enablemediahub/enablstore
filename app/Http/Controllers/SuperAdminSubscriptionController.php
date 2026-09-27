<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminSubscriptionController extends Controller
{
    private const FEATURES = ['products', 'inventory', 'pos', 'online_store', 'restaurant_foodstore'];

    public function index(): Response
    {
        return Inertia::render('SuperAdmin/SubscriptionSettings', [
            'plans' => Plan::query()->orderByDesc('is_active')->orderBy('price_minor')->get([
                'id', 'name', 'description', 'price_minor', 'currency', 'billing_interval_months', 'features', 'is_active',
            ]),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $slug = Str::slug($data['name']);
        $baseSlug = $slug !== '' ? $slug : 'subscription';
        $suffix = 2;
        while (Plan::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        Plan::query()->create($this->attributes($data) + ['slug' => $slug]);

        return back()->with('status', 'Subscription plan created.');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->attributes($this->validatedData($request)));

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

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $months = (int) $data['billing_interval_months'];

        return [
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'price_minor' => (int) round((float) $data['price_ghs'] * 100),
            'currency' => 'GHS',
            'billing_interval' => match ($months) {
                1 => 'monthly',
                3 => 'quarterly',
                6 => 'semiannual',
                12 => 'yearly',
                default => "every_{$months}_months",
            },
            'billing_interval_months' => $months,
            'features' => array_values(array_unique($data['features'])),
            'is_active' => (bool) $data['is_active'],
        ];
    }
}