<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', static function (Blueprint $table): void {
            $table->date('purchased_at')->nullable()->after('unit_cost_minor');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', static function (Blueprint $table): void {
            $table->dropColumn('purchased_at');
        });
    }
};