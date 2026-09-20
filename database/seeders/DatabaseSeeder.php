<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlanSeeder::class);
        $this->call(DemoWorkspaceSeeder::class);
        $this->call(SuperAdminSeeder::class);

        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['username' => config('admin.username')],
            [
                'name' => 'Demo Store Owner',
                'email' => 'admin@enablstore.test',
                'tenant_id' => Tenant::query()->value('id'),
                'password' => config('admin.password'),
            ],
        );
    }
}
