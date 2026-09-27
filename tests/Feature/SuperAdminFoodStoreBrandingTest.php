<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
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
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SuperAdmin/Dashboard')
                ->where('foodStoreHeroImageUrl', fn (mixed $url): bool => is_string($url) && str_ends_with($url, '/storage/'.$path)));
    }
}