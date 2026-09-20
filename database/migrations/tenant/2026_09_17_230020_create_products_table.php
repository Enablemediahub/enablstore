<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->unsignedBigInteger('price_minor');
            $table->unsignedBigInteger('cost_minor')->nullable();
            $table->string('currency', 3)->default('GHS');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
