<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantSalesExpensesController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $from = isset($filters['from']) ? now()->parse($filters['from'])->startOfDay() : now()->startOfMonth();
        $to = isset($filters['to']) ? now()->parse($filters['to'])->endOfDay() : now()->endOfDay();
        $sales = Sale::query()->where('status', 'completed')->whereBetween('completed_at', [$from, $to]);
        $expenses = Expense::query()->whereBetween('spent_at', [$from, $to]);
        $revenueMinor = (int) (clone $sales)->sum('total_minor');
        $expenseMinor = (int) (clone $expenses)->sum('amount_minor');

        return Inertia::render('Tenant/SalesExpenses/Index', [
            'filters' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'metrics' => [
                'sales_count' => (clone $sales)->count(),
                'online_revenue_minor' => (clone $sales)->where('source', 'storefront')->sum('total_minor'),
                'pos_revenue_minor' => (clone $sales)->where('source', '!=', 'storefront')->sum('total_minor'),
                'revenue_minor' => $revenueMinor,
                'expenses_minor' => $expenseMinor,
                'net_minor' => $revenueMinor - $expenseMinor,
            ],
            'recentSales' => (clone $sales)->latest('completed_at')->limit(15)->get([
                'id', 'transaction_uuid', 'source', 'payment_method', 'total_minor', 'completed_at',
            ]),
            'recentExpenses' => (clone $expenses)->latest('spent_at')->limit(15)->get([
                'id', 'category', 'description', 'amount_minor', 'payment_method', 'spent_at',
            ]),
            'status' => session('status'),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount_ghs' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'payment_method' => ['required', 'in:cash,bank_transfer,mobile_money,card,other'],
            'spent_at' => ['required', 'date_format:Y-m-d'],
        ]);

        Expense::query()->create([
            'category' => trim($data['category']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'amount_minor' => (int) round((float) $data['amount_ghs'] * 100),
            'currency' => 'GHS',
            'payment_method' => $data['payment_method'],
            'spent_at' => now()->parse($data['spent_at'])->startOfDay(),
        ]);

        return back()->with('status', 'Expense recorded.');
    }
};