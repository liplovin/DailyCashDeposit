<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Nelson Manabal',
            'email' => 'nelson@intra.com',
            'role' => 'admin',
        ]);

        // Create treasury user
        User::factory()->create([
            'name' => 'Melanie Mendoza',
            'email' => 'melanie@intra.com',
            'role' => 'treasury',
        ]);

        // Create treasury2 user
        User::factory()->create([
            'name' => 'Airah Ascado',
            'email' => 'airah@intra.com',
            'role' => 'treasury2',
        ]);

        // Create treasury3 user
        User::factory()->create([
            'name' => 'Aira Santiago',
            'email' => 'airasantiago@intra.com',
            'role' => 'treasury3',
        ]);

        // Create accounting user
        User::factory()->create([
            'name' => 'Edwin Domingo',
            'email' => 'edwin@intra.com',
            'role' => 'accounting',
        ]);

        // Create accounting2 user
        User::factory()->create([
            'name' => 'Accounting 2 User',
            'email' => 'accounting2@intra.com',
            'role' => 'accounting2',
        ]);
    }
}
