<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username'          => 'admin',
                'password'          => 'Admin123!',
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Utilisateur test
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'username'          => 'user',
                'password'          => 'User123!',
                'role'              => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
