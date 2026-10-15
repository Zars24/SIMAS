<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('simas.primary_admin_email')],
            [
                'name' => 'Administrator SIMAS',
                'username' => 'admin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );
    }
}
