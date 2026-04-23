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
                    'data' => \App\Models\OtherInvestment::get()->filter(function($item) {
                        return floatval($item->beginning_balance) > 0;
                    })->toArray(),
                    'key' => 'other_investment_name',
                    'accField' => 'account_number'
                ],
                [
                    'name' => 'Operating Accounts',
                    'data' => \App\Models\OperatingAccount::with(['collections', 'disbursements', 'renewals', 'withdrawals'])->get()->toArray(),
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
                    'data' => \App\Models\CashInfusion::get()->filter(function($item) {
                        return floatval($item->beginning_balance) > 0;
                    })->toArray(),
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
                
                // Conditional header based on module
                if ($module['name'] === 'Cash Infusion' || $module['name'] === 'Operating Accounts') {
                    $csv .= $module['name'] . " Name," . ucfirst(str_replace('_', ' ', $module['accField'])) . ",Beginning Balance,Collection,Disbursement,Ending Balance\n";
                } else {
                    $csv .= $module['name'] . " Name," . ucfirst(str_replace('_', ' ', $module['accField'])) . ",Acquisition Date,Maturity Date,Beginning Balance,Collection,Disbursement,Ending Balance\n";
                }
                
                foreach ($module['data'] as $item) {
                    $name = $item[$module['key']] ?? '';
                    $accNumber = $item[$module['accField']] ?? '';
                    $beginBal = isset($item['beginning_balance']) ? $item['beginning_balance'] : 0;
                    $collection = isset($item['collection']) ? $item['collection'] : 0;
                    $disbursement = isset($item['disbursement']) ? $item['disbursement'] : 0;
                    $endBal = isset($item['ending_balance']) ? $item['ending_balance'] : 0;
                    
                    // Conditional data based on module
                    if ($module['name'] === 'Cash Infusion' || $module['name'] === 'Operating Accounts') {
                        $csv .= "\"" . $name . "\",\"" . $accNumber . "\"," . $beginBal . "," . $collection . "," . $disbursement . "," . $endBal . "\n";
                    } else {
                        $acqDate = isset($item['acquisition_date']) ? date('M d, Y', strtotime($item['acquisition_date'])) : 'N/A';
                        $matDate = isset($item['maturity_date']) ? date('M d, Y', strtotime($item['maturity_date'])) : 'N/A';
                        $csv .= "\"" . $name . "\",\"" . $accNumber . "\",\"" . $acqDate . "\",\"" . $matDate . "\"," . $beginBal . "," . $collection . "," . $disbursement . "," . $endBal . "\n";
                    }
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
            
            // Fetch data from database - ALL records with relationship data
            $modules = [];
            
            // Helper function to filter transactions by date
            $filterTransactionsByDate = function($data, $date) {
                $result = [];
                foreach ($data as $item) {
                    $newItem = $item;
                    
                    // Calculate beginning balance for this date
                    $balance = floatval($item['beginning_balance'] ?? 0);
                    
                    // Handle both flat properties and relationship arrays
                    
                    // For Operating Accounts with collections array
                    if (isset($item['collections']) && is_array($item['collections'])) {
                        foreach ($item['collections'] as $collection) {
                            $collectionDate = date('Y-m-d', strtotime($collection['created_at'] ?? ''));
                            if ($collectionDate < $date) {
                                $balance += floatval($collection['collection_amount'] ?? 0);
                            }
                        }
                    } else {
                        // For other modules with flat properties
                        if (isset($item['collection_date'])) {
                            $collectionDate = date('Y-m-d', strtotime($item['collection_date']));
                            if ($collectionDate < $date) {
                                $balance += floatval($item['collection'] ?? 0);
                            }
                        }
                    }
                    
                    // For Operating Accounts with disbursements array
                    if (isset($item['disbursements']) && is_array($item['disbursements'])) {
                        foreach ($item['disbursements'] as $disbursement) {
                            $disbursementDate = date('Y-m-d', strtotime($disbursement['created_at'] ?? ''));
                            if ($disbursementDate < $date) {
                                $balance -= floatval($disbursement['amount'] ?? 0);
                            }
                        }
                    } else {
                        // For other modules with flat properties
                        if (isset($item['disbursement_date'])) {
                            $disbursementDate = date('Y-m-d', strtotime($item['disbursement_date']));
                            if ($disbursementDate < $date) {
                                $balance -= floatval($item['disbursement'] ?? 0);
                            }
                        }
                    }
                    
                    $newItem['beginning_balance'] = $balance;
                    
                    // Calculate collection for this date
                    $newItem['collection'] = 0;
                    if (isset($item['collections']) && is_array($item['collections'])) {
                        foreach ($item['collections'] as $collection) {
                            $collectionDate = date('Y-m-d', strtotime($collection['created_at'] ?? ''));
                            if ($collectionDate === $date) {
                                $newItem['collection'] += floatval($collection['collection_amount'] ?? 0);
                            }
                        }
                    } else {
                        if (isset($item['collection_date'])) {
                            $collectionDate = date('Y-m-d', strtotime($item['collection_date']));
                            $newItem['collection'] = ($collectionDate === $date) ? (floatval($item['collection']) ?? 0) : 0;
                        }
                    }
                    
                    // Calculate disbursement for this date
                    $newItem['disbursement'] = 0;
                    if (isset($item['disbursements']) && is_array($item['disbursements'])) {
                        foreach ($item['disbursements'] as $disbursement) {
                            $disbursementDate = date('Y-m-d', strtotime($disbursement['created_at'] ?? ''));
                            if ($disbursementDate === $date) {
                                $newItem['disbursement'] += floatval($disbursement['amount'] ?? 0);
                            }
                        }
                    } else {
                        if (isset($item['disbursement_date'])) {
                            $disbursementDate = date('Y-m-d', strtotime($item['disbursement_date']));
                            $newItem['disbursement'] = ($disbursementDate === $date) ? (floatval($item['disbursement']) ?? 0) : 0;
                        }
                    }
                    
                    // Calculate ending balance
                    $newItem['ending_balance'] = $balance + $newItem['collection'] - $newItem['disbursement'];
                    
                    $result[] = $newItem;
                }
                return $result;
            };
            
            // Collateral
            $collaterals = \App\Models\Collateral::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Collateral',
                'data' => $filterTransactionsByDate($collaterals, $date),
                'key' => 'collateral',
                'accField' => 'account_number'
            ];
            
            // Time Deposit
            $timeDeposits = \App\Models\TimeDeposit::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Time Deposit',
                'data' => $filterTransactionsByDate($timeDeposits, $date),
                'key' => 'time_deposit_name',
                'accField' => 'account_number'
            ];
            
            // Government Securities
            $govSecurities = \App\Models\GovernmentSecurity::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Government Securities',
                'data' => $filterTransactionsByDate($govSecurities, $date),
                'key' => 'government_security_name',
                'accField' => 'reference_number'
            ];
            
            // Other Investment
            $otherInvestments = \App\Models\OtherInvestment::get()->filter(function($item) {
                return floatval($item->beginning_balance) > 0;
            })->toArray();
            $modules[] = [
                'name' => 'Other Investment',
                'data' => $filterTransactionsByDate($otherInvestments, $date),
                'key' => 'other_investment_name',
                'accField' => 'account_number'
            ];
            
            // Operating Accounts
            $operatingAccounts = \App\Models\OperatingAccount::with(['collections', 'disbursements'])->get()->toArray();
            $modules[] = [
                'name' => 'Operating Accounts',
                'data' => $filterTransactionsByDate($operatingAccounts, $date),
                'key' => 'operating_account_name',
                'accField' => 'account_number'
            ];
            
            // Dollar
            $dollars = \App\Models\Dollar::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Dollar',
                'data' => $filterTransactionsByDate($dollars, $date),
                'key' => 'dollar_name',
                'accField' => 'account_number'
            ];
            
            // Corporate Bonds
            $corporateBonds = \App\Models\CorporateBond::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Corporate Bonds',
                'data' => $filterTransactionsByDate($corporateBonds, $date),
                'key' => 'corporate_bond_name',
                'accField' => 'account_number'
            ];
            
            // Cash Infusion - filter by beginning_balance > 0 (not fully withdrawn)
            $cashInfusions = \App\Models\CashInfusion::get()->filter(function($item) {
                return floatval($item->beginning_balance) > 0;
            })->toArray();
            $modules[] = [
                'name' => 'Cash Infusion',
                'data' => $filterTransactionsByDate($cashInfusions, $date),
                'key' => 'cash_infusion_name',
                'accField' => 'account_number'
            ];
            
            // Investment
            $investments = \App\Models\Investment::where('maturity_date', '!=', null)->get()->toArray();
            $modules[] = [
                'name' => 'Investment',
                'data' => $filterTransactionsByDate($investments, $date),
                'key' => 'investment_name',
                'accField' => 'reference_number'
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
            $html = view('reports.pdf', [
                'modules' => $modules, 
                'reportDate' => $reportDate, 
                'logoBase64' => $logoBase64,
                'transactionDate' => $date
            ])->render();
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
