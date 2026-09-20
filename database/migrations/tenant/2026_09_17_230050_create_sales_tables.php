<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('transaction_uuid')->unique();
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('total_minor');
            $table->string('currency', 3)->default('GHS');
            $table->string('payment_method', 30);
            $table->string('status', 30)->default('completed');
            $table->timestamp('completed_at');
            $table->timestamps();
        });

        Schema::create('sale_items', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
