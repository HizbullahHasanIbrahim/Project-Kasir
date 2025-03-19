<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Petugas 1
        User::create([
            'name' => 'Petugas 1',
            'email' => 'petugas1@petugas.com',
            'password' => bcrypt('12345678'),
            'role' => 'cashier',
            'email_verified_at' => now(),
        ]);

        // Petugas 2
        User::create([
            'name' => 'Petugas 2',
            'email' => 'petugas2@petugas.com',
            'password' => bcrypt('12345678'),
            'role' => 'cashier',
            'email_verified_at' => now(),
        ]);

        // Petugas 3
        User::create([
            'name' => 'Petugas 3',
            'email' => 'petugas3@petugas.com',
            'password' => bcrypt('12345678'),
            'role' => 'cashier',
            'email_verified_at' => now(),
        ]);
    }
}
