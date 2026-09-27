<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPaymentIntent;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TenantSubscriptionController extends Controller
{
    public function suspended(): Response
    {
        $tenant = tenant();
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();

        return Inertia::render('Tenant/Suspended', [
            'tenant' => ['id' => $tenant->getTenantKey(), 'name' => $tenant->name],
            'accountActive' => $tenant->status === 'active',
            'contactEmail' => $tenant->email,
            'subscriptionAmountMinor' => $subscription?->amount_minor ?? $subscription?->plan?->price_minor,
            'billingInterval' => $subscription?->plan?->billing_interval ?? 'monthly',
            'billingIntervalMonths' => $subscription?->plan?->billing_interval_months ?? 1,
            'status' => session('subscription_status'),
            'error' => session('subscription_error'),
        ]);
    }

    public function checkout(Request $request, PaystackService $paystack): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $tenant = tenant();
        abort_unless($tenant->status === 'suspended', 409, 'This tenant is not suspended.');

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        abort_unless($subscription !== null, 422, 'This tenant has no subscription to renew.');
        $amountMinor = $subscription->amount_minor ?? $subscription->plan?->price_minor;
        abort_unless(is_int($amountMinor) && $amountMinor > 0, 422, 'A subscription amount has not been configured. Contact Enablstore support.');
        $companyPublicKey = config('paystack.public_key');
        if ($request->expectsJson() && (! is_string($companyPublicKey) || $companyPublicKey === '')) {
            throw ValidationException::withMessages([
                'email' => 'Company Paystack is not configured. Please contact Enablstore support.',
            ]);
        }

        $reference = 'enablstore-sub-'.Str::uuid();
        $intent = TenantPaymentIntent::query()->create([
            'tenant_id' => (string) $tenant->getTenantKey(),
            'reference' => $reference,
            'context' => 'subscription_renewal',
            'amount_minor' => $amountMinor,
            'payload' => ['subscription_id' => $subscription->id],
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'reference' => $reference,
                'amount_minor' => $amountMinor,
                'public_key' => $companyPublicKey,
                'callback_url' => route('tenant.subscription.callback', ['tenant' => $tenant->getTenantKey()]),
            ]);
        }

        try {
            $payment = $paystack->initialize([
                'email' => $data['email'],
                'amount_minor' => $amountMinor,
                'reference' => $reference,
                'callback_url' => route('tenant.subscription.callback', ['tenant' => $tenant->getTenantKey()]),
            ]);
        } catch (Throwable $exception) {
            $intent->update(['status' => 'failed']);
            Log::warning('Company Paystack subscription initialization failed.', [
                'tenant_id' => $tenant->getTenantKey(),
                'reference' => $reference,
                'exception' => $exception::class,
            ]);

            throw ValidationException::withMessages([
                'email' => 'Company Paystack could not start the subscription payment. Please try again or contact Enablstore support.',
            ]);
        }

        return Inertia::location($payment['authorization_url']);
    }

    public function callback(Request $request, PaystackService $paystack): RedirectResponse|JsonResponse
    {
        $tenant = tenant();
        $reference = (string) $request->query('reference', $request->query('trxref', ''));
        $intent = TenantPaymentIntent::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->where('context', 'subscription_renewal')
            ->where('reference', $reference)
            ->first();

        if ($intent === null) {
            return $this->paymentResult($request, $tenant, 'error', 'This subscription payment could not be matched. Contact Enablstore support.', 404);
        }

        if ($intent->status === 'completed') {
            return $this->paymentResult($request, $tenant, 'success', 'Payment was already verified.');
        }

        if ($intent->status !== 'pending') {
            return $this->paymentResult($request, $tenant, 'error', 'This subscription payment is no longer pending.', 409);
        }

        try {
            $verified = $paystack->verify($reference);
        } catch (Throwable $exception) {
            Log::warning('Company Paystack subscription verification failed.', [
                'tenant_id' => $tenant->getTenantKey(),
                'reference' => $reference,
                'exception' => $exception::class,
            ]);

            return $this->paymentResult($request, $tenant, 'error', 'We could not verify the payment yet. Please retry or contact support.', 503);
        }

        if (($verified['status'] ?? null) !== 'success'
            || (int) ($verified['amount'] ?? 0) !== $intent->amount_minor
            || strtoupper((string) ($verified['currency'] ?? '')) !== 'GHS'
            || (string) ($verified['reference'] ?? '') !== $reference) {
            $intent->update(['status' => 'failed']);

            return $this->paymentResult($request, $tenant, 'error', 'Paystack did not confirm the expected amount. Your workspace remains suspended.', 422);
        }

        DB::connection(config('tenancy.database.central_connection'))
            ->transaction(function () use ($tenant, $intent, $reference, $verified): void {
            $lockedIntent = TenantPaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);
            if ($lockedIntent->status === 'completed') {
                return;
            }
            abort_unless($lockedIntent->status === 'pending', 409);

            $subscription = Subscription::query()
                ->with('plan')
                ->where('tenant_id', $tenant->getTenantKey())
                ->lockForUpdate()
                ->findOrFail($lockedIntent->payload['subscription_id']);
            $renewalBase = $subscription->renews_at?->isFuture() ? $subscription->renews_at->copy() : now();
            $renewsAt = match ($subscription->plan?->billing_interval) {
                'weekly' => $renewalBase->addWeek(),
                'daily' => $renewalBase->addDay(),
                default => $renewalBase->addMonthsNoOverflow(max(1, (int) ($subscription->plan?->billing_interval_months ?? 1))),
            };

            Payment::query()->create([
                'tenant_id' => (string) $tenant->getTenantKey(),
                'subscription_id' => $subscription->id,
                'provider' => 'paystack',
                'provider_reference' => $reference,
                'amount_minor' => $intent->amount_minor,
                'currency' => 'GHS',
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => [
                    'source' => 'company_subscription_renewal',
                    'paystack_status' => $verified['status'] ?? null,
                ],
            ]);

            $subscription->update([
                'provider' => 'paystack',
                'provider_reference' => $reference,
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => $renewsAt,
                'ends_at' => null,
                'grace_ends_at' => null,
            ]);
            DB::connection(config('tenancy.database.central_connection'))
                ->table('tenants')
                ->where('id', $tenant->getTenantKey())
                ->update(['status' => 'active']);
            $lockedIntent->update(['status' => 'completed', 'paid_at' => now()]);
            });

        return $this->paymentResult($request, $tenant, 'success', 'Payment received. Your workspace service has been restored.');
    }

    private function paymentResult(Request $request, Tenant $tenant, string $status, string $message, int $httpStatus = 200): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'account_active' => $tenant->fresh()->status === 'active',
            ], $httpStatus);
        }

        return redirect()->route('tenant.subscription.suspended', ['tenant' => $tenant->getTenantKey()])
            ->with($status === 'success' ? 'subscription_status' : 'subscription_error', $message);
    }
}
