<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantAnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->toDateString())->endOfDay();
        $search = trim((string) ($validated['search'] ?? ''));
        $sales = Sale::query()->where('status', 'completed')->whereBetween('completed_at', [$from, $to])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(fn (Builder $saleQuery) => $saleQuery->where('transaction_uuid', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%")->orWhere('customer_phone', 'like', "%{$search}%"));
            });
        $metrics = (clone $sales)->selectRaw('COUNT(*) as sales_count, COALESCE(SUM(total_minor), 0) as revenue_minor, COALESCE(SUM(discount_minor), 0) as discounts_minor, COALESCE(AVG(total_minor), 0) as average_sale_minor')->first();
        $saleIds = (clone $sales)->select('id');

        return Inertia::render('Tenant/Analytics/Index', [
            'filters' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'search' => $search],
            'metrics' => [
                'sales_count' => (int) $metrics->sales_count,
                'revenue_minor' => (int) $metrics->revenue_minor,
                'discounts_minor' => (int) $metrics->discounts_minor,
                'average_sale_minor' => (int) round((float) $metrics->average_sale_minor),
                'items_sold' => (int) SaleItem::query()->whereIn('sale_id', $saleIds)->sum('quantity'),
            ],
            'dailySales' => (clone $sales)->selectRaw('DATE(completed_at) as date, COUNT(*) as sales_count, COALESCE(SUM(total_minor), 0) as revenue_minor')->groupBy('date')->orderBy('date')->get(),
            'paymentMethods' => (clone $sales)->selectRaw('payment_method, COUNT(*) as sales_count, COALESCE(SUM(total_minor), 0) as revenue_minor')->groupBy('payment_method')->orderByDesc('revenue_minor')->get(),
            'topProducts' => SaleItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(line_total_minor) as revenue_minor'))
                ->whereIn('sale_id', $saleIds)
                ->with('product:id,name')
                ->groupBy('product_id')
                ->orderByDesc('revenue_minor')
                ->limit(8)
                ->get(),
            'recentSales' => (clone $sales)->latest('completed_at')->limit(8)->get(['transaction_uuid', 'customer_name', 'payment_method', 'total_minor', 'completed_at']),
        ]);
    }
}
