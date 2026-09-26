<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_intents', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('reference')->unique();
            $table->string('context', 30);
            $table->unsignedBigInteger('amount_minor');
            $table->json('payload');
            $table->string('status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'context', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_intents');
    }
};