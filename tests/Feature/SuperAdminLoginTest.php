<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}