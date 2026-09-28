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
            $table->json('option_groups')->nullable();
        });

        Schema::table('restaurant_order_items', static function (Blueprint $table): void {
            $table->json('selected_options')->nullable();
        });

        Schema::table('sale_items', static function (Blueprint $table): void {
            $table->json('selected_options')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', static function (Blueprint $table): void {
            $table->dropColumn('selected_options');
        });

        Schema::table('restaurant_order_items', static function (Blueprint $table): void {
            $table->dropColumn('selected_options');
        });

        Schema::table('restaurant_menu_items', static function (Blueprint $table): void {
            $table->dropColumn('option_groups');
        });
    }
};