<?php

namespace Database\Seeders;

use App\Models\Dollar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DollarSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dollar::create([
            'dollar_name' => 'EAST WEST BANK-$ TD',
            'account_number' => '3000-01-17392-1',
            'beginning_balance' => 46953.45,
            'maturity_date' => '2026-03-30',
        ]);
           Dollar::create([
            'dollar_name' => 'EAST WEST BANK-$ TD',
            'account_number' => '3000-01-37505-2',
            'beginning_balance' => 8595.34,
            'maturity_date' => '2026-03-30',
        ]);
        
    }
}
