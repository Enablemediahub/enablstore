<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class PublicAssetPublisher
{
    public static function publish(string $path): void
    {
        $path = self::normalizePath($path);
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw new RuntimeException("Uploaded public asset [{$path}] was not found on the public disk.");
        }

        $source = $disk->path($path);
        $destination = public_path('storage/'.str_replace('/', DIRECTORY_SEPARATOR, $path));
        File::ensureDirectoryExists(dirname($destination));

        if (is_file($destination)) {
            return;
        }

        if (! File::copy($source, $destination)) {
            throw new RuntimeException("Unable to publish public asset [{$path}] to the web root.");
        }
    }

    public static function delete(string $path): void
    {
        $path = self::normalizePath($path);
        Storage::disk('public')->delete($path);
        File::delete(public_path('storage/'.str_replace('/', DIRECTORY_SEPARATOR, $path)));
    }

    private static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));

        if ($path === '' || in_array('..', explode('/', $path), true)) {
            throw new InvalidArgumentException('A valid relative public asset path is required.');
        }

        return $path;
    }
}
