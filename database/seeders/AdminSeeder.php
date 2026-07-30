<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_SEED_USERNAME');
        $password = env('ADMIN_SEED_PASSWORD');

        if (blank($username) || blank($password)) {
            throw new \RuntimeException(
                'ADMIN_SEED_USERNAME and ADMIN_SEED_PASSWORD must be set before running AdminSeeder.'
            );
        }

        Admin::firstOrCreate(
            ['username' => $username],
            [
                'password' => Hash::make($password),
                'full_name' => 'Lê Thị Nhã Chân',
                'email' => 'lethinhachan18@gmail.com',
            ]
        );
    }
}
