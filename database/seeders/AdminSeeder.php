<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            ['password' => Hash::make('admin123'), 'full_name' => 'Quản trị viên', 'email' => 'admin@eventblog.local']
        );
    }
}
