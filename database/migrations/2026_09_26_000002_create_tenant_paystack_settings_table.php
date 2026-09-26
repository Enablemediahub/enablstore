<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_paystack_settings', static function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_paystack_settings');
    }
};