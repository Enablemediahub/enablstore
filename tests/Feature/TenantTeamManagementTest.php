<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class TenantTeamManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<Tenant> */
    private array $createdTenants = [];

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        foreach ($this->createdTenants as $tenant) {
            $tenant->delete();
        }

        parent::tearDown();
    }

    public function test_tenant_admin_can_create_edit_reset_and_delete_team_accounts(): void
    {
        [$tenant, $admin] = $this->createWorkspace(true, 'ES920');

        $this->actingAs($admin)->post(route('tenant.team.store', ['tenant' => $tenant->id]), [
            'name' => 'Front Counter',
            'username' => 'counter',
            'email' => 'counter@example.test',
            'role' => 'cashier',
            'pos_pin' => '1234',
        ])->assertRedirect()->assertSessionHas('status', 'Team member created successfully.');

        $cashier = User::query()->where('tenant_id', $tenant->id)->where('role', 'cashier')->firstOrFail();
        $this->assertSame('ES920-counter', $cashier->username);
        $this->assertTrue(Hash::check('1234', $cashier->pos_pin_hash));

        $this->actingAs($admin)->patch(route('tenant.team.update', ['tenant' => $tenant->id, 'user' => $cashier->id]), [
            'name' => 'Front Desk',
            'username' => 'ES920-frontdesk',
            'email' => 'frontdesk@example.test',
            'role' => 'cashier',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $cashier->refresh();
        $this->assertSame('Front Desk', $cashier->name);
        $this->assertSame('ES920-frontdesk', $cashier->username);

        $this->actingAs($admin)->patch(route('tenant.team.reset-access', ['tenant' => $tenant->id, 'user' => $cashier->id]), [
            'pin' => '5678',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('5678', $cashier->fresh()->pos_pin_hash));

        $this->actingAs($admin)->delete(route('tenant.team.destroy', ['tenant' => $tenant->id, 'user' => $cashier->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $cashier->id]);
    }

    public function test_disabling_team_management_blocks_direct_writes_but_keeps_the_roster_readable(): void
    {
        [$tenant, $admin] = $this->createWorkspace(false, 'ES921');

        $this->actingAs($admin)->get(route('tenant.team.index', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Team/Index')
                ->where('teamManagementEnabled', false));

        $this->actingAs($admin)->post(route('tenant.team.store', ['tenant' => $tenant->id]), [
            'name' => 'Blocked Cashier',
            'username' => 'blocked',
            'role' => 'cashier',
            'pos_pin' => '1234',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['tenant_id' => $tenant->id, 'username' => 'ES921-blocked']);
    }

    /** @return array{Tenant, User} */
    private function createWorkspace(bool $teamManagementEnabled, string $subscriberCode): array
    {
        $tenantId = 'team-'.Str::lower(Str::random(8));
        $tenant = Tenant::query()->create([
            'id' => $tenantId,
            'subscriber_code' => $subscriberCode,
            'name' => 'Team Test Store',
            'slug' => $tenantId,
            'email' => $tenantId.'@example.test',
            'status' => 'active',
            'team_management_enabled' => $teamManagementEnabled,
            'data' => [],
        ]);
        $this->createdTenants[] = $tenant;
        $admin = User::factory()->create([
            'username' => strtolower($subscriberCode).'-owner',
            'tenant_id' => $tenantId,
            'role' => 'admin',
        ]);

        return [$tenant, $admin];
    }
}