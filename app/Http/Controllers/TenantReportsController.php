<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantReportsController extends Controller
{
    public function index(Request $request): Response
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $sales = Sale::query()->where('status', 'completed')->whereBetween('completed_at', [$from, $to]);

        return Inertia::render('Tenant/Reports/Index', [
            'filters' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'metrics' => [
                'sales' => (clone $sales)->count(),
                'revenue_minor' => (int) (clone $sales)->sum('total_minor'),
                'discount_minor' => (int) (clone $sales)->sum('discount_minor'),
                'items' => (int) SaleItem::query()->whereHas('sale', fn ($query) => $query->whereBetween('completed_at', [$from, $to]))->sum('quantity'),
            ],
            'byPayment' => (clone $sales)->select('payment_method', DB::raw('SUM(total_minor) as total_minor'), DB::raw('COUNT(*) as count'))->groupBy('payment_method')->get(),
            'recentSales' => (clone $sales)->latest('completed_at')->limit(20)->get(['id', 'transaction_uuid', 'total_minor', 'discount_minor', 'payment_method', 'completed_at']),
        ]);
    }
}