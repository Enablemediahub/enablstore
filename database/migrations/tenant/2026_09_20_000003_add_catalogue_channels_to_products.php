<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->boolean('available_in_pos')->default(true)->after('is_active');
            $table->boolean('available_online')->default(true)->after('available_in_pos');
            $table->json('image_gallery')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->dropColumn(['available_in_pos', 'available_online', 'image_gallery']);
        });
    }
};