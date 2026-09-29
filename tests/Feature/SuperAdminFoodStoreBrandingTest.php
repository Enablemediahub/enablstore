<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\SuperAdmin\Pages\Branding;
use App\Models\PlatformSetting;
use App\Models\SuperAdmin;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminFoodStoreBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_upload_a_shared_foodstore_hero_image_from_branding(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Branding Admin',
            'email' => 'foodstore-branding@example.test',
            'password' => Hash::make('correct-password'),
        ]);
        Storage::fake('public');

        $this->actingAs($admin, 'super_admin')
            ->post(route('super-admin.branding.foodstore-hero-image'), [
                'foodstore_hero_image' => UploadedFile::fake()->image('foodstore-hero.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'FoodStore hero image updated.');

        $path = PlatformSetting::value('foodstore_hero_image');
        $this->assertIsString($path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.dashboard'))
            ->assertRedirect(route('filament.super-admin.pages.dashboard'));

        $this->actingAs($admin, 'super_admin')
            ->get(route('filament.super-admin.pages.branding'))
            ->assertOk()
            ->assertSee('Branding and platform settings');
    }

    public function test_filament_branding_page_saves_shared_assets_and_storefront_defaults(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Filament Branding Admin',
            'email' => 'filament-branding@example.test',
            'password' => Hash::make('correct-password'),
        ]);
        Storage::fake('public');

        $this->actingAs($admin, 'super_admin')
            ->get('/super-admin/branding')
            ->assertOk()
            ->assertSee('Save branding and settings');
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));

        Livewire::test(Branding::class)
            ->set('data.foodstore_hero_image', UploadedFile::fake()->image('foodstore-hero.jpg'))
            ->set('data.tenant_display', [
                'enabled' => false,
                'placement' => 'both',
                'label_prefix' => 'Shop from',
            ])
            ->set('data.catalogue_mode_default', 'separate_online')
            ->call('save')
            ->assertHasNoErrors();

        $path = PlatformSetting::value('foodstore_hero_image');
        $this->assertIsString($path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame([
            'enabled' => false,
            'placement' => 'both',
            'label_prefix' => 'Shop from',
        ], PlatformSetting::storefrontTenantDisplaySettings());
        $this->assertSame('separate_online', PlatformSetting::value('catalogue_mode_default'));
    }
}
