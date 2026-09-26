<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\StorefrontCheckoutRequest;
use App\Models\TenantPaymentIntent;
use App\Services\CheckoutService;
use App\Services\TenantPaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TenantPaymentController extends Controller
{
    public function storefrontCheckout(
        StorefrontCheckoutRequest $request,
        CheckoutService $checkoutService,
        TenantPaystackService $paystack,
    ): Response {
        $data = $request->validated();
        $payload = [
            'transaction_uuid' => (string) Str::uuid(),
            'payment_method' => $data['payment_method'],
            'source' => 'storefront',
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'delivery_location' => $data['delivery_location'],
            'items' => $data['items'],
        ];

        try {
            $quote = $checkoutService->quote($payload);
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages(['items' => $exception->getMessage()]);
        }

        $reference = 'enablstore-'.Str::uuid();
        $payload['tenders'] = [[
            'method' => $data['payment_method'],
            'amount_minor' => $quote['total_minor'],
            'provider_reference' => $reference,
        ]];

        $intent = TenantPaymentIntent::query()->create([
            'tenant_id' => (string) tenant()->getTenantKey(),
            'reference' => $reference,
            'context' => 'storefront',
            'amount_minor' => $quote['total_minor'],
            'payload' => $payload,
            'status' => 'pending',
        ]);

        try {
            $payment = $paystack->initialize(
                (string) tenant()->getTenantKey(),
                $data['customer_email'],
                $quote['total_minor'],
                $reference,
                route('tenant.storefront.payment.callback', ['tenant' => tenant()->getTenantKey()]),
                $data['payment_method'],
            );
        } catch (Throwable $exception) {
            $intent->update(['status' => 'failed']);
            Log::warning('Tenant storefront Paystack initialization failed.', [
                'tenant_id' => tenant()->getTenantKey(),
                'reference' => $reference,
                'exception' => $exception::class,
            ]);

            throw ValidationException::withMessages([
                'payment_method' => 'Paystack could not start payment. Check this subscriber’s live keys and try again.',
            ]);
        }

        return Inertia::location($payment['authorization_url']);
    }

    public function posCheckout(
        CheckoutRequest $request,
        CheckoutService $checkoutService,
    ): RedirectResponse {
        try {
            $checkoutService->checkout($request->validated());
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages(['tenders' => $exception->getMessage()]);
        }

        return back()->with('success', 'Sale completed successfully.');
    }

    public function callback(
        Request $request,
        CheckoutService $checkoutService,
        TenantPaystackService $paystack,
    ): RedirectResponse {
        return $this->completePayment($request, $checkoutService, $paystack, 'pos');
    }

    public function storefrontCallback(
        Request $request,
        CheckoutService $checkoutService,
        TenantPaystackService $paystack,
    ): RedirectResponse {
        return $this->completePayment($request, $checkoutService, $paystack, 'storefront');
    }

    private function completePayment(
        Request $request,
        CheckoutService $checkoutService,
        TenantPaystackService $paystack,
        string $context,
    ): RedirectResponse {
        $reference = (string) $request->query('reference', '');
        $intent = TenantPaymentIntent::query()
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('reference', $reference)
            ->first();

        if ($intent === null || $intent->context !== $context) {
            return $this->paymentRedirect($context, 'This payment could not be matched to an order.', true);
        }

        if ($intent->status === 'completed') {
            if ($context === 'pos') {
                $request->session()->flash('pos_completed_sale_id', $intent->payload['sale_id'] ?? null);
            }

            return $this->paymentRedirect($context, 'Payment was already verified.');
        }

        if ($intent->status !== 'pending') {
            return $this->paymentRedirect($context, 'This payment is no longer pending.', true);
        }

        try {
            $verified = $paystack->verify((string) tenant()->getTenantKey(), $reference);
        } catch (Throwable $exception) {
            Log::warning('Tenant Paystack verification failed.', [
                'tenant_id' => tenant()->getTenantKey(),
                'reference' => $reference,
                'exception' => $exception::class,
            ]);

            return $this->paymentRedirect($context, 'Payment verification failed. Contact the store before retrying.', true);
        }

        if (($verified['status'] ?? null) !== 'success'
            || (int) ($verified['amount'] ?? 0) !== $intent->amount_minor
            || strtoupper((string) ($verified['currency'] ?? '')) !== 'GHS'
            || (string) ($verified['reference'] ?? '') !== $reference) {
            $intent->update(['status' => 'failed']);

            return $this->paymentRedirect($context, 'Paystack did not confirm the expected amount. No order was recorded.', true);
        }

        $intent->update(['status' => 'processing']);

        try {
            $sale = $checkoutService->checkout($intent->payload);
        } catch (Throwable $exception) {
            $intent->update(['status' => 'paid_review']);
            Log::error('Verified tenant payment could not be finalized as a sale.', [
                'tenant_id' => tenant()->getTenantKey(),
                'reference' => $reference,
                'exception' => $exception::class,
            ]);

            return $this->paymentRedirect($context, 'Payment was received but stock finalization needs store review. Reference: '.$reference, true);
        }

        $intent->payload = array_merge($intent->payload, ['sale_id' => $sale->id]);
        $intent->status = 'completed';
        $intent->paid_at = now();
        $intent->save();
        if ($context === 'pos') {
            $request->session()->flash('pos_completed_sale_id', $sale->id);
        }

        return $this->paymentRedirect($context, 'Payment verified. Order reference: '.$sale->transaction_uuid);
    }

    private function paymentRedirect(string $context, string $message, bool $error = false): RedirectResponse
    {
        if ($context === 'pos') {
            return $error
                ? redirect()->route('tenant.pos')->withErrors(['payment' => $message])
                : redirect()->route('tenant.pos')->with('payment_success', $message);
        }

        return redirect()->route('tenant.home', ['tenant' => tenant()->getTenantKey()])
            ->with('checkout_status', $message)
            ->with('checkout_status_error', $error);
    }
}