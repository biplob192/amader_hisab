<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@amaderhisab.com'],
            ['name' => 'Super Admin', 'password' => 'password'],
        );

        User::firstOrCreate(
            ['email' => 'biplob@amaderhisab.com'],
            ['name' => 'Md Biplob Mia', 'password' => 'password'],
        );
    }
}
