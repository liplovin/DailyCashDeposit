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
        $users = [
            ['name' => 'Super Admin',      'email' => 'superadmin@intra.com',    'role' => 'superadmin'],
            ['name' => 'Nelson Manabal',   'email' => 'nelson@intra.com',        'role' => 'admin'],
            ['name' => 'Melanie Mendoza',  'email' => 'melanie@intra.com',       'role' => 'treasury'],
            ['name' => 'Airah Ascado',     'email' => 'airah@intra.com',         'role' => 'treasury2'],
            ['name' => 'Aira Santiago',    'email' => 'airasantiago@intra.com',  'role' => 'treasury3'],
            ['name' => 'Edwin Domingo',    'email' => 'edwin@intra.com',         'role' => 'accounting'],
            ['name' => 'Accounting 2 User','email' => 'accounting2@intra.com',   'role' => 'accounting2'],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'email_verified_at' => now(),
                ])
            );
        }
    }
}
