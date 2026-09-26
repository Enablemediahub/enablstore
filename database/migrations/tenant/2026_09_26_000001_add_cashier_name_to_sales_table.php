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
            $table->string('cashier_name', 120)->nullable()->after('transaction_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('sales', static function (Blueprint $table): void {
            $table->dropColumn('cashier_name');
        });
    }
};