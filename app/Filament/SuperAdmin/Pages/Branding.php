<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\PlatformSetting;
use App\Support\PublicAssetPublisher;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Branding extends Page
{
    protected static string $view = 'filament.super-admin.pages.branding';

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Branding and settings';

    protected static ?string $navigationGroup = 'Platform tools';

    protected static ?string $title = 'Branding and platform settings';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'storefront_logo' => PlatformSetting::value('storefront_logo'),
            'foodstore_hero_image' => PlatformSetting::value('foodstore_hero_image'),
            'pos_hero_image' => PlatformSetting::value('pos_hero_image'),
            'dashboard_wallpaper' => PlatformSetting::value('dashboard_wallpaper'),
            'login_wallpaper' => PlatformSetting::value('login_wallpaper'),
            'tenant_display' => PlatformSetting::storefrontTenantDisplaySettings(),
            'catalogue_mode_default' => PlatformSetting::value('catalogue_mode_default', 'shared'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Storefront identity')
                    ->description('Control the shared storefront brand and how each subscribed tenant is identified.')
                    ->columns(2)
                    ->schema([
                        $this->imageUpload('storefront_logo', 'Storefront logo', true),
                        Toggle::make('tenant_display.enabled')->label('Show tenant name on storefront'),
                        Select::make('tenant_display.placement')
                            ->label('Tenant name placement')
                            ->options([
                                'header' => 'Header',
                                'hero' => 'Hero banner',
                                'both' => 'Header and hero',
                            ])
                            ->required(),
                        TextInput::make('tenant_display.label_prefix')
                            ->label('Tenant label prefix')
                            ->maxLength(40),
                    ]),
                Section::make('Shared imagery')
                    ->description('Images are shared across tenant workspaces. Upload JPG, PNG, or WebP files up to 5 MB.')
                    ->columns(2)
                    ->schema([
                        $this->imageUpload('foodstore_hero_image', 'FoodStore hero image'),
                        $this->imageUpload('pos_hero_image', 'POS hero image'),
                        $this->imageUpload('dashboard_wallpaper', 'Workspace dashboard wallpaper'),
                        $this->imageUpload('login_wallpaper', 'Login wallpaper'),
                    ]),
                Section::make('Catalogue defaults')
                    ->description('This applies to tenants that have not selected their own catalogue mode.')
                    ->schema([
                        Radio::make('catalogue_mode_default')
                            ->label('Default Online Store mode')
                            ->options([
                                'shared' => 'Shared products',
                                'separate_online' => 'Separate Online Store products',
                            ])
                            ->inline()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ([
            'storefront_logo' => 'storefront_logo',
            'foodstore_hero_image' => 'foodstore_hero_image',
            'pos_hero_image' => 'pos_hero_image',
            'dashboard_wallpaper' => 'dashboard_wallpaper',
            'login_wallpaper' => 'login_wallpaper',
        ] as $field => $key) {
            $this->saveAsset($data[$field] ?? null, $key);
        }

        $tenantDisplay = $data['tenant_display'] ?? [];
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'storefront_tenant_display'],
            ['value' => json_encode([
                'enabled' => (bool) ($tenantDisplay['enabled'] ?? false),
                'placement' => $tenantDisplay['placement'] ?? 'header',
                'label_prefix' => filled($tenantDisplay['label_prefix'] ?? null)
                    ? trim($tenantDisplay['label_prefix'])
                    : 'Shopping at',
            ], JSON_THROW_ON_ERROR)],
        );
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'catalogue_mode_default'],
            ['value' => $data['catalogue_mode_default']],
        );

        Notification::make()
            ->title('Branding and platform settings saved.')
            ->success()
            ->send();
    }

    private function imageUpload(string $name, string $label, bool $allowSvg = false): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes($allowSvg
                ? ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']
                : ['image/jpeg', 'image/png', 'image/webp'])
            ->disk('public')
            ->directory('platform')
            ->visibility('public')
            ->maxSize(5120)
            ->imagePreviewHeight('140px')
            ->nullable();
    }

    private function saveAsset(mixed $newPath, string $key): void
    {
        $previousPath = PlatformSetting::value($key);

        if ($newPath === $previousPath) {
            return;
        }

        if (filled($newPath)) {
            PublicAssetPublisher::publish((string) $newPath);
            PlatformSetting::query()->updateOrCreate(['key' => $key], ['value' => $newPath]);
        } else {
            PlatformSetting::query()->where('key', $key)->delete();
        }

        if (is_string($previousPath) && $previousPath !== '') {
            PublicAssetPublisher::delete($previousPath);
        }
    }
}
