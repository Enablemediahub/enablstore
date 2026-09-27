<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminFinancialController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'in:all,paystack,manual'],
            'status' => ['nullable', 'in:all,paid,pending,failed,refunded'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'renewal_window' => ['nullable', 'in:all,7_days'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $provider = $filters['provider'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $renewalWindow = $filters['renewal_window'] ?? 'all';
        $renewalStart = now()->startOfDay();
        $renewalEnd = now()->addDays(7)->endOfDay();

        $paymentsQuery = Payment::query()
            ->with(['tenant', 'subscription.plan'])
            ->whereNotNull('payments.subscription_id')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('payments.provider_reference', 'like', "%{$search}%")
                        ->orWhere('payments.tenant_id', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tenantQuery) => $tenantQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('subscriber_code', 'like', "%{$search}%")
                            ->orWhere('data->name', 'like', "%{$search}%"))
                        ->orWhereHas('subscription.plan', fn ($planQuery) => $planQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($provider !== 'all', fn ($query) => $query->where('payments.provider', $provider))
            ->when($status !== 'all', fn ($query) => $query->where('payments.status', $status))
            ->when(isset($filters['date_from']), fn ($query) => $query->whereDate('payments.created_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($query) => $query->whereDate('payments.created_at', '<=', $filters['date_to']))
            ->when($renewalWindow === '7_days', fn ($query) => $query->whereHas('subscription', function ($subscriptionQuery) use ($renewalStart, $renewalEnd): void {
                $subscriptionQuery->whereIn('status', ['active', 'trialing'])
                    ->where(function ($dateQuery) use ($renewalStart, $renewalEnd): void {
                        $dateQuery->whereBetween('ends_at', [$renewalStart, $renewalEnd])
                            ->orWhere(fn ($renewalQuery) => $renewalQuery->whereNull('ends_at')->whereBetween('renews_at', [$renewalStart, $renewalEnd]));
                    });
            }));

        $metricsQuery = clone $paymentsQuery;
        $paidQuery = (clone $metricsQuery)->where('payments.status', 'paid');

        return Inertia::render('SuperAdmin/Financial', [
            'payments' => $paymentsQuery->latest('payments.created_at')->paginate(20)->withQueryString()->through(function (Payment $payment): array {
                $tenant = $payment->tenant;

                return [
                    'id' => $payment->id,
                    'reference' => $payment->provider_reference,
                    'subscriber_code' => $tenant?->subscriber_code,
                    'subscriber' => $tenant?->name ?: ($tenant?->data['name'] ?? $tenant?->slug ?? $payment->tenant_id),
                    'email' => $tenant?->email ?: ($tenant?->data['email'] ?? null),
                    'phone' => $tenant?->phone ?: ($tenant?->data['phone'] ?? null),
                    'plan' => $payment->subscription?->plan?->name ?? 'Subscription',
                    'expires_at' => ($payment->subscription?->ends_at ?? $payment->subscription?->renews_at)?->toIso8601String(),
                    'amount_minor' => $payment->amount_minor,
                    'currency' => $payment->currency,
                    'provider' => $payment->provider,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at?->toIso8601String(),
                    'created_at' => $payment->created_at?->toIso8601String(),
                ];
            }),
            'filters' => [
                'search' => $search,
                'provider' => $provider,
                'status' => $status,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'renewal_window' => $renewalWindow,
            ],
            'metrics' => [
                'revenue_minor' => (clone $paidQuery)->sum('payments.amount_minor'),
                'paid_count' => (clone $paidQuery)->count(),
                'paystack_minor' => (clone $paidQuery)->where('payments.provider', 'paystack')->sum('payments.amount_minor'),
                'manual_minor' => (clone $paidQuery)->where('payments.provider', 'manual')->sum('payments.amount_minor'),
                'pending_count' => (clone $metricsQuery)->where('payments.status', 'pending')->count(),
                'upcoming_renewals_count' => Subscription::query()
                    ->whereIn('status', ['active', 'trialing'])
                    ->where(function ($dateQuery) use ($renewalStart, $renewalEnd): void {
                        $dateQuery->whereBetween('ends_at', [$renewalStart, $renewalEnd])
                            ->orWhere(fn ($renewalQuery) => $renewalQuery->whereNull('ends_at')->whereBetween('renews_at', [$renewalStart, $renewalEnd]));
                    })
                    ->distinct('tenant_id')
                    ->count('tenant_id'),
            ],
        ]);
    }
}