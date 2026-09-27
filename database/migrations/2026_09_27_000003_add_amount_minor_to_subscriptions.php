<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('amount_minor')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', static function (Blueprint $table): void {
            $table->dropColumn('amount_minor');
        });
    }
};
