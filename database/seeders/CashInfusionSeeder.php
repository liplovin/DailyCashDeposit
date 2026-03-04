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
        CashInfusion::create([
            'cash_infusion_name' => 'CHINABANK',
            'account_number' => '112000000968',
            'beginning_balance' => 11273.08,
        ]);
    }
}
