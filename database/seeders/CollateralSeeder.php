<?php

namespace Database\Seeders;

use App\Models\Collateral;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollateralSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Collaterals
        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904652',
            'beginning_balance' => 5181392.87,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904653',
            'beginning_balance' => 587028.46,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1817888',
            'beginning_balance' => 1197458.58,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904640',
            'beginning_balance' => 1425261.52,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1817893',
            'beginning_balance' => 587814.85,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904636',
            'beginning_balance' => 2285869.70,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904648',
            'beginning_balance' => 4355217.19,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904656',
            'beginning_balance' => 1101012.63,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1895827',
            'beginning_balance' => 1132068.35,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1895376',
            'beginning_balance' => 10826210.41,
            'acquisition_date' => '2023-02-26',
            'maturity_date' => '2026-02-26',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1895000',
            'beginning_balance' => 15707500.00,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '3030000824-1904651',
            'beginning_balance' => 12570000.00,
            'acquisition_date' => '2023-03-30',
            'maturity_date' => '2026-03-30',
        ]);

        // TEST RECORDS FOR ACTIONS COLUMN VISIBILITY
        // Created TODAY - Should show Edit/Delete buttons
        Collateral::create([
            'collateral' => '[TEST] Today Creation',
            'account_number' => 'TEST-COLLATERAL-TODAY',
            'beginning_balance' => 5000000.00,
            'acquisition_date' => '2023-05-23',
            'maturity_date' => '2026-05-23',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Created YESTERDAY - Should hide Edit/Delete buttons
        Collateral::create([
            'collateral' => '[TEST] Yesterday Creation',
            'account_number' => 'TEST-COLLATERAL-YESTERDAY',
            'beginning_balance' => 3500000.00,
            'acquisition_date' => '2023-04-15',
            'maturity_date' => '2026-04-15',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Created 2 DAYS AGO - Should hide Edit/Delete buttons
        Collateral::create([
            'collateral' => '[TEST] 2 Days Old',
            'account_number' => 'TEST-COLLATERAL-2DAYS',
            'beginning_balance' => 2000000.00,
            'acquisition_date' => '2023-03-20',
            'maturity_date' => '2026-03-20',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
