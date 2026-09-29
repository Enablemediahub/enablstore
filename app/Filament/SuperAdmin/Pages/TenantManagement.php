<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantManagementService;
use App\Support\TenantPortalFeatures;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TenantManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.super-admin.pages.tenant-management';

    protected static ?string $slug = 'tenant-management/{tenant}';

    protected static bool $shouldRegisterNavigation = false;

    public Tenant $tenant;

    public ?array $data = [];

    /** @var array<string, mixed> */
    private const PORTAL_FEATURES = [
        'online_store' => 'Online Store',
        'pos' => 'Point of Sale',
        'restaurant_foodstore' => 'FoodStore POS',
        'foodstore_online' => 'FoodStore Online',
        'sales_expenses' => 'Sales and expenses',
        'audit_log' => 'Audit log',
        'whatsapp_orders' => 'WhatsApp ordering',
    ];

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        session()->put('workspace_tenant_id', $this->tenant->id);
        $service = app(TenantManagementService::class);
        $subscription = $this->tenant->subscriptions()->with('plan')->latest()->first();

        $this->form->fill([
            'name' => $this->tenant->name,
            'email' => $this->tenant->email,
            'phone' => $this->tenant->phone,
            'whatsapp_phone' => $this->tenant->whatsapp_phone,
            'status' => $this->tenant->status,
            'team_management_enabled' => (bool) $this->tenant->team_management_enabled,
            'subscription_plan_id' => $subscription?->plan_id,
            'subscription_status' => $subscription?->status,
            'subscription_amount_ghs' => $subscription === null ? null : number_format(($subscription->amount_minor ?? $subscription->plan?->price_minor ?? 0) / 100, 2, '.', ''),
            'features' => $subscription === null ? [] : array_values(array_intersect(array_keys(self::PORTAL_FEATURES), TenantPortalFeatures::forSubscription($subscription))),
            'storefront_logo' => $this->tenant->data['storefront_logo'] ?? null,
            ...$service->storefrontSettings($this->tenant),
            'paystack' => [
                ...$service->paystackSettings($this->tenant),
                'live_secret_key' => '',
                'test_secret_key' => '',
            ],
        ]);
    }

    public function getTitle(): string
    {
        return 'Manage '.$this->tenant->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('activateManually')
                ->label('Manually activate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Confirm that payment was received outside Paystack before activating this subscription.')
                ->visible(fn (): bool => $this->tenant->status === 'suspended' && $this->tenant->subscriptions()->exists())
                ->action(function (): void {
                    app(TenantManagementService::class)->activateManually($this->tenant);
                    $this->refreshPageData();
                    Notification::make()->title('Tenant manually activated and payment recorded.')->success()->send();
                }),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tenant account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Business name')->required()->maxLength(120),
                        TextInput::make('email')->label('Contact email')->email()->nullable()->maxLength(255),
                        TextInput::make('phone')->label('Contact phone')->tel()->nullable()->maxLength(30),
                        TextInput::make('whatsapp_phone')->label('WhatsApp number')->tel()->nullable()->maxLength(40),
                        Select::make('status')->options(['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended'])->required(),
                        Toggle::make('team_management_enabled')->label('Allow tenant admin to manage team accounts'),
                    ]),
                Section::make('Subscription and portal access')
                    ->columns(2)
                    ->schema([
                        Select::make('subscription_plan_id')
                            ->label('Plan')
                            ->options(fn (): array => Plan::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->nullable(),
                        Select::make('subscription_status')
                            ->label('Subscription status')
                            ->options([
                                'trialing' => 'Trialing',
                                'active' => 'Active',
                                'past_due' => 'Past due',
                                'disabled' => 'Disabled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->nullable(),
                        TextInput::make('subscription_amount_ghs')
                            ->label('Subscription amount (GHS)')
                            ->numeric()
                            ->minValue(0.01)
                            ->nullable(),
                        CheckboxList::make('features')
                            ->label('Portal access')
                            ->options(self::PORTAL_FEATURES)
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Online Store settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('storefront_store_name')->label('Store display name')->maxLength(80),
                        Select::make('catalogue_mode')->options([
                            'shared' => 'Shared POS products',
                            'separate_online' => 'Separate online products',
                        ])->required(),
                        TextInput::make('storefront_delivery_message')->label('Navigation delivery message')->maxLength(160),
                        TextInput::make('storefront_hero_delivery_message')->label('Hero delivery message')->maxLength(160),
                        TextInput::make('storefront_customer_service_phone')->label('Customer service phone')->maxLength(40),
                        TextInput::make('storefront_customer_service_email')->label('Customer service email')->email()->maxLength(120),
                        FileUpload::make('storefront_logo')
                            ->label('Permanent storefront logo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->disk('public')
                            ->directory('tenants/'.$this->tenant->getTenantKey().'/branding')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imagePreviewHeight('120px')
                            ->visible(fn (): bool => blank($this->tenant->data['storefront_logo'] ?? null))
                            ->columnSpanFull(),
                    ]),
                Section::make('Storefront Paystack settings')
                    ->description('Secret keys are encrypted at rest and are never shown after saving.')
                    ->columns(2)
                    ->schema([
                        Select::make('paystack.mode')->label('Mode')->options(['test' => 'Test', 'live' => 'Live'])->required(),
                        Toggle::make('paystack.enabled')->label('Enable Paystack payments'),
                        TextInput::make('paystack.test_public_key')->label('Test public key')->placeholder('pk_test_')->maxLength(255),
                        TextInput::make('paystack.test_secret_key')->label('Test secret key')->password()->revealable()->placeholder('Leave blank to keep saved key')->maxLength(255),
                        TextInput::make('paystack.live_public_key')->label('Live public key')->placeholder('pk_live_')->maxLength(255),
                        TextInput::make('paystack.live_secret_key')->label('Live secret key')->password()->revealable()->placeholder('Leave blank to keep saved key')->maxLength(255),
                        Toggle::make('paystack.test_configured')->label('Test keys configured')->disabled()->dehydrated(false),
                        Toggle::make('paystack.live_configured')->label('Live keys configured')->disabled()->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $tenantData = array_diff_key($data, ['paystack' => true]);
        $validated = Validator::make($tenantData, TenantManagementService::updateRules())->validate();
        $service = app(TenantManagementService::class);

        $paystack = Validator::make($data['paystack'] ?? [], TenantManagementService::paystackRules())->validate();
        $service->updatePaystackSettings($this->tenant, $paystack);
        $service->update($this->tenant, $validated);
        session()->put('workspace_tenant_id', $this->tenant->id);
        $this->refreshPageData();

        Notification::make()->title('Tenant settings updated.')->success()->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->where('tenant_id', $this->tenant->id))
            ->columns([
                TextColumn::make('name')->label('Team member')->searchable()->sortable(),
                TextColumn::make('username')->fontFamily('mono'),
                TextColumn::make('role')->badge()->formatStateUsing(fn (string $state): string => str($state)->title()->toString()),
                TextColumn::make('email')->placeholder('No email'),
                TextColumn::make('created_at')->label('Added')->date()->placeholder('Unknown'),
            ])
            ->actions([
                TableAction::make('editMember')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (User $record): array => [
                        'name' => $record->name,
                        'username' => $record->username,
                        'email' => $record->email,
                        'role' => $record->role,
                    ])
                    ->form([
                        TextInput::make('name')->required()->maxLength(120),
                        TextInput::make('username')->required()->alphaDash()->maxLength(60)->unique('users', 'username', ignoreRecord: true),
                        TextInput::make('email')->email()->nullable()->maxLength(255)->unique('users', 'email', ignoreRecord: true),
                        Select::make('role')->options(['admin' => 'Administrator', 'cashier' => 'Cashier'])->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $prefix = strtolower((string) $this->tenant->subscriber_code).'-';
                        $username = strtolower(trim($data['username']));
                        if ($prefix !== '-' && ! str_starts_with($username, $prefix)) {
                            throw ValidationException::withMessages(['username' => "This username must begin with {$this->tenant->subscriber_code}-."]);
                        }
                        if ($record->role === 'admin' && $data['role'] !== 'admin' && User::query()->where('tenant_id', $this->tenant->id)->where('role', 'admin')->count() <= 1) {
                            throw ValidationException::withMessages(['role' => 'A subscriber must keep at least one administrator.']);
                        }

                        $record->update([
                            'name' => $data['name'],
                            'username' => $username,
                            'email' => filled($data['email'] ?? null) ? $data['email'] : null,
                            'role' => $data['role'],
                        ]);
                    })
                    ->successNotificationTitle('Team member updated.'),
                TableAction::make('resetAccess')
                    ->label('Reset access')
                    ->icon('heroicon-o-key')
                    ->form(fn (User $record): array => [
                        TextInput::make($record->role === 'cashier' ? 'pin' : 'password')
                            ->label($record->role === 'cashier' ? 'New POS PIN' : 'New login password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength($record->role === 'cashier' ? 4 : 8)
                            ->maxLength($record->role === 'cashier' ? 6 : 255),
                    ])
                    ->action(function (User $record, array $data): void {
                        if ($record->role === 'cashier') {
                            Validator::make($data, ['pin' => ['required', 'digits_between:4,6']])->validate();
                            $record->update(['pos_pin_hash' => Hash::make($data['pin'])]);
                        } else {
                            $record->update(['password' => $data['password']]);
                        }
                    })
                    ->successNotificationTitle('Team member access reset.'),
                TableAction::make('deleteMember')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->role !== 'admin' || User::query()->where('tenant_id', $this->tenant->id)->where('role', 'admin')->count() > 1)
                    ->action(fn (User $record) => $record->delete())
                    ->successNotificationTitle('Team member deleted.'),
            ])
            ->defaultSort('created_at');
    }

    private function refreshPageData(): void
    {
        $tenantId = $this->tenant->id;
        $this->tenant = Tenant::query()->findOrFail($tenantId);
        $this->mount($this->tenant);
    }
}
