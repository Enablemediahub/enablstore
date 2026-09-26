<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantPaystackSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TenantPaystackService
{
    /** @return array{authorization_url: string, reference: string} */
    public function initialize(string $tenantId, string $email, int $amountMinor, string $reference, string $callbackUrl, string $channel): array
    {
        $settings = $this->settings($tenantId);
        $secretKey = $settings->mode === 'test' ? $settings->test_secret_key : $settings->secret_key;
        $channels = $channel === 'card' ? ['card'] : ['mobile_money'];

        $data = Http::baseUrl((string) config('paystack.base_url'))
            ->withToken((string) $secretKey)
            ->acceptJson()
            ->post('/transaction/initialize', [
                'email' => $email,
                'amount' => $amountMinor,
                'currency' => 'GHS',
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'channels' => $channels,
                'metadata' => ['tenant_id' => $tenantId],
            ])
            ->throw()
            ->json('data');

        return [
            'authorization_url' => (string) ($data['authorization_url'] ?? ''),
            'reference' => (string) ($data['reference'] ?? $reference),
        ];
    }

    /** @return array<string, mixed> */
    public function verify(string $tenantId, string $reference): array
    {
        $settings = $this->settings($tenantId);
        $secretKey = $settings->mode === 'test' ? $settings->test_secret_key : $settings->secret_key;

        $data = Http::baseUrl((string) config('paystack.base_url'))
            ->withToken((string) $secretKey)
            ->acceptJson()
            ->get('/transaction/verify/'.rawurlencode($reference))
            ->throw()
            ->json('data');

        return is_array($data) ? $data : [];
    }

    private function settings(string $tenantId): TenantPaystackSetting
    {
        $settings = TenantPaystackSetting::query()
            ->where('tenant_id', $tenantId)
            ->where('enabled', true)
            ->first();

        $publicKey = $settings?->mode === 'test' ? $settings?->test_public_key : $settings?->public_key;
        $secretKey = $settings?->mode === 'test' ? $settings?->test_secret_key : $settings?->secret_key;

        if ($settings === null || ! filled($publicKey) || ! filled($secretKey)) {
            throw new RuntimeException('Paystack is not configured for the selected mode for this subscriber.');
        }

        return $settings;
    }
}