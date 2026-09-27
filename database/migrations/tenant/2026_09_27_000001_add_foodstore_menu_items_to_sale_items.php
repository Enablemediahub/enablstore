<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', static function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('restaurant_menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', static function (Blueprint $table): void {
            $table->dropForeign(['restaurant_menu_item_id']);
            $table->dropColumn(['restaurant_menu_item_id', 'item_name']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
