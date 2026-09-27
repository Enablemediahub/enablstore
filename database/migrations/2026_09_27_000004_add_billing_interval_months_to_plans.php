<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', static function (Blueprint $table): void {
            $table->unsignedSmallInteger('billing_interval_months')->default(1)->after('billing_interval');
        });
    }

    public function down(): void
    {
        Schema::table('plans', static function (Blueprint $table): void {
            $table->dropColumn('billing_interval_months');
        });
    }
};