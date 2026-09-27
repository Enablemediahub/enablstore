<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SuperAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SuperAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_with_email_when_username_is_missing(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Dale Quist',
            'email' => 'crepindale@gmail.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->post(route('super-admin.login.store'), [
            'username' => 'crepindale@gmail.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('super-admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'super_admin');
    }

    public function test_super_admin_tenant_search_finds_royal_in_json_tenant_data(): void
    {
        DB::table('tenants')->insert([
            'id' => 'royal',
            'subscriber_code' => 'ES002',
            'name' => null,
            'slug' => null,
            'email' => null,
            'status' => 'active',
            'data' => json_encode([
                'name' => 'Royal Supermarket',
                'slug' => 'royal',
                'email' => 'royal@example.test',
                'phone' => '+233201234567',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = SuperAdmin::query()->create([
            'name' => 'Test Super Admin',
            'email' => 'search-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.tenants.index', ['search' => 'royal']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SuperAdmin/Tenants')
                ->where('filters.search', 'royal')
                ->where('tenants.data.0.id', 'royal')
                ->where('tenants.data.0.name', 'Royal Supermarket')
                ->where('tenants.data.0.phone', '+233201234567'));
    }
}