<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@springglossy.com.ng',
            'password' => Hash::make('Nigeria@2025'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
