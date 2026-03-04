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
            'beginning_balance' => 3583065.57,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'BPI-SALCEDO',
            'account_number' => '003773-0407-52',
            'beginning_balance' => 646958.66,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'China Bank - HO(settlement Bank)',
            'account_number' => '303-0885917',
            'beginning_balance' => 2423315.91,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'DBP - Makati Ave.',
            'account_number' => '0405-018364-530',
            'beginning_balance' => 524436.16,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'LBP - Salcedo (Premium)',
            'account_number' => '1792-1005-85',
            'beginning_balance' => 438577.28,
        ]);

        OperatingAccount::create([
            'operating_account_name' => 'ROBINSONS BANK',
            'account_number' => '1.0403E+14',
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
            'beginning_balance' => 3735027.27,
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
            'beginning_balance' => 19929103.42,
        ]);


    }
}
