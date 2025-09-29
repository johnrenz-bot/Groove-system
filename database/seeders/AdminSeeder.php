<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basahin mula sa .env para hindi hard-coded
        $name     = env('ADMIN_NAME', 'Admin User');
        $username = env('ADMIN_USERNAME', 'adminuser');
        $email    = env('ADMIN_EMAIL', 'johnrenzbandianon9@gmail.com');
        $password = env('ADMIN_PASSWORD', 'adminpassword');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'username' => $username,
                'password' => Hash::make($password),
            ]
        );
    }
}
