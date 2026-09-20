<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user === null) {
            return Inertia::render('Dashboard', [
                'user' => null,
                'tenant' => null,
                'subscription' => null,
                'access' => [
                    'onlineStore' => false,
                    'pos' => false,
                ],
            ]);
        }

        $tenant = $user->tenant ?? Tenant::query()->first();
        $subscription = $tenant?->subscriptions()->with('plan')->latest()->first();
        $features = $subscription?->plan?->features ?? [];

        return Inertia::render('Dashboard', [
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'tenant' => $tenant === null ? null : [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'subscription' => $subscription === null ? null : [
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status,
            ],
            'access' => [
                'onlineStore' => in_array('online_store', $features, true),
                'pos' => in_array('pos', $features, true),
            ],
        ]);
    }
}
