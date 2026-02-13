<?php

namespace Database\Seeders;

use App\Models\TimeDeposit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeDepositSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Time Deposits
        TimeDeposit::create([
            'time_deposit_name' => 'AMALGAMATED',
            'account_number' => 'FXTN 0330',
            'beginning_balance' => 100000000.00,
            'maturity_date' => '2027-01-04',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'AMALGAMATED',
            'account_number' => 'FXTN 1072',
            'beginning_balance' => 10234128.63,
            'maturity_date' => '2026-09-02',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'LANDBANK',
            'account_number' => 'TD',
            'beginning_balance' => 1037527.80,
            'maturity_date' => '2026-02-24',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'Metrobank',
            'account_number' => '3030000724-1834212',
            'beginning_balance' => 10000000.00,
            'maturity_date' => '2026-02-02',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'Metrobank',
            'account_number' => '3030000724-1807227',
            'beginning_balance' => 104340.09,
            'maturity_date' => '2026-01-05',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'AMALGAMATED',
            'account_number' => 'FXTN 0764',
            'beginning_balance' => 10231827.50,
            'maturity_date' => '2026-03-05',
        ]);

        TimeDeposit::create([
            'time_deposit_name' => 'AMALGAMATED',
            'account_number' => 'PH000057860',
            'beginning_balance' => 10000000.00,
            'maturity_date' => '2026-01-15',
        ]);
    }
}
