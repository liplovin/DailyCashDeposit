<?php

namespace Database\Seeders;

use App\Models\Investment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Investments
        Investment::create([
            'investment_name' => 'BANCO DE ORO',
            'reference_number' => 'RTB 05-15',
            'beginning_balance' => 10000000.00,
            'maturity_date' => '2027-03-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'FXTN 03-30',
            'beginning_balance' => 50214049.73,
            'maturity_date' => '2027-01-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-16',
            'beginning_balance' => 7008090.17,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 5014093.96,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 20000000.00,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'IC-MANDATORY RESERVE/RTB 05-17',
            'beginning_balance' => 8152064.00,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'IC-MANDATORY RESERVE/RTB 05-17',
            'beginning_balance' => 5014023.39,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 5085811.25,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 4949640.04,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 100631925.50,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 15985080.01,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 50000000.00,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'IC-MANDATORY RESERVE/NROSS/TBILLS/RTB 05-17',
            'beginning_balance' => 156380545.56,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-16',
            'beginning_balance' => 4879474.96,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'IC-MANDATORY RESERVE/FXTN 10-61',
            'beginning_balance' => 5521331.57,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'IC-MANDATORY RESERVE/ADDITIONAL RESERVE/NROSS/RTB 05-16',
            'beginning_balance' => 105665328.76,
            'maturity_date' => '2027-03-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 05-16',
            'beginning_balance' => 5000000.00,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'MANDATORY RESERVE/NROSS/RTB 10-05',
            'beginning_balance' => 31155338.50,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'reference_number' => 'RTB 5-13',
            'beginning_balance' => 25000000.00,
            'maturity_date' => '2028-09-30',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK-RTBONDS',
            'reference_number' => 'IC-MANDATORY RESERVE/PID10261057',
            'beginning_balance' => 20000000.00,
            'maturity_date' => '2026-09-20',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'PHDSO525H130',
            'beginning_balance' => 11997453.25,
            'maturity_date' => '2026-04-08',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'IC-MANDATORY RESERVE/FXTN 7-61',
            'beginning_balance' => 9365261.02,
            'maturity_date' => '2030-07-27',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'FXTN 05-77',
            'beginning_balance' => 10194129.86,
            'maturity_date' => '2026-04-08',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'IC-MANDATORY RESERVE/PID0107E617',
            'beginning_balance' => 4949865.17,
            'maturity_date' => '2027-05-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'IC-MANDATORY RESERVE/NROSS/FXTN 7-61',
            'beginning_balance' => 50273191.39,
            'maturity_date' => '2027-03-07',
        ]);

        Investment::create([
            'investment_name' => 'SECURITY BANK',
            'reference_number' => 'RTB 05-18',
            'beginning_balance' => 20000000.00,
            'maturity_date' => '2029-02-28',
        ]);

        Investment::create([
            'investment_name' => 'SECURITY BANK',
            'reference_number' => 'FXTN 03-30',
            'beginning_balance' => 10960534.12,
            'maturity_date' => '2027-01-04',
        ]);

        Investment::create([
            'investment_name' => 'SECURITY BANK',
            'reference_number' => 'RTB 05-17',
            'beginning_balance' => 22470427.86,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'reference_number' => 'FXTN 07-70/PH00000057218',
            'beginning_balance' => 38804069.60,
            'maturity_date' => '2027-08-14',
        ]);

        Investment::create([
            'investment_name' => 'AMALGAMATED',
            'reference_number' => 'PH00000607 RTB 0519',
            'beginning_balance' => 20173513.39,
            'maturity_date' => '2030-08-20',
        ]);

        // TEST RECORDS FOR ACTIONS COLUMN VISIBILITY
        // Created TODAY - Should show Edit/Delete buttons
        Investment::create([
            'investment_name' => '[TEST] Today Creation',
            'reference_number' => 'TEST-INV-TODAY',
            'beginning_balance' => 5000000.00,
            'maturity_date' => '2026-05-23',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Created YESTERDAY - Should hide Edit/Delete buttons
        Investment::create([
            'investment_name' => '[TEST] Yesterday Creation',
            'reference_number' => 'TEST-INV-YESTERDAY',
            'beginning_balance' => 3500000.00,
            'maturity_date' => '2026-04-15',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Created 2 DAYS AGO - Should hide Edit/Delete buttons
        Investment::create([
            'investment_name' => '[TEST] 2 Days Old',
            'reference_number' => 'TEST-INV-2DAYS',
            'beginning_balance' => 2000000.00,
            'maturity_date' => '2026-03-20',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
