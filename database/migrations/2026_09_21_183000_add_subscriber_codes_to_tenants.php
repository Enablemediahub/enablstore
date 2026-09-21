<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('subscriber_code', 20)->nullable()->unique()->after('id');
        });

        Tenant::query()->orderBy('created_at')->orderBy('id')->get()->each(function (Tenant $tenant, int $index): void {
            $code = 'ES'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $tenant->update(['subscriber_code' => $code]);

            User::query()->where('tenant_id', $tenant->id)->get()->each(function (User $user) use ($code): void {
                if (! str_starts_with(strtoupper($user->username), $code.'-')) {
                    $user->update(['username' => $code.'-'.strtolower($user->username)]);
                }
            });
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropUnique(['subscriber_code']);
            $table->dropColumn('subscriber_code');
        });
    }
};
