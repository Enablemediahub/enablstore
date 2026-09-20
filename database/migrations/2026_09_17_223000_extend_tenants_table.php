<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', static function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('email')->nullable()->after('slug');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('status', 30)->default('trial')->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', static function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug', 'email', 'phone', 'status']);
        });
    }
};
