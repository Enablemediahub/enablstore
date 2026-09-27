<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTenantRequest;
use App\Models\Plan;
use App\Services\TenantRegistrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Tenant/Register', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('price_minor')->get([
                'id', 'name', 'description', 'price_minor', 'currency', 'billing_interval_months',
            ]),
        ]);
    }

    public function store(
        RegisterTenantRequest $request,
        TenantRegistrationService $registrationService,
    ): RedirectResponse {
        $result = $registrationService->register($request);

        return redirect()
            ->route('tenant.home', ['tenant' => $result['tenant']->slug])
            ->with('success', 'Your store has been created.');
    }
}
