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
            'acquisition_date' => '2024-07-08',
            'maturity_date' => '2027-07-08',
        ]);

        CorporateBond::create([
            'corporate_bond_name' => 'AMALGAMATED',
            'account_number' => 'PH0000060634',
            'beginning_balance' => 15000000.00,
            'acquisition_date' => '2025-06-26',
            'maturity_date' => '2028-06-26',
        ]);

        // TEST RECORDS FOR ACTIONS COLUMN VISIBILITY
        // Created TODAY - Should show Edit/Delete buttons
        CorporateBond::create([
            'corporate_bond_name' => '[TEST] Today Creation',
            'account_number' => 'TEST-CB-TODAY',
            'beginning_balance' => 5000000.00,
            'acquisition_date' => '2023-05-23',
            'maturity_date' => '2026-05-23',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Created YESTERDAY - Should hide Edit/Delete buttons
        CorporateBond::create([
            'corporate_bond_name' => '[TEST] Yesterday Creation',
            'account_number' => 'TEST-CB-YESTERDAY',
            'beginning_balance' => 3500000.00,
            'acquisition_date' => '2023-04-15',
            'maturity_date' => '2026-04-15',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Created 2 DAYS AGO - Should hide Edit/Delete buttons
        CorporateBond::create([
            'corporate_bond_name' => '[TEST] 2 Days Old',
            'account_number' => 'TEST-CB-2DAYS',
            'beginning_balance' => 2000000.00,
            'acquisition_date' => '2023-03-20',
            'maturity_date' => '2026-03-20',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
