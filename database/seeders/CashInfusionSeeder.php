<?php

namespace Database\Seeders;

use App\Models\CashInfusion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashInfusionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TEST RECORDS FOR ACTIONS COLUMN VISIBILITY
        // Created TODAY - Should show Edit/Delete buttons
        CashInfusion::create([
            'cash_infusion_name' => '[TEST] Today Creation',
            'account_number' => 'TEST-CI-TODAY',
            'beginning_balance' => 5000000.00,
            'maturity_date' => '2026-05-23',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Created YESTERDAY - Should hide Edit/Delete buttons
        CashInfusion::create([
            'cash_infusion_name' => '[TEST] Yesterday Creation',
            'account_number' => 'TEST-CI-YESTERDAY',
            'beginning_balance' => 3500000.00,
            'maturity_date' => '2026-04-15',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Created 2 DAYS AGO - Should hide Edit/Delete buttons
        CashInfusion::create([
            'cash_infusion_name' => '[TEST] 2 Days Old',
            'account_number' => 'TEST-CI-2DAYS',
            'beginning_balance' => 2000000.00,
            'maturity_date' => '2026-03-20',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
