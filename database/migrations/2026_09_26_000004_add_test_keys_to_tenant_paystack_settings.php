<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_paystack_settings', static function (Blueprint $table): void {
            $table->string('test_public_key')->nullable()->after('secret_key');
            $table->text('test_secret_key')->nullable()->after('test_public_key');
            $table->string('mode', 10)->default('live')->after('test_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_paystack_settings', static function (Blueprint $table): void {
            $table->dropColumn(['test_public_key', 'test_secret_key', 'mode']);
        });
    }
};