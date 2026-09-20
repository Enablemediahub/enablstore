<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('provider', 30)->default('paystack');
            $table->string('provider_reference')->nullable()->unique();
            $table->string('status', 30)->default('trialing');
            $table->timestamp('starts_at');
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
