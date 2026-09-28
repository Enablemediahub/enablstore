<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', static function (Blueprint $table): void {
            $table->id();
            $table->string('category', 80);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('GHS');
            $table->string('payment_method', 30);
            $table->timestamp('spent_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};