<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PlatformOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeWorkspaces = Tenant::query()
            ->where(fn (Builder $query): Builder => $query->where('status', 'active')->orWhere('data->status', 'active'))
            ->count();
        $suspendedWorkspaces = Tenant::query()
            ->where(fn (Builder $query): Builder => $query->where('status', 'suspended')->orWhere('data->status', 'suspended'))
            ->count();
        $monthStart = now()->startOfMonth();

        return [
            Stat::make('Subscribers', Tenant::query()->count())
                ->description("{$activeWorkspaces} active, {$suspendedWorkspaces} suspended")
                ->color('primary')
                ->url('/super-admin/tenant-directory'),
            Stat::make('Active subscriptions', Subscription::query()->where('status', 'active')->count())
                ->description('Currently active plans')
                ->color('success'),
            Stat::make('Trialing subscriptions', Subscription::query()->where('status', 'trialing')->count())
                ->description('Plans currently in a trial period')
                ->color('warning'),
            Stat::make('Pending payments', Payment::query()->where('status', 'pending')->count())
                ->description('Awaiting payment confirmation')
                ->color('warning'),
            Stat::make('Revenue this month', 'GHS '.number_format(Payment::query()
                ->where('status', 'paid')
                ->where('paid_at', '>=', $monthStart)
                ->sum('amount_minor') / 100, 2))
                ->description('Payments recorded since the start of the month')
                ->color('success'),
            Stat::make('Paid revenue total', 'GHS '.number_format(Payment::query()->where('status', 'paid')->sum('amount_minor') / 100, 2))
                ->description('All recorded subscription payments')
                ->color('gray'),
        ];
    }
}