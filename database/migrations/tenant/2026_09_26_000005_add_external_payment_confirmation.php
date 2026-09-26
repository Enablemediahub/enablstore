<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_payments', 'externally_confirmed')) {
            Schema::table('sale_payments', static function (Blueprint $table): void {
                $table->boolean('externally_confirmed')->default(false)->after('cash_received_minor');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_payments', 'externally_confirmed')) {
            Schema::table('sale_payments', static function (Blueprint $table): void {
                $table->dropColumn('externally_confirmed');
            });
        }
    }
};