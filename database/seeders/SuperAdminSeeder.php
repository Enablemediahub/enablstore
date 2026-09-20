<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('super-admin.email');
        $password = config('super-admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            return;
        }

        SuperAdmin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('super-admin.name'),
                'username' => config('super-admin.username'),
                'password' => Hash::make($password),
            ],
        );
    }
}
