<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public function getConnectionName(): ?string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function loginWallpaperUrl(Request $request): ?string
    {
        $path = static::value('login_wallpaper');

        if (! is_string($path) || $path === '') {
            return null;
        }

        return $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/');
    }

    public static function storefrontLogoUrl(Request $request): string
    {
        $path = static::value('storefront_logo');

        if (is_string($path) && $path !== '') {
            return $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/');
        }

        return $request->getSchemeAndHttpHost().'/images/storefront/enablstore-logo.png';
    }

    /**
     * @return array{enabled: bool, placement: string, label_prefix: string}
     */
    public static function storefrontTenantDisplaySettings(): array
    {
        $stored = static::value('storefront_tenant_display');
        $defaults = static::defaultStorefrontTenantDisplay();

        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($stored)) {
            return $defaults;
        }

        $placement = (string) ($stored['placement'] ?? $defaults['placement']);

        if (! in_array($placement, ['header', 'hero', 'both'], true)) {
            $placement = $defaults['placement'];
        }

        return [
            'enabled' => array_key_exists('enabled', $stored)
                ? (bool) $stored['enabled']
                : $defaults['enabled'],
            'placement' => $placement,
            'label_prefix' => filled($stored['label_prefix'] ?? null)
                ? trim((string) $stored['label_prefix'])
                : $defaults['label_prefix'],
        ];
    }

    /**
     * @return array{enabled: bool, placement: string, labelPrefix: string}
     */
    public static function storefrontTenantDisplayForStorefront(): array
    {
        $settings = static::storefrontTenantDisplaySettings();

        return [
            'enabled' => $settings['enabled'],
            'placement' => $settings['placement'],
            'labelPrefix' => $settings['label_prefix'],
        ];
    }

    /**
     * @return array{enabled: bool, placement: string, label_prefix: string}
     */
    private static function defaultStorefrontTenantDisplay(): array
    {
        return [
            'enabled' => true,
            'placement' => 'header',
            'label_prefix' => 'Shopping at',
        ];
    }
}
