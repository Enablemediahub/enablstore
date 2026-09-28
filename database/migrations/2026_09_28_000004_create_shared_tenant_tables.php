<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', static function (Blueprint $table): void {
            $table->timestamp('shared_data_imported_at')->nullable();
        });

        Schema::create('categories', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'slug']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('suppliers', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('restaurant_menu_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name', 120);
            $table->string('category', 60)->default('Mains');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_minor');
            $table->string('unit_label', 32)->default('plate');
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->json('option_groups')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'is_available', 'category']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('price_minor');
            $table->unsignedBigInteger('compare_at_price_minor')->nullable();
            $table->unsignedBigInteger('cost_minor')->nullable();
            $table->string('purchase_unit', 40)->default('unit');
            $table->unsignedInteger('units_per_purchase')->default(1);
            $table->string('currency', 3)->default('GHS');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('image_gallery')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('available_in_pos')->default(true);
            $table->boolean('available_online')->default(true);
            $table->boolean('is_online_deal')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
            $table->index(['tenant_id', 'is_active', 'available_online']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('inventory_stocks', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'product_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('tenant_settings', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'key']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('sales', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->uuid('transaction_uuid');
            $table->string('cashier_name', 120)->nullable();
            $table->string('customer_name', 120)->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->string('discount_type', 20)->nullable();
            $table->string('discount_reason')->nullable();
            $table->unsignedBigInteger('total_minor');
            $table->string('currency', 3)->default('GHS');
            $table->string('payment_method', 30);
            $table->string('source', 30)->default('pos');
            $table->string('delivery_location', 120)->nullable();
            $table->string('status', 30)->default('completed');
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'transaction_uuid']);
            $table->index(['tenant_id', 'status', 'completed_at']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('sale_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('restaurant_menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name', 120)->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->json('selected_options')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('sale_payments', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30);
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedBigInteger('cash_received_minor')->nullable();
            $table->boolean('externally_confirmed')->default(false);
            $table->string('provider_reference')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'provider_reference']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('stock_movements', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->integer('quantity');
            $table->unsignedBigInteger('unit_cost_minor')->nullable();
            $table->date('purchased_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('audit_logs', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('user_id')->nullable();
            $table->string('action', 80);
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'auditable_type', 'auditable_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('restaurant_orders', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('table_label', 40)->nullable();
            $table->string('customer_name', 120)->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedBigInteger('total_minor');
            $table->string('created_by_name', 120)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('restaurant_order_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('restaurant_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name', 120);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->json('selected_options')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('expenses', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('category', 80);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('GHS');
            $table->string('payment_method', 30);
            $table->timestamp('spent_at');
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'spent_at']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('restaurant_order_items');
        Schema::dropIfExists('restaurant_orders');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('restaurant_menu_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
        Schema::table('tenants', static function (Blueprint $table): void {
            $table->dropColumn('shared_data_imported_at');
        });
    }
};