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
            'account_number' => '30300000824-1904652',
            'beginning_balance' => 5181392.87,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904623',
            'beginning_balance' => 357009.29,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1817888',
            'beginning_balance' => 1197458.58,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904640',
            'beginning_balance' => 1425261.92,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1817893',
            'beginning_balance' => 587914.98,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904636',
            'beginning_balance' => 2285869.70,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904648',
            'beginning_balance' => 4355217.42,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904656',
            'beginning_balance' => 1101012.53,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904627',
            'beginning_balance' => 1132049.47,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1895376',
            'beginning_balance' => 10826210.41,
            'maturity_date' => '2026-02-26',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1805000',
            'beginning_balance' => 15707701.27,
            'maturity_date' => '2026-03-30',
        ]);

        Collateral::create([
            'collateral' => 'Metrobank - collateral',
            'account_number' => '30300000824-1904661',
            'beginning_balance' => 12570000.00,
            'maturity_date' => '2026-03-30',
        ]);

    }
}
