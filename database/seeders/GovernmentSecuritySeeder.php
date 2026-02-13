<?php

namespace Database\Seeders;

use App\Models\GovernmentSecurity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GovernmentSecuritySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Government Securities
        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTN11831-7.71',
            'beginning_balance' => 20053043.23,
            'maturity_date' => '2031-01-18',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO42730-7-63',
            'beginning_balance' => 11859695.56,
            'maturity_date' => '2030-04-27',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTN 7-71',
            'beginning_balance' => 4614498.32,
            'maturity_date' => '2031-01-18',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO42228-7-64',
            'beginning_balance' => 7681017.92,
            'maturity_date' => '2028-04-22',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'RTB022825-5-18',
            'beginning_balance' => 6116672.27,
            'maturity_date' => '2025-02-18',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO50427-0046',
            'beginning_balance' => 9874432.95,
            'maturity_date' => '2029-05-19',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO214267-7-62',
            'beginning_balance' => 5124000.63,
            'maturity_date' => '2029-02-28',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO72730-7-70',
            'beginning_balance' => 20233839.01,
            'maturity_date' => '2027-09-04',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO51829-7-67',
            'beginning_balance' => 20475071.26,
            'maturity_date' => '2026-02-14',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'RTB022829-5-18',
            'beginning_balance' => 16225463.08,
            'maturity_date' => '2029-05-19',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO214267-762',
            'beginning_balance' => 6153226.14,
            'maturity_date' => '2026-08-04',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO020426',
            'beginning_balance' => 4498241.02,
            'maturity_date' => '2026-02-14',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO72730-2031',
            'beginning_balance' => 9309374.14,
            'maturity_date' => '2026-02-04',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO11831-7.71',
            'beginning_balance' => 5165083.54,
            'maturity_date' => '2026-04-08',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'RTB 5-18',
            'beginning_balance' => 6147646.01,
            'maturity_date' => '2029-02-28',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTN 7-65',
            'beginning_balance' => 5137713.08,
            'maturity_date' => '2030-04-27',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'TBLO50826',
            'beginning_balance' => 11513362.50,
            'maturity_date' => '2026-08-05',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'RTB062227-R5-14',
            'beginning_balance' => 9883890.36,
            'maturity_date' => '2027-06-07',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'RTB082030-5-19',
            'beginning_balance' => 4496400.00,
            'maturity_date' => '2030-08-20',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO13279-7-68',
            'beginning_balance' => 41671577.18,
            'maturity_date' => '2028-10-13',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO42730-7-69',
            'beginning_balance' => 14155770.79,
            'maturity_date' => '2030-04-27',
        ]);

        GovernmentSecurity::create([
            'government_security_name' => 'Metrobank',
            'account_number' => 'FXTO70930-1063',
            'beginning_balance' => 21001872.12,
            'maturity_date' => '2030-04-27',
        ]);
    }
}
