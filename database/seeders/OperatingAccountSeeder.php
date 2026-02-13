<?php

namespace Database\Seeders;

use App\Models\OperatingAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperatingAccountSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Operating Accounts
        OperatingAccount::create([
            'operating_account_name' => 'BDO - Valero',
            'account_number' => '1380062436',
            'beginning_balance' => 1788906.09,
            'maturity_date' => '2026-12-31',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'BPI-SALCEDO',
            'account_number' => '003773-0407-52',
            'beginning_balance' => 582509.91,
            'maturity_date' => '2026-09-30',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'China Bank - HO(settlement Bank)',
            'account_number' => '303-0885917',
            'beginning_balance' => 1313065.91,
            'maturity_date' => '2027-03-15',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'DBP - Makati Ave.',
            'account_number' => '0405-018364-530',
            'beginning_balance' => 1232516.60,
            'maturity_date' => '2026-06-30',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'LBP - Salcedo (Premium)',
            'account_number' => '1792-1005-85',
            'beginning_balance' => 438577.28,
            'maturity_date' => '2027-02-28',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'ROBINSONS BANK',
            'account_number' => '104030000000000',
            'beginning_balance' => 609425.39,
            'maturity_date' => '2026-08-15',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'Security Bank',
            'account_number' => '39072176',
            'beginning_balance' => 130580.22,
            'maturity_date' => '2026-11-30',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'Metrobank',
            'account_number' => '292-7-29254395-2',
            'beginning_balance' => 2011290.63,
            'maturity_date' => '2027-01-31',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Cag.de Oro (Agency)',
            'account_number' => '00-213-000498-8',
            'beginning_balance' => 263683.93,
            'maturity_date' => '2026-10-30',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Cebu Br.',
            'account_number' => '00-213-000496-4',
            'beginning_balance' => 289827.11,
            'maturity_date' => '2026-07-31',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Davao Br.',
            'account_number' => '00-213-000495-2',
            'beginning_balance' => 115966.48,
            'maturity_date' => '2026-09-15',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Dumaguete Br.',
            'account_number' => '00-213-00067-05',
            'beginning_balance' => 448468.37,
            'maturity_date' => '2027-04-30',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - Iloilo ( Agency)',
            'account_number' => '00-213-000497-6',
            'beginning_balance' => 174582.49,
            'maturity_date' => '2026-12-15',
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'UBP - HV DE LA COSTA',
            'account_number' => '00-213-000492-7',
            'beginning_balance' => 13170983.48,
            'maturity_date' => '2027-05-31',
        ]);
    }
}
