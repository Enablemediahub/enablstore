<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', static function (Blueprint $table): void {
            $table->unsignedBigInteger('discount_minor')->default(0)->after('subtotal_minor');
            $table->string('discount_type', 20)->nullable()->after('discount_minor');
            $table->string('discount_reason')->nullable()->after('discount_type');
        });

        Schema::create('suppliers', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_movements', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->integer('quantity');
            $table->unsignedBigInteger('unit_cost_minor')->nullable();
                // $table->date('purchased_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', static function (Blueprint $table): void {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('action', 80);
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('suppliers');
        Schema::table('sales', static function (Blueprint $table): void {
            $table->dropColumn(['discount_minor', 'discount_type', 'discount_reason']);
        });
    }
};