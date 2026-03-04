<?php

namespace Database\Seeders;

use App\Models\OtherInvestment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OtherInvestmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OtherInvestment::create([
            'other_investment_name' => 'MBTC (UITF)',
            'account_number' => 'null',
            'beginning_balance' => 2139232.02,

        ]);

      OtherInvestment::create([
            'other_investment_name' => 'PS BANK (ESCROW)',
            'account_number' => '881-161-000378',
            'beginning_balance' => 1290098.17,
            'maturity_date' => '2026-03-02',

        ]);

        OtherInvestment::create([
            'other_investment_name' => 'CHINABANK - TRUST FUND',
            'account_number' => '01532103TA01',
            'beginning_balance' => 12948559.46,

        ]);
    }
}
