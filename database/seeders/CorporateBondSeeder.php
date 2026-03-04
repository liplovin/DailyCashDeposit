<?php

namespace Database\Seeders;

use App\Models\CorporateBond;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CorporateBondSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Corporate Bonds
        CorporateBond::create([
            'corporate_bond_name' => 'CHINA BANK - SMC Corp. Bond',
            'account_number' => 'SMC DUE 2027',
            'beginning_balance' => 15000000.00,
            'maturity_date' => '2027-07-08',
        ]);

        CorporateBond::create([
            'corporate_bond_name' => 'AMALGAMATED',
            'account_number' => 'PH0000060634',
            'beginning_balance' => 15000000.00,
            'maturity_date' => '2028-06-26',
        ]);
    }
}
