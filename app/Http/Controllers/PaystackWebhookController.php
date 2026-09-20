<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $secret = (string) config('paystack.webhook_secret');
        $payload = $request->getContent();

        if (! is_string($signature) || $secret === '' || ! hash_equals($secret, hash_hmac('sha512', $payload, $secret))) {
            abort(401, 'Invalid webhook signature.');
        }

        $event = $request->json()->all();

        DB::transaction(function () use ($event): void {
            $data = $event['data'] ?? [];
            $reference = (string) ($data['reference'] ?? '');

            if ($reference === '') {
                return;
            }

            if ($event['event'] === 'charge.success') {
                $payment = Payment::query()->updateOrCreate(
                    ['provider_reference' => $reference],
                    [
                        'tenant_id' => (string) ($data['metadata']['tenant_id'] ?? ''),
                        'subscription_id' => $data['metadata']['subscription_id'] ?? null,
                        'provider' => 'paystack',
                        'amount_minor' => (int) ($data['amount'] ?? 0),
                        'currency' => (string) ($data['currency'] ?? 'GHS'),
                        'status' => 'paid',
                        'paid_at' => now(),
                        'metadata' => $data,
                    ],
                );

                if ($payment->subscription_id !== null) {
                    Subscription::query()
                        ->whereKey($payment->subscription_id)
                        ->update(['status' => 'active']);
                }
            }

            if (in_array($event['event'], ['subscription.disable', 'invoice.payment_failed'], true)) {
                Subscription::query()
                    ->where('provider_reference', $reference)
                    ->update(['status' => $event['event'] === 'subscription.disable' ? 'disabled' : 'past_due']);
            }
        });

        return response()->json(['received' => true]);
    }
}
