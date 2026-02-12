<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\OperatingAccount;
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

        // Create treasury2 user
        User::factory()->create([
            'name' => 'Treasury 2 User',
            'email' => 'treasury2@intra.com',
            'role' => 'treasury2',
        ]);

        // Create treasury3 user
        User::factory()->create([
            'name' => 'Treasury 3 User',
            'email' => 'treasury3@intra.com',
            'role' => 'treasury3',
        ]);

        // Create accounting user
        User::factory()->create([
            'name' => 'Accounting User',
            'email' => 'accounting@intra.com',
            'role' => 'accounting',
        ]);

        // Create accounting2 user
        User::factory()->create([
            'name' => 'Accounting 2 User',
            'email' => 'accounting2@intra.com',
            'role' => 'accounting2',
        ]);

        // Create Operating Accounts
        OperatingAccount::create([
            'operating_account_name' => 'BDO - Valero',
            'account_number' => '1380062436',
            'beginning_balance' => 1788906.09,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'BPI-SALCEDO',
            'account_number' => '003773-0407-52',
            'beginning_balance' => 582509.91,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'China Bank - HO(settlement Bank)',
            'account_number' => '303-0885917',
            'beginning_balance' => 1313065.91,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'DBP - Makati Ave.',
            'account_number' => '0405-018364-530',
            'beginning_balance' => 1232516.60,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'LBP - Salcedo (Premium)',
            'account_number' => '1792-1005-85',
            'beginning_balance' => 438577.28,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'ROBINSONS BANK',
            'account_number' => '104030000000000',
            'beginning_balance' => 609425.39,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'Security Bank',
            'account_number' => '39072176',
            'beginning_balance' => 130580.22,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'Metrobank',
            'account_number' => '292-7-29254395-2',
            'beginning_balance' => 2011290.63,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Cag.de Oro (Agency)',
            'account_number' => '00-213-000498-8',
            'beginning_balance' => 263683.93,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Cebu Br.',
            'account_number' => '00-213-000496-4',
            'beginning_balance' => 289827.11,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Davao Br.',
            'account_number' => '00-213-000495-2',
            'beginning_balance' => 115966.48,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Dumaguete Br.',
            'account_number' => '00-213-00067-05',
            'beginning_balance' => 448468.37,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Iloilo ( Agency)',
            'account_number' => '00-213-000497-6',
            'beginning_balance' => 174582.49,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - HV DE LA COSTA',
            'account_number' => '00-213-000492-7',
            'beginning_balance' => 13170983.48,
        ]);
    }
}