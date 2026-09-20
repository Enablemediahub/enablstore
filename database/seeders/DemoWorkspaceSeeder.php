<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->find('demo');

        if ($tenant === null) {
            $tenant = Tenant::withoutEvents(function (): Tenant {
                $model = new Tenant([
                    'id' => 'demo',
                    'name' => 'Demo Market',
                    'slug' => 'demo',
                    'email' => 'demo@enablstore.test',
                    'status' => 'active',
                ]);
                $model->id = 'demo';
                $model->save();

                return $model;
            });
        } else {
            $tenant->update([
                'name' => 'Demo Market',
                'slug' => 'demo',
                'email' => 'demo@enablstore.test',
                'status' => 'active',
            ]);
        }
        $plan = Plan::query()->where('slug', 'starter')->firstOrFail();
        $tenant->subscriptions()->updateOrCreate(
            ['plan_id' => $plan->id],
            [
                'provider' => 'internal',
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => now()->addMonth(),
            ],
        );
    }
}
