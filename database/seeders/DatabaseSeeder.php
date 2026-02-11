<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@intra.com',
            'role' => 'admin',
        ]);

        // Create treasury user
        User::factory()->create([
            'name' => 'Treasury User',
            'email' => 'treasury@intra.com',
            'role' => 'treasury',
        ]);

        // Create accounting user
        User::factory()->create([
            'name' => 'Accounting User',
            'email' => 'accounting@intra.com',
            'role' => 'accounting',
        ]);
    }
}
