<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')->select(['id', 'subscriber_code', 'data'])->orderBy('id')->get()->each(static function (object $tenant): void {
            $data = is_array($tenant->data) ? $tenant->data : json_decode((string) ($tenant->data ?? '{}'), true);
            $data = is_array($data) ? $data : [];
            $subscriberCode = $tenant->subscriber_code ?: ($data['subscriber_code'] ?? null);

            if (! is_string($subscriberCode) || $subscriberCode === '') {
                return;
            }

            unset($data['subscriber_code']);
            DB::table('tenants')->where('id', $tenant->id)->update([
                'subscriber_code' => $subscriberCode,
                'data' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('tenants')->select(['id', 'subscriber_code', 'data'])->orderBy('id')->get()->each(static function (object $tenant): void {
            if (! is_string($tenant->subscriber_code) || $tenant->subscriber_code === '') {
                return;
            }

            $data = is_array($tenant->data) ? $tenant->data : json_decode((string) ($tenant->data ?? '{}'), true);
            $data = is_array($data) ? $data : [];
            $data['subscriber_code'] = $tenant->subscriber_code;
            DB::table('tenants')->where('id', $tenant->id)->update([
                'data' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
        });
    }
};