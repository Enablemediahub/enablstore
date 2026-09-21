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
            $table->string('customer_name', 120)->nullable()->after('transaction_uuid');
            $table->string('customer_phone', 40)->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('sales', static function (Blueprint $table): void {
            $table->dropColumn(['customer_name', 'customer_phone']);
        });
    }
};
