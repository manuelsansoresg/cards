<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'manuelsansoresg@gmail.com'],
            [
                'name' => 'manuel',
                'password' => Hash::make('demo123'),
                'role' => 'admin',
                'stars' => 0,
            ]
        );
    }
}