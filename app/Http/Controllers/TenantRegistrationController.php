<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTenantRequest;
use App\Services\TenantRegistrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Tenant/Register');
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
