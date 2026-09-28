<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\RestaurantMenuItem;
use App\Models\RestaurantOrder;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ConsolidateTenantDatabases extends Command
{
    protected $signature = 'tenants:consolidate-shared {--tenant=* : Only import the specified tenant ID(s)}';

    protected $description = 'Copy existing tenant databases into the shared central database without deleting source databases';

    public function handle(): int
    {
        $centralName = (string) config('tenancy.database.central_connection');
        $central = DB::connection($centralName);
        if (! in_array($central->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('Tenant database consolidation currently requires MySQL or MariaDB.');

            return self::FAILURE;
        }

        $tenantIds = $this->option('tenant');
        $tenants = Tenant::query()
            ->when($tenantIds !== [], fn ($query) => $query->whereIn('id', $tenantIds))
            ->orderBy('id')
            ->get();
        if ($tenants->isEmpty()) {
            $this->warn('No matching tenants found.');

            return self::SUCCESS;
        }

        $baseConfig = config('database.connections.'.$centralName);
        $prefix = (string) config('tenancy.database.prefix', 'tenant');
        $suffix = (string) config('tenancy.database.suffix', '');

        foreach ($tenants as $tenant) {
            if ($tenant->shared_data_imported_at !== null) {
                $this->line("Skipping {$tenant->id}: already imported at {$tenant->shared_data_imported_at}.");
                continue;
            }

            $sourceDatabase = $prefix.$tenant->getTenantKey().$suffix;
            $databaseExists = $central->selectOne(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
                [$sourceDatabase],
            );
            if ($databaseExists === null) {
                $this->warn("Skipping {$tenant->id}: source database {$sourceDatabase} does not exist.");
                continue;
            }

            $sourceName = 'tenant_import_'.substr(sha1($tenant->getTenantKey()), 0, 10);
            Config::set('database.connections.'.$sourceName, array_replace($baseConfig, [
                'database' => $sourceDatabase,
                'url' => null,
            ]));
            DB::purge($sourceName);
            $source = DB::connection($sourceName);

            if ($source->table('users')->count() > 0) {
                $this->error("Tenant {$tenant->id} has tenant-local users. Import aborted to avoid losing or duplicating accounts.");
                DB::purge($sourceName);

                return self::FAILURE;
            }

            try {
                $counts = $central->transaction(function () use ($source, $central, $tenant): array {
                    $counts = $this->importTenant($source, $central, (string) $tenant->getTenantKey());
                    $central->table('tenants')->where('id', $tenant->getTenantKey())->update(['shared_data_imported_at' => now()]);

                    return $counts;
                });
                $this->info("Imported {$tenant->id}: ".collect($counts)->map(fn (int $count, string $table): string => "{$table}={$count}")->implode(', '));
            } catch (\Throwable $exception) {
                $this->error("Failed to import {$tenant->id}: {$exception->getMessage()}");
                DB::purge($sourceName);

                return self::FAILURE;
            }

            DB::purge($sourceName);
        }

        return self::SUCCESS;
    }

    /** @return array<string, int> */
    private function importTenant(Connection $source, Connection $central, string $tenantId): array
    {
        $counts = [];
        $categoryIds = $this->copyRows($source, $central, $tenantId, 'categories', $counts);
        $supplierIds = $this->copyRows($source, $central, $tenantId, 'suppliers', $counts);
        $menuItemIds = $this->copyRows($source, $central, $tenantId, 'restaurant_menu_items', $counts);
        $productIds = $this->copyRows($source, $central, $tenantId, 'products', $counts, static function (array $row) use ($categoryIds): array {
            $row['category_id'] = self::mappedNullableId($categoryIds, $row['category_id'] ?? null, 'products.category_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'inventory_stocks', $counts, static function (array $row) use ($productIds): array {
            $row['product_id'] = self::mappedId($productIds, $row['product_id'], 'inventory_stocks.product_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'tenant_settings', $counts);
        $saleIds = $this->copyRows($source, $central, $tenantId, 'sales', $counts);
        $orderIds = $this->copyRows($source, $central, $tenantId, 'restaurant_orders', $counts);
        $this->copyRows($source, $central, $tenantId, 'sale_items', $counts, static function (array $row) use ($saleIds, $productIds, $menuItemIds): array {
            $row['sale_id'] = self::mappedId($saleIds, $row['sale_id'], 'sale_items.sale_id');
            $row['product_id'] = self::mappedNullableId($productIds, $row['product_id'] ?? null, 'sale_items.product_id');
            $row['restaurant_menu_item_id'] = self::mappedNullableId($menuItemIds, $row['restaurant_menu_item_id'] ?? null, 'sale_items.restaurant_menu_item_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'sale_payments', $counts, static function (array $row) use ($saleIds): array {
            $row['sale_id'] = self::mappedId($saleIds, $row['sale_id'], 'sale_payments.sale_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'stock_movements', $counts, static function (array $row) use ($productIds, $supplierIds): array {
            $row['product_id'] = self::mappedId($productIds, $row['product_id'], 'stock_movements.product_id');
            $row['supplier_id'] = self::mappedNullableId($supplierIds, $row['supplier_id'] ?? null, 'stock_movements.supplier_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'audit_logs', $counts, static function (array $row) use ($productIds, $supplierIds, $saleIds, $menuItemIds, $orderIds): array {
            $idMap = match ($row['auditable_type'] ?? null) {
                Product::class => $productIds,
                Supplier::class => $supplierIds,
                Sale::class => $saleIds,
                RestaurantMenuItem::class => $menuItemIds,
                RestaurantOrder::class => $orderIds,
                default => [],
            };
            if (($row['auditable_id'] ?? null) !== null && $idMap !== []) {
                $row['auditable_id'] = (string) self::mappedId($idMap, $row['auditable_id'], 'audit_logs.auditable_id');
            }

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'restaurant_order_items', $counts, static function (array $row) use ($orderIds, $menuItemIds): array {
            $row['restaurant_order_id'] = self::mappedId($orderIds, $row['restaurant_order_id'], 'restaurant_order_items.restaurant_order_id');
            $row['restaurant_menu_item_id'] = self::mappedNullableId($menuItemIds, $row['restaurant_menu_item_id'] ?? null, 'restaurant_order_items.restaurant_menu_item_id');

            return $row;
        });
        $this->copyRows($source, $central, $tenantId, 'expenses', $counts);

        return $counts;
    }

    /** @param array<string, int> $counts
     *  @param (callable(array<string, mixed>): array<string, mixed>)|null $transform
     *  @return array<int|string, int>
     */
    private function copyRows(Connection $source, Connection $central, string $tenantId, string $table, array &$counts, ?callable $transform = null): array
    {
        if (! Schema::connection($source->getName())->hasTable($table)) {
            $counts[$table] = 0;

            return [];
        }

        $idMap = [];
        foreach ($source->table($table)->orderBy('id')->get() as $sourceRow) {
            $row = (array) $sourceRow;
            $oldId = $row['id'];
            unset($row['id']);
            if ($transform !== null) {
                $row = $transform($row);
            }
            $row['tenant_id'] = $tenantId;
            $idMap[$oldId] = (int) $central->table($table)->insertGetId($row);
        }
        $counts[$table] = count($idMap);

        return $idMap;
    }

    /** @param array<int|string, int> $map */
    private static function mappedId(array $map, int|string $id, string $field): int
    {
        if (! array_key_exists($id, $map)) {
            throw new RuntimeException("Could not remap {$field} value {$id}.");
        }

        return $map[$id];
    }

    /** @param array<int|string, int> $map */
    private static function mappedNullableId(array $map, int|string|null $id, string $field): ?int
    {
        return $id === null ? null : self::mappedId($map, $id, $field);
    }
}