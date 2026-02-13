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
            'account_number' => 'RTB 05-15',
            'beginning_balance' => 10000000.00,
            'maturity_date' => '2027-03-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'account_number' => 'FXTN 03-30',
            'beginning_balance' => 50214049.73,
            'maturity_date' => '2027-03-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'account_number' => 'RTB 05-16',
            'beginning_balance' => 7008090.17,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'account_number' => 'RTB 05-17',
            'beginning_balance' => 5014093.86,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'account_number' => 'IC-MANDATORY RESERVE/',
            'beginning_balance' => 8152064.00,
            'maturity_date' => '2028-08-22',
        ]);

        Investment::create([
            'investment_name' => 'CHINA BANK',
            'account_number' => 'FXTO214267-7-62',
            'beginning_balance' => 5124000.63,
            'maturity_date' => '2029-02-28',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'account_number' => 'MANDATORY RESERVE/',
            'beginning_balance' => 31155338.06,
            'maturity_date' => '2028-03-07',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'account_number' => 'RTB 5-3',
            'beginning_balance' => 25000000.00,
            'maturity_date' => '2028-08-20',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'account_number' => 'PH0052525H130',
            'beginning_balance' => 11997453.93,
            'maturity_date' => '2027-04-08',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'account_number' => 'FXTN 05-77',
            'beginning_balance' => 10144129.86,
            'maturity_date' => '2031-04-08',
        ]);

        Investment::create([
            'investment_name' => 'SECURITY BANK',
            'account_number' => 'RTB 05-18',
            'beginning_balance' => 20000000.00,
            'maturity_date' => '2027-02-28',
        ]);

        Investment::create([
            'investment_name' => 'SECURITY BANK',
            'account_number' => 'FXTO42730-7-63',
            'beginning_balance' => 10060534.12,
            'maturity_date' => '2027-01-04',
        ]);

        Investment::create([
            'investment_name' => 'CHINABANK',
            'account_number' => 'FXTN 07-70/',
            'beginning_balance' => 38804069.68,
            'maturity_date' => '2030-02-27',
        ]);

        Investment::create([
            'investment_name' => 'AMALGAMATED',
            'account_number' => 'PH0050050907 RTB 0519',
            'beginning_balance' => 20173513.38,
            'maturity_date' => '2029-05-09',
        ]);
    }
}
