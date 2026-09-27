<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('billing_interval', 'quarterly')->update(['billing_interval_months' => 3]);
        DB::table('plans')->whereIn('billing_interval', ['semiannual', 'half-yearly'])->update(['billing_interval_months' => 6]);
        DB::table('plans')->whereIn('billing_interval', ['yearly', 'annual'])->update(['billing_interval_months' => 12]);
    }

    public function down(): void
    {
        DB::table('plans')->whereIn('billing_interval', ['quarterly', 'semiannual', 'half-yearly', 'yearly', 'annual'])
            ->update(['billing_interval_months' => 1]);
    }
};