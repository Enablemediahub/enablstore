<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Plan;
use App\Services\SubscriptionPlanManager;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class SubscriptionPlans extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.super-admin.pages.subscription-plans';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Subscription plans';

    protected static ?string $navigationGroup = 'Platform tools';

    protected static ?string $title = 'Subscription plans';

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('createPlan')
                ->label('Create plan')
                ->icon('heroicon-o-plus')
                ->fillForm([
                    'billing_interval_months' => 1,
                    'features' => ['pos', 'online_store'],
                    'is_active' => true,
                ])
                ->form($this->planFormSchema())
                ->action(fn (array $data) => app(SubscriptionPlanManager::class)->create($data))
                ->successNotificationTitle('Subscription plan created.'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Plan::query()->withCount('subscriptions')->orderByDesc('is_active')->orderBy('price_minor'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Plan $record): ?string => $record->description),
                TextColumn::make('price_minor')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state, Plan $record): string => $record->currency.' '.number_format($state / 100, 2))
                    ->sortable(),
                TextColumn::make('billing_interval_months')
                    ->label('Billing interval')
                    ->formatStateUsing(fn (int $state): string => $this->billingLabel($state))
                    ->sortable(),
                TextColumn::make('features')
                    ->label('Included access')
                    ->badge(),
                TextColumn::make('is_active')
                    ->label('Availability')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Offered' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('subscriptions_count')
                    ->label('Subscribers')
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('editPlan')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (Plan $record): array => [
                        'name' => $record->name,
                        'description' => $record->description,
                        'price_ghs' => number_format($record->price_minor / 100, 2, '.', ''),
                        'billing_interval_months' => $record->billing_interval_months,
                        'features' => $record->features ?? [],
                        'is_active' => $record->is_active,
                    ])
                    ->form($this->planFormSchema())
                    ->action(fn (Plan $record, array $data) => app(SubscriptionPlanManager::class)->update($record, $data))
                    ->successNotificationTitle('Subscription plan updated.'),
                TableAction::make('toggleAvailability')
                    ->label(fn (Plan $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (Plan $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Plan $record) => $record->update(['is_active' => ! $record->is_active]))
                    ->successNotificationTitle('Plan availability updated.'),
            ])
            ->defaultSort('is_active', 'desc');
    }

    /** @return array<int, mixed> */
    private function planFormSchema(): array
    {
        return [
            TextInput::make('name')->required()->maxLength(100),
            Textarea::make('description')->maxLength(1000),
            TextInput::make('price_ghs')
                ->label('Price (GHS)')
                ->numeric()
                ->minValue(0.01)
                ->maxValue(1000000)
                ->required(),
            TextInput::make('billing_interval_months')
                ->label('Billing duration (months)')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->maxValue(120)
                ->required(),
            CheckboxList::make('features')
                ->label('Included access')
                ->options([
                    'pos' => 'Point of Sale',
                    'online_store' => 'Online Store',
                    'restaurant_foodstore' => 'FoodStore',
                    'products' => 'Products',
                    'inventory' => 'Inventory',
                    'sales_expenses' => 'Sales and expenses',
                    'audit_log' => 'Audit log',
                    'whatsapp_orders' => 'WhatsApp ordering',
                ])
                ->columns(2)
                ->dehydrated(),
            Toggle::make('is_active')
                ->label('Offer this plan to new subscribers')
                ->default(true)
                ->required(),
        ];
    }

    private function billingLabel(int $months): string
    {
        return match ($months) {
            1 => 'Monthly',
            3 => 'Quarterly',
            6 => 'Every 6 months',
            12 => 'Yearly',
            default => "Every {$months} months",
        };
    }
}
