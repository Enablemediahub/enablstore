<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->unsignedBigInteger('compare_at_price_minor')->nullable()->after('price_minor');
            $table->boolean('is_online_deal')->default(false)->after('available_online');
        });
    }

    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table): void {
            $table->dropColumn(['compare_at_price_minor', 'is_online_deal']);
        });
    }
};
