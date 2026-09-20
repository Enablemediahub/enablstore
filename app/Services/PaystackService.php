<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    private function client(): PendingRequest
    {
        $secretKey = config('paystack.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Paystack is not configured.');
        }

        return Http::baseUrl((string) config('paystack.base_url'))
            ->withToken($secretKey)
            ->acceptJson();
    }

    /**
     * Initialize a Paystack transaction in Ghanaian cedis.
     *
     * @param  array{email: string, amount_minor: int, reference: string, callback_url: string}  $payload
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initialize(array $payload): array
    {
        $response = $this->client()->post('/transaction/initialize', [
            'email' => $payload['email'],
            'amount' => $payload['amount_minor'],
            'currency' => 'GHS',
            'reference' => $payload['reference'],
            'callback_url' => $payload['callback_url'],
        ])->throw()->json('data');

        return [
            'authorization_url' => (string) $response['authorization_url'],
            'access_code' => (string) $response['access_code'],
            'reference' => (string) $response['reference'],
        ];
    }
}
