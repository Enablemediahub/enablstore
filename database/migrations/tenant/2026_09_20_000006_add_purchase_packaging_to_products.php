<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('purchase_unit', 40)->default('unit')->after('cost_minor');
            $table->unsignedInteger('units_per_purchase')->default(1)->after('purchase_unit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['purchase_unit', 'units_per_purchase']);
        });
    }
};