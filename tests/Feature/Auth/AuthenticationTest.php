<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_open_the_admin_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_tenant_admin_login_opens_the_tenant_dashboard(): void
    {
        DB::table('tenants')->insert([
            'id' => 'royal',
            'subscriber_code' => 'ES003',
            'name' => 'Royal Supermarket',
            'slug' => 'royal',
            'email' => 'royal@example.test',
            'status' => 'active',
            'data' => json_encode(['name' => 'Royal Supermarket', 'slug' => 'royal']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->create([
            'tenant_id' => 'royal',
            'role' => 'admin',
        ]);

        $this->withSession(['url.intended' => '/dashboard/royal'])
            ->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('subscriberCode', 'ES003'));

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard/royal');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
