<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PosCashierUnlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_pin_unlock_opens_pos_by_default(): void
    {
        DB::table('tenants')->insert([
            'id' => 'pos-unlock-test',
            'subscriber_code' => 'ES902',
            'name' => 'POS Test',
            'slug' => 'pos-unlock-test',
            'email' => 'pos@example.test',
            'status' => 'active',
            'data' => json_encode(['subscriber_code' => 'ES902']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tenant = Tenant::query()->findOrFail('pos-unlock-test');
        $cashier = User::query()->create([
            'name' => 'POS Cashier',
            'username' => 'ES902-cashier',
            'email' => null,
            'tenant_id' => $tenant->id,
            'role' => 'cashier',
            'password' => Hash::make('unused-password'),
            'pos_pin_hash' => Hash::make('1357'),
        ]);

        $response = $this->post(route('tenant.pos.unlock', ['tenant' => $tenant->id]), [
            'username' => 'ES902-cashier',
            'pin' => '1357',
        ]);

        app(\Stancl\Tenancy\Tenancy::class)->end();

        $response->assertRedirect(route('tenant.pos', ['tenant' => $tenant->id], false));
        $this->assertSame($cashier->id, session('pos_cashier_id'));
    }

    public function test_cashier_pin_unlock_returns_to_foodstore_when_requested_from_workspace(): void
    {
        DB::table('tenants')->insert([
            'id' => 'foodstore-unlock-test',
            'subscriber_code' => 'ES901',
            'name' => 'FoodStore Test',
            'slug' => 'foodstore-unlock-test',
            'email' => 'foodstore@example.test',
            'status' => 'active',
            'data' => json_encode(['subscriber_code' => 'ES901']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tenant = Tenant::query()->findOrFail('foodstore-unlock-test');

        $cashier = User::query()->create([
            'name' => 'Test Cashier',
            'username' => 'ES901-cashier',
            'email' => null,
            'tenant_id' => $tenant->id,
            'role' => 'cashier',
            'password' => Hash::make('unused-password'),
            'pos_pin_hash' => Hash::make('2468'),
        ]);

        $this->get(route('tenant.foodstore.index', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Pos/Unlock')
                ->where('usernameHint', 'ES901-cashier'));

        $response = $this->withSession(['workspace_destination' => 'foodstore'])
            ->post(route('tenant.pos.unlock', ['tenant' => $tenant->id]), [
                'username' => 'ES901-cashier',
                'pin' => '2468',
            ]);

        app(\Stancl\Tenancy\Tenancy::class)->end();

        $response->assertRedirect(route('tenant.foodstore.index', ['tenant' => $tenant->id], false));

        $this->assertSame($cashier->id, session('pos_cashier_id'));
    }
}