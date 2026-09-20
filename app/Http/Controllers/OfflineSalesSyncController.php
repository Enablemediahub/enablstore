<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SyncOfflineSalesRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class OfflineSalesSyncController extends Controller
{
    public function __invoke(
        SyncOfflineSalesRequest $request,
        CheckoutService $checkoutService,
    ): JsonResponse {
        $synced = 0;

        foreach ($request->validated('sales') as $sale) {
            $checkoutService->checkout($sale);
            $synced++;
        }

        return response()->json(['synced' => $synced]);
    }
}
