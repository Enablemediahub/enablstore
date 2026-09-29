<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\SuperAdmin\Pages\AdminAccounts;
use App\Filament\SuperAdmin\Pages\TenantDirectory;
use App\Filament\SuperAdmin\Widgets\PlatformOverview;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_filament_panel_is_protected_by_its_guard(): void
    {
        $this->get('/super-admin-preview/login')
            ->assertRedirect('/super-admin/login');

        $this->get('/super-admin')
            ->assertRedirect(route('filament.super-admin.auth.login'));
        $this->get('/super-admin/login')
            ->assertOk()
            ->assertSee(asset('images/storefront/enablstore-logo.png'));

        $admin = SuperAdmin::query()->create([
            'name' => 'Panel Admin',
            'email' => 'preview-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'super_admin')
            ->get('/super-admin')
            ->assertOk()
            ->assertSee(asset('images/storefront/enablstore-logo.png'))
            ->assertSee('Branding and settings')
            ->assertSee('Subscription plans')
            ->assertSee('Financial overview')
            ->assertSee('Admin accounts');

        $this->get('/super-admin/tenant-directory')
            ->assertOk();

        $this->get(route('super-admin.dashboard'))
            ->assertRedirect(route('filament.super-admin.pages.dashboard'));
        $this->get(route('super-admin.users.index'))
            ->assertRedirect(route('filament.super-admin.pages.tenant-directory'));
        $this->get(route('super-admin.accounts.index'))
            ->assertRedirect(route('filament.super-admin.pages.admin-accounts'));

        Livewire::test(PlatformOverview::class)
            ->assertSee('Trialing subscriptions')
            ->assertSee('Pending payments')
            ->assertSee('Revenue this month');
    }

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
        ])->assertRedirect(route('filament.super-admin.pages.dashboard'));

        $this->assertAuthenticatedAs($admin, 'super_admin');
    }

    public function test_filament_admin_accounts_page_creates_and_updates_admins(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Account Page Admin',
            'email' => 'account-page-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'super_admin')
            ->get('/super-admin/admin-accounts')
            ->assertOk()
            ->assertSee('Create administrator');
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));

        Livewire::test(AdminAccounts::class)
            ->callAction('createAdmin', [
                'name' => 'New Platform Admin',
                'email' => 'new-platform-admin@example.test',
                'password' => 'correct-password',
            ]);

        $createdAdmin = SuperAdmin::query()->where('email', 'new-platform-admin@example.test')->firstOrFail();

        Livewire::test(AdminAccounts::class)
            ->callTableAction('editAdmin', $createdAdmin, [
                'name' => 'Updated Platform Admin',
                'email' => 'new-platform-admin@example.test',
                'password' => 'updated-password',
            ]);

        $createdAdmin->refresh();
        $this->assertSame('Updated Platform Admin', $createdAdmin->name);
        $this->assertTrue(Hash::check('updated-password', $createdAdmin->password));
    }

    public function test_filament_tenant_directory_filters_searches_and_toggles_tenant_status(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Directory Admin',
            'email' => 'directory-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);
        $this->actingAs($admin, 'super_admin');
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));

        $activeTenant = Tenant::query()->create([
            'id' => 'filament-active',
            'subscriber_code' => 'ES101',
            'name' => 'Active Workspace',
            'slug' => 'filament-active',
            'email' => 'active@example.test',
            'status' => 'active',
        ]);
        $suspendedTenant = Tenant::query()->create([
            'id' => 'filament-suspended',
            'subscriber_code' => 'ES102',
            'name' => 'Suspended Workspace',
            'slug' => 'filament-suspended',
            'email' => 'suspended@example.test',
            'status' => 'suspended',
        ]);
        $trialTenant = Tenant::query()->create([
            'id' => 'filament-trial',
            'subscriber_code' => 'ES103',
            'name' => 'Trial Workspace',
            'slug' => 'filament-trial',
            'email' => 'trial@example.test',
            'status' => 'trial',
        ]);

        Livewire::test(TenantDirectory::class)
            ->searchTable('Trial Workspace')
            ->assertSee('Trial Workspace')
            ->assertDontSee('Active Workspace')
            ->assertDontSee('Suspended Workspace');

        Livewire::test(TenantDirectory::class)
            ->assertTableActionExists('manage', fn ($action): bool => str_contains((string) $action->getUrl(), '/super-admin/tenant-management/'.$activeTenant->id), $activeTenant)
            ->callTableAction('quickEdit', $activeTenant, [
                'name' => 'Updated Active Workspace',
                'email' => 'active@example.test',
                'phone' => '+233201234567',
                'whatsapp_phone' => '+233201222222',
                'status' => 'active',
                'team_management_enabled' => true,
            ]);
        $this->assertSame('Updated Active Workspace', $activeTenant->fresh()->name);
        $this->assertSame('+233201222222', $activeTenant->fresh()->whatsapp_phone);
        $this->assertTrue($activeTenant->fresh()->teamManagementEnabled());

        Livewire::test(TenantDirectory::class)
            ->mountTableAction('details', $activeTenant)
            ->assertSee('active@example.test')
            ->assertSee('No plan');

        $filteredDirectory = Livewire::test(TenantDirectory::class)
            ->filterTable('status', 'suspended');

        $this->assertSame(
            [$suspendedTenant->id],
            $filteredDirectory->instance()->getFilteredTableQuery()->pluck('id')->all(),
        );

        $filteredDirectory
            ->assertSee('Suspended Workspace')
            ->assertDontSee('Active Workspace')
            ->assertDontSee('Trial Workspace');

        Livewire::test(TenantDirectory::class)
            ->callTableAction('toggleStatus', $activeTenant);
        $this->assertSame('suspended', $activeTenant->fresh()->status);

        Livewire::test(TenantDirectory::class)
            ->callTableAction('toggleStatus', $suspendedTenant);
        $this->assertSame('active', $suspendedTenant->fresh()->status);

        Livewire::test(TenantDirectory::class)
            ->assertTableActionHidden('toggleStatus', $trialTenant);
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
            ->assertRedirect(route('filament.super-admin.pages.tenant-directory', ['tableSearch' => 'royal']));

        $this->actingAs($admin, 'super_admin')
            ->get(route('filament.super-admin.pages.tenant-directory', ['tableSearch' => 'royal']))
            ->assertOk()
            ->assertSee('Royal Supermarket')
            ->assertSee('+233201234567');
    }
}
