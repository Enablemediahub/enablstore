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
            $table->string('source', 30)->default('pos')->after('payment_method');
            $table->string('delivery_location', 120)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('sales', static function (Blueprint $table): void {
            $table->dropColumn(['source', 'delivery_location']);
        });
    }
};