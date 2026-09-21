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
}
