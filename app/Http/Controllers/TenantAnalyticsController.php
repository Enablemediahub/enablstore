<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantAnalyticsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Tenant/Analytics/Index', [
            'metrics' => [
                'sales_count' => Sale::query()->where('status', 'completed')->count(),
                'revenue_minor' => (int) Sale::query()->where('status', 'completed')->sum('total_minor'),
                'items_sold' => (int) SaleItem::query()->sum('quantity'),
            ],
            'topProducts' => SaleItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as quantity'))
                ->with('product:id,name')
                ->groupBy('product_id')
                ->orderByDesc('quantity')
                ->limit(10)
                ->get(),
        ]);
    }
}
