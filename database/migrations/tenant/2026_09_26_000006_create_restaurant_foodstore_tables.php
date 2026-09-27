<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_menu_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('category', 60)->default('Mains');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_minor');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index(['is_available', 'category']);
        });

        Schema::create('restaurant_orders', static function (Blueprint $table): void {
            $table->id();
            $table->string('table_label', 40)->nullable();
            $table->string('customer_name', 120)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedBigInteger('total_minor');
            $table->string('created_by_name', 120)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('restaurant_order_items', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_menu_item_id')->nullable()->constrained('restaurant_menu_items')->nullOnDelete();
            $table->string('item_name', 120);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_items');
        Schema::dropIfExists('restaurant_orders');
        Schema::dropIfExists('restaurant_menu_items');
    }
};