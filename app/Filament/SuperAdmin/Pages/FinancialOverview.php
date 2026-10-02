<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Payment;
use App\Models\Subscription;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.super-admin.pages.financial-overview';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Financial overview';

    protected static ?string $navigationGroup = 'Platform tools';

    protected static ?string $title = 'Financial overview';

    protected static ?int $navigationSort = 2;

    public function mount(): void
    {
        $this->tableSearch = trim((string) request()->query('tableSearch', ''));
        $filters = [];

        foreach (['provider', 'status'] as $filter) {
            $value = request()->query($filter);
            if (filled($value) && $value !== 'all') {
                $filters[$filter] = ['value' => $value];
            }
        }

        $dateRange = array_filter([
            'from' => request()->query('date_from'),
            'until' => request()->query('date_to'),
        ]);
        if ($dateRange !== []) {
            $filters['date_range'] = $dateRange;
        }

        if (request()->query('renewal_window') === '7_days') {
            $filters['upcoming_renewals'] = ['isActive' => true];
        }

        $this->tableFilters = $filters ?: null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Payment::query()->with(['tenant', 'subscription.plan'])->whereNotNull('subscription_id'))
            ->columns([
                TextColumn::make('provider_reference')->label('Reference')->searchable()->copyable(),
                TextColumn::make('subscriber')
                    ->label('Subscriber')
                    ->state(fn (Payment $record): string => $record->tenant?->name ?: ($record->tenant?->data['name'] ?? $record->tenant_id))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('tenant', fn (Builder $tenantQuery): Builder => $tenantQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('data->name', 'like', "%{$search}%")
                        ->orWhere('subscriber_code', 'like', "%{$search}%"))),
                TextColumn::make('subscription.plan.name')->label('Plan')->default('Subscription')->searchable(),
                TextColumn::make('amount_minor')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state, Payment $record): string => $record->currency.' '.number_format($state / 100, 2))
                    ->sortable(),
                TextColumn::make('provider')->badge()->formatStateUsing(fn (string $state): string => str($state)->headline()->toString()),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed', 'refunded' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')->label('Paid at')->dateTime()->sortable()->placeholder('Not paid'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]),
                SelectFilter::make('provider')->options([
                    'paystack' => 'Paystack',
                    'manual' => 'Manual',
                ]),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Created from'),
                        DatePicker::make('until')->label('Created until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))),
                Filter::make('upcoming_renewals')
                    ->label('Renewals due in 7 days')
                    ->query(function (Builder $query): Builder {
                        $start = now()->startOfDay();
                        $end = now()->addDays(7)->endOfDay();

                        return $query->whereHas('subscription', fn (Builder $subscriptionQuery): Builder => $subscriptionQuery
                            ->whereIn('status', ['active', 'trialing'])
                            ->where(fn (Builder $dates): Builder => $dates
                                ->whereBetween('ends_at', [$start, $end])
                                ->orWhere(fn (Builder $renewals): Builder => $renewals
                                    ->whereNull('ends_at')
                                    ->whereBetween('renews_at', [$start, $end]))));
                    }),
            ])
            ->actions([
                Action::make('deletePayment')
                    ->label('Delete payment')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete this payment record?')
                    ->modalDescription('This permanently deletes the selected payment record from the financial history. The associated subscription will not be deleted.')
                    ->action(fn (Payment $record) => $record->delete())
                    ->successNotificationTitle('Payment record deleted.'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getViewData(): array
    {
        $start = now()->startOfDay();
        $end = now()->addDays(7)->endOfDay();

        return [
            'metrics' => [
                'revenue' => 'GHS '.number_format(Payment::query()
                    ->whereNotNull('subscription_id')
                    ->where('status', 'paid')
                    ->sum('amount_minor') / 100, 2),
                'paid_count' => Payment::query()->whereNotNull('subscription_id')->where('status', 'paid')->count(),
                'pending_count' => Payment::query()->whereNotNull('subscription_id')->where('status', 'pending')->count(),
                'upcoming_renewals' => Subscription::query()
                    ->whereIn('status', ['active', 'trialing'])
                    ->where(fn (Builder $dates): Builder => $dates
                        ->whereBetween('ends_at', [$start, $end])
                        ->orWhere(fn (Builder $renewals): Builder => $renewals
                            ->whereNull('ends_at')
                            ->whereBetween('renews_at', [$start, $end])))
                    ->distinct('tenant_id')
                    ->count('tenant_id'),
            ],
        ];
    }
}
