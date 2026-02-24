<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function generate(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            
            // Fetch data from database
            $modules = [
                [
                    'name' => 'Collateral',
                    'data' => \App\Models\Collateral::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'collateral',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Time Deposit',
                    'data' => \App\Models\TimeDeposit::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'time_deposit_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Government Securities',
                    'data' => \App\Models\GovernmentSecurity::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'government_security_name',
                    'accField' => 'reference_number'
                ],
                [
                    'name' => 'Other Investment',
                    'data' => \App\Models\OtherInvestment::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'other_investment_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Operating Accounts',
                    'data' => \App\Models\OperatingAccount::with(['collections', 'disbursements', 'renewals', 'withdrawals'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'operating_account_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Dollar',
                    'data' => \App\Models\Dollar::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'dollar_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Corporate Bonds',
                    'data' => \App\Models\CorporateBond::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'corporate_bond_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Cash Infusion',
                    'data' => \App\Models\CashInfusion::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'cash_infusion_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Investment',
                    'data' => \App\Models\Investment::with(['renewals', 'withdrawals', 'balances'])->where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'investment_name',
                    'accField' => 'reference_number'
                ]
            ];
            
            // Create CSV content
            $csv = "Daily Deposit Report - " . date('M d, Y', strtotime($date)) . "\n\n";
            
            // Add summary
            $csv .= "Module Summary\n";
            $csv .= "Module,Record Count\n";
            foreach ($modules as $module) {
                $csv .= $module['name'] . "," . count($module['data']) . "\n";
            }
            $csv .= "\n\n";
            
            // Add data for each module
            foreach ($modules as $module) {
                $csv .= $module['name'] . " Details\n";
                $csv .= $module['name'] . " Name," . ucfirst(str_replace('_', ' ', $module['accField'])) . ",Acquisition Date,Maturity Date,Beginning Balance,Collection,Disbursement,Ending Balance\n";
                
                foreach ($module['data'] as $item) {
                    $name = $item[$module['key']] ?? '';
                    $accNumber = $item[$module['accField']] ?? '';
                    $acqDate = isset($item['acquisition_date']) ? date('M d, Y', strtotime($item['acquisition_date'])) : 'N/A';
                    $matDate = isset($item['maturity_date']) ? date('M d, Y', strtotime($item['maturity_date'])) : 'N/A';
                    $beginBal = isset($item['beginning_balance']) ? $item['beginning_balance'] : 0;
                    $collection = isset($item['collection']) ? $item['collection'] : 0;
                    $disbursement = isset($item['disbursement']) ? $item['disbursement'] : 0;
                    $endBal = isset($item['ending_balance']) ? $item['ending_balance'] : 0;
                    
                    $csv .= "\"" . $name . "\",\"" . $accNumber . "\",\"" . $acqDate . "\",\"" . $matDate . "\"," . $beginBal . "," . $collection . "," . $disbursement . "," . $endBal . "\n";
                }
                $csv .= "\n";
            }
            
            $filename = "Daily_Deposit_Report_" . $date . ".csv";
            
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generatePDF(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            
            // Fetch data from database
            $modules = [
                [
                    'name' => 'Collateral',
                    'data' => \App\Models\Collateral::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'collateral',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Time Deposit',
                    'data' => \App\Models\TimeDeposit::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'time_deposit_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Government Securities',
                    'data' => \App\Models\GovernmentSecurity::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'government_security_name',
                    'accField' => 'reference_number'
                ],
                [
                    'name' => 'Other Investment',
                    'data' => \App\Models\OtherInvestment::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'other_investment_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Operating Accounts',
                    'data' => \App\Models\OperatingAccount::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'operating_account_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Dollar',
                    'data' => \App\Models\Dollar::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'dollar_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Corporate Bonds',
                    'data' => \App\Models\CorporateBond::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'corporate_bond_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Cash Infusion',
                    'data' => \App\Models\CashInfusion::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'cash_infusion_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Investment',
                    'data' => \App\Models\Investment::where('maturity_date', '!=', null)->get()->toArray(),
                    'key' => 'investment_name',
                    'accField' => 'reference_number'
                ]
            ];
            
            // Render Blade template to HTML
            $reportDate = date('F d, Y', strtotime($date));
            
            // Convert logo to base64 for PDF embedding
            $logoPath = public_path('INTRA.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
            
            // Use Dompdf directly
            $dompdf = new \Dompdf\Dompdf();
            $html = view('reports.pdf', ['modules' => $modules, 'reportDate' => $reportDate, 'logoBase64' => $logoBase64])->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            $filename = 'Daily_Deposit_Report_' . $date . '.pdf';
            $output = $dompdf->output();
            
            return response($output)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Throwable $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }
}
