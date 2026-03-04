<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        $this->call(UserSeeder::class);

        $this->call(OperatingAccountSeeder::class);
        $this->call(CollateralSeeder::class);
        $this->call(CorporateBondSeeder::class);
        $this->call(CashInfusionSeeder::class);
        $this->call(DollarSeeder::class);
        $this->call(GovernmentSecuritySeeder::class);
        $this->call(InvestmentSeeder::class);
        $this->call(OtherInvestmentSeeder::class);
        $this->call(TimeDepositSeeder::class);
    }
}