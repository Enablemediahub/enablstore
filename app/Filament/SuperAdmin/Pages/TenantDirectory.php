<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\SubscriberEnrollmentService;
use Closure;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantDirectory extends Page implements HasTable
{
    use InteractsWithTable;

    private const PORTAL_FEATURES = [
        'online_store' => 'Online Store',
        'pos' => 'Point of Sale',
        'restaurant_foodstore' => 'FoodStore',
        'sales_expenses' => 'Sales and expenses',
        'audit_log' => 'Audit log',
        'whatsapp_orders' => 'WhatsApp ordering',
    ];

    protected static string $view = 'filament.super-admin.pages.tenant-directory';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Subscribers';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $title = 'Subscribers';

    protected static ?int $navigationSort = 2;

    public function mount(): void
    {
        $this->tableSearch = trim((string) request()->query('tableSearch', ''));

        $feature = request()->query('feature');
        if (in_array($feature, ['pos', 'online_store', 'restaurant_foodstore'], true)) {
            $this->tableFilters = ['feature' => ['value' => $feature]];
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('addSubscriber')
                ->label('Add subscriber')
                ->icon('heroicon-o-user-plus')
                ->modalHeading('Enrol a subscriber')
                ->modalDescription('Create the workspace, assign a subscription, and set up its main administrator.')
                ->form([
                    TextInput::make('business_name')->label('Business name')->required()->maxLength(120),
                    TextInput::make('name')->label('Main administrator name')->required()->maxLength(120),
                    TextInput::make('username')
                        ->label('Administrator username suffix')
                        ->helperText('The generated subscriber code is added as a prefix, for example ES001-ama.')
                        ->alphaDash()
                        ->required()
                        ->maxLength(52),
                    TextInput::make('email')->label('Administrator email')->email()->nullable()->maxLength(255),
                    TextInput::make('password')->label('Administrator login password')->password()->revealable()->required()->minLength(8),
                    Select::make('plan_id')
                        ->label('Subscription plan')
                        ->options(fn (): array => Plan::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?int => Plan::query()->where('is_active', true)->orderBy('name')->value('id'))
                        ->live()
                        ->afterStateUpdated(function (Set $set, mixed $state): void {
                            $features = Plan::query()->where('is_active', true)->find($state)?->features ?? [];
                            $set('features', array_values(array_intersect(array_keys(self::PORTAL_FEATURES), $features)));
                        })
                        ->required(),
                    CheckboxList::make('features')
                        ->label('Portal access')
                        ->options(self::PORTAL_FEATURES)
                        ->default(function (): array {
                            $features = Plan::query()->where('is_active', true)->orderBy('name')->value('features') ?? [];

                            return array_values(array_intersect(array_keys(self::PORTAL_FEATURES), $features));
                        })
                        ->columns(2)
                        ->required(),
                ])
                ->action(fn (array $data) => app(SubscriberEnrollmentService::class)->create($data))
                ->successNotificationTitle('Subscriber created.'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Tenant::query()->with('subscriptions.plan'))
            ->columns([
                TextColumn::make('subscriber_code')->label('Code')->searchable()->sortable(),
                TextColumn::make('name')
                    ->label('Subscriber')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $tenantQuery): Builder => $tenantQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('data->name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('data->email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('data->phone', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('data->slug', 'like', "%{$search}%")
                        ->orWhere('subscriber_code', 'like', "%{$search}%")))
                    ->sortable(),
                TextColumn::make('email')->label('Email')->placeholder('Not provided'),
                TextColumn::make('phone')->label('Phone')->placeholder('Not provided'),
                TextColumn::make('subscription_plan')
                    ->label('Plan')
                    ->state(fn (Tenant $record): string => $record->subscriptions->sortByDesc('created_at')->first()?->plan?->name ?? 'No plan'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'warning'),
                TextColumn::make('subscription_status')
                    ->label('Subscription')
                    ->state(fn (Tenant $record): string => str($record->subscriptions->sortByDesc('created_at')->first()?->status ?? 'none')->replace('_', ' ')->title()->toString())
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'trial' => 'Trial',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'] ?? null;

                        if (! filled($status)) {
                            return $query;
                        }

                        return $query->where(fn (Builder $query): Builder => $query
                            ->where('status', $status)
                            ->orWhere('data->status', $status));
                    }),
                SelectFilter::make('feature')
                    ->label('Portal access')
                    ->options([
                        'pos' => 'Point of Sale',
                        'online_store' => 'Online Store',
                        'restaurant_foodstore' => 'FoodStore',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $feature = $data['value'] ?? null;

                        if (! filled($feature)) {
                            return $query;
                        }

                        return $query->whereHas('subscriptions', function (Builder $subscriptionQuery) use ($feature): void {
                            $subscriptionQuery->whereIn('status', ['trialing', 'active'])
                                ->where(function (Builder $accessQuery) use ($feature): void {
                                    $accessQuery->whereJsonContains('metadata->features', $feature)
                                        ->orWhere(function (Builder $legacyQuery) use ($feature): void {
                                            $legacyQuery->where(function (Builder $metadataQuery): void {
                                                $metadataQuery->whereNull('metadata')
                                                    ->orWhereRaw("JSON_EXTRACT(metadata, '$.features') IS NULL");
                                            })->whereHas('plan', fn (Builder $planQuery): Builder => $planQuery->whereJsonContains('features', $feature));
                                        });
                                });
                        });
                    }),
            ])
            ->actions([
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Tenant $record): string => 'Workspace details: '.$record->name)
                    ->modalContent(fn (Tenant $record) => view('filament.super-admin.pages.tenant-details-modal', [
                        'tenant' => $record,
                        'subscription' => $record->subscriptions->sortByDesc('created_at')->first(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->action(fn () => null),
                Action::make('manage')
                    ->label('Manage')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Tenant $record): string => TenantManagement::getUrl(
                        ['tenant' => $record->id],
                        panel: 'super-admin',
                    )),
                Action::make('quickEdit')
                    ->label('Quick edit')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading(fn (Tenant $record): string => 'Manage '.$record->name)
                    ->form([
                        TextInput::make('name')->required()->maxLength(120),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('phone')->tel()->maxLength(30),
                        TextInput::make('whatsapp_phone')
                            ->label('WhatsApp number')
                            ->tel()
                            ->maxLength(40)
                            ->rules([
                                'nullable',
                                'string',
                                'max:40',
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (! filled($value)) {
                                        return;
                                    }

                                    $digits = preg_replace('/\\D+/', '', (string) $value);
                                    if (! str_starts_with(trim((string) $value), '+') || strlen($digits) < 8 || strlen($digits) > 15) {
                                        $fail('Enter a WhatsApp number in international format, including its country code.');
                                    }
                                },
                            ]),
                        Select::make('status')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->required(),
                        Toggle::make('team_management_enabled')->label('Enable team management'),
                    ])
                    ->fillForm(fn (Tenant $record): array => [
                        'name' => $record->name,
                        'email' => $record->email,
                        'phone' => $record->phone,
                        'whatsapp_phone' => $record->whatsapp_phone,
                        'status' => $record->status,
                        'team_management_enabled' => $record->teamManagementEnabled(),
                    ])
                    ->action(fn (Tenant $record, array $data) => $record->update([
                        'name' => $data['name'],
                        'email' => filled($data['email'] ?? null) ? $data['email'] : null,
                        'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
                        'whatsapp_phone' => filled($data['whatsapp_phone'] ?? null) ? trim($data['whatsapp_phone']) : null,
                        'status' => $data['status'],
                        'team_management_enabled' => (bool) ($data['team_management_enabled'] ?? false),
                    ]))
                    ->successNotificationTitle('Workspace updated.'),
                Action::make('toggleStatus')
                    ->label(fn (Tenant $record): string => $record->status === 'suspended' ? 'Reactivate' : 'Suspend')
                    ->color(fn (Tenant $record): string => $record->status === 'suspended' ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Tenant $record): string => $record->status === 'suspended'
                        ? 'This will restore access for this workspace.'
                        : 'This will suspend access for this workspace.')
                    ->visible(fn (Tenant $record): bool => in_array($record->status, ['active', 'suspended'], true))
                    ->action(fn (Tenant $record) => $record->update([
                        'status' => $record->status === 'suspended' ? 'active' : 'suspended',
                    ]))
                    ->successNotificationTitle('Tenant status updated.'),
                Action::make('deleteSubscriber')
                    ->label('Delete subscriber')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Tenant $record): string => 'Delete '.$record->name.'?')
                    ->modalDescription('This permanently deletes the subscriber, workspace database, users, subscriptions, payments, and associated workspace records. This cannot be undone.')
                    ->action(function (Tenant $record): void {
                        $database = $record->database();
                        $manager = $database->manager();
                        $databaseName = $database->getName();

                        if ($databaseName !== null && $manager->databaseExists($databaseName) && ! $manager->deleteDatabase($record)) {
                            throw new \RuntimeException("Unable to delete workspace database [{$databaseName}].");
                        }

                        $record->delete();

                        if (session('workspace_tenant_id') === $record->id) {
                            session()->forget('workspace_tenant_id');
                        }
                    })
                    ->successNotificationTitle('Subscriber and workspace deleted.'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
