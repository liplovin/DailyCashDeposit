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

        // Seed Operating Accounts
        $this->call(OperatingAccountSeeder::class);

        // Seed Collaterals
        $this->call(CollateralSeeder::class);

        // Seed Time Deposits
        $this->call(TimeDepositSeeder::class);

        // Seed Government Securities
        $this->call(GovernmentSecuritySeeder::class);

        // Seed Investments
        $this->call(InvestmentSeeder::class);

        // Seed Corporate Bonds
        $this->call(CorporateBondSeeder::class);
    }
}