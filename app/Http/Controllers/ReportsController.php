<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportsController extends Controller
{
    public function generate(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $spreadsheet = new Spreadsheet();
        
        // Remove default sheet
        $spreadsheet->removeSheetByIndex(0);
        
        // Define modules
        $modules = [
            [
                'name' => 'Collateral',
                'data' => $request->input('collaterals', []),
                'key' => 'collateral',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Time Deposit',
                'data' => $request->input('timeDeposits', []),
                'key' => 'time_deposit_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Government Securities',
                'data' => $request->input('governmentSecurities', []),
                'key' => 'government_security_name',
                'accField' => 'reference_number'
            ],
            [
                'name' => 'Other Investment',
                'data' => $request->input('otherInvestments', []),
                'key' => 'other_investment_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Operating Accounts',
                'data' => $request->input('operatingAccounts', []),
                'key' => 'operating_account_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Dollar',
                'data' => $request->input('dollars', []),
                'key' => 'dollar_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Corporate Bonds',
                'data' => $request->input('corporateBonds', []),
                'key' => 'corporate_bond_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Cash Infusion',
                'data' => $request->input('cashInfusions', []),
                'key' => 'cash_infusion_name',
                'accField' => 'account_number'
            ],
            [
                'name' => 'Investment',
                'data' => $request->input('investments', []),
                'key' => 'investment_name',
                'accField' => 'reference_number'
            ]
        ];
        
        // Create sheet for each module
        foreach ($modules as $module) {
            $this->createModuleSheet($spreadsheet, $module, $date);
        }
        
        // Create summary sheet
        $this->createSummarySheet($spreadsheet, $modules, $date);
        
        // Set active sheet to summary
        $spreadsheet->setActiveSheetIndex($spreadsheet->getSheetCount() - 1);
        
        // Create writer and output
        $writer = new Xlsx($spreadsheet);
        $filename = storage_path("app/reports/Daily_Deposit_Report_" . $date . ".xlsx");
        
        // Ensure directory exists
        if (!is_dir(storage_path("app/reports"))) {
            mkdir(storage_path("app/reports"), 0755, true);
        }
        
        $writer->save($filename);
        
        return response()->download($filename, "Daily_Deposit_Report_" . $date . ".xlsx")->deleteFileAfterSend(true);
    }
    
    private function createModuleSheet($spreadsheet, $module, $date)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(substr($module['name'], 0, 31));
        
        // Determine account field name
        $accField = $module['accField'] ?? 'account_number';
        
        // Headers
        $headers = [$module['name'] . ' Name', ucfirst(str_replace('_', ' ', $accField)), 'Acquisition Date', 'Maturity Date', 'Beginning Balance', 'Collection', 'Disbursement', 'Ending Balance'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => new Font(['bold' => true, 'color' => ['rgb' => 'FFFFFF']]),
            'fill' => new Fill(['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCD34D']])
        ];
        
        for ($col = 1; $col <= 8; $col++) {
            $sheet->getCellByColumnAndRow($col, 1)->applyFromArray($headerStyle);
        }
        
        // Add data
        $row = 2;
        foreach ($module['data'] as $item) {
            $sheet->setCellValue('A' . $row, $item[$module['key']] ?? '');
            $sheet->setCellValue('B' . $row, $item[$accField] ?? '');
            $sheet->setCellValue('C' . $row, isset($item['acquisition_date']) ? date('M d, Y', strtotime($item['acquisition_date'])) : 'N/A');
            $sheet->setCellValue('D' . $row, isset($item['maturity_date']) ? date('M d, Y', strtotime($item['maturity_date'])) : 'N/A');
            
            $sheet->setCellValue('E' . $row, isset($item['beginning_balance']) ? (float)$item['beginning_balance'] : 0);
            $sheet->setCellValue('F' . $row, isset($item['collection']) ? (float)$item['collection'] : 0);
            $sheet->setCellValue('G' . $row, isset($item['disbursement']) ? (float)$item['disbursement'] : 0);
            $sheet->setCellValue('H' . $row, isset($item['ending_balance']) ? (float)$item['ending_balance'] : 0);
            
            // Format currency columns
            for ($col = 5; $col <= 8; $col++) {
                $sheet->getCellByColumnAndRow($col, $row)->setDataType('n');
            }
            
            $row++;
        }
        
        // Auto-fit columns
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $sheet->getStyle('E2:H' . ($row - 1))->getNumberFormat()->setFormatCode('₱#,##0.00');
    }
    
    private function createSummarySheet($spreadsheet, $modules, $date)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Summary');
        
        // Title
        $sheet->setCellValue('A1', 'Daily Deposit Report Summary');
        $sheet->getCellByColumnAndRow(1, 1)->getFont()->setSize(16)->setBold(true);
        
        $sheet->setCellValue('A2', 'Report Date: ' . date('M d, Y', strtotime($date)));
        $sheet->getCellByColumnAndRow(1, 2)->getFont()->setSize(12);
        
        // Module summary table
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Module');
        $sheet->setCellValue('B' . $row, 'Record Count');
        
        $headerStyle = [
            'font' => new Font(['bold' => true, 'color' => ['rgb' => 'FFFFFF']]),
            'fill' => new Fill(['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCD34D']])
        ];
        
        $sheet->getCellByColumnAndRow(1, $row)->applyFromArray($headerStyle);
        $sheet->getCellByColumnAndRow(2, $row)->applyFromArray($headerStyle);
        
        $row++;
        foreach ($modules as $module) {
            $sheet->setCellValue('A' . $row, $module['name']);
            $sheet->setCellValue('B' . $row, count($module['data']));
            $row++;
        }
        
        // Auto-fit columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
}
