<?php

declare(strict_types=1);

return [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
];
