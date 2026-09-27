<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_menu_items', static function (Blueprint $table): void {
            $table->string('unit_label', 32)->default('plate');
            $table->string('image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_menu_items', static function (Blueprint $table): void {
            $table->dropColumn(['unit_label', 'image_path']);
        });
    }
};
