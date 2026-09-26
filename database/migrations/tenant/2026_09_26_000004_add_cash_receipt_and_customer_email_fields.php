<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_payments', 'cash_received_minor')) {
            Schema::table('sale_payments', static function (Blueprint $table): void {
                $table->unsignedBigInteger('cash_received_minor')->nullable()->after('amount_minor');
            });
        }

        if (! Schema::hasColumn('sales', 'customer_email')) {
            Schema::table('sales', static function (Blueprint $table): void {
                $table->string('customer_email', 255)->nullable()->after('customer_phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'customer_email')) {
            Schema::table('sales', static function (Blueprint $table): void {
                $table->dropColumn('customer_email');
            });
        }

        if (Schema::hasColumn('sale_payments', 'cash_received_minor')) {
            Schema::table('sale_payments', static function (Blueprint $table): void {
                $table->dropColumn('cash_received_minor');
            });
        }
    }
};