<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'jairo@jr.com'],
            [
                'name' => 'Jairo Rodrigues',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
