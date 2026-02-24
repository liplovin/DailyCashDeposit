<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Deposit Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1a3a52;
        }
        
        .logo-container {
            margin-bottom: 12px;
        }
        
        .header h1 {
            margin: 8px 0 5px 0;
            font-size: 20px;
            font-weight: bold;
            color: #1a3a52;
            letter-spacing: 0.5px;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #555;
        }
        
        .report-info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section h2 {
            font-size: 13px;
            margin: 12px 0 8px 0;
            border-bottom: 2px solid #1a3a52;
            padding-bottom: 5px;
            color: #1a3a52;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        th {
            background-color: #FCD34D;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #999;
            color: #333;
            font-size: 10px;
        }
        
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 2px solid #1a3a52;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if($logoBase64)
            <div class="logo-container">
                <img src="{{ $logoBase64 }}" style="height: 75px; max-width: 90%; width: auto;">
            </div>
        @endif
        <h1>DAILY DEPOSIT REPORT</h1>
        <div class="report-info">
            <p>Report Date: <strong>{{ $reportDate }}</strong></p>
            <p>Generated: {{ date('F d, Y h:i A') }}</p>
        </div>
    </div>

    <!-- Module Details -->
    @foreach($modules as $module)
        @if(count($module['data']) > 0)
            <div class="section">
                <h2>{{ $module['name'] }} Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th>{{ $module['name'] }}</th>
                            <th>{{ ucfirst(str_replace('_', ' ', $module['accField'])) }}</th>
                            <th>Acq Date</th>
                            <th>Maturity Date</th>
                            <th class="text-right">Beginning Balance</th>
                            <th class="text-right">Collection</th>
                            <th class="text-right">Disbursement</th>
                            <th class="text-right">Ending Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($module['data'] as $item)
                            <tr>
                                <td>{{ $item[$module['key']] ?? '' }}</td>
                                <td>{{ $item[$module['accField']] ?? '' }}</td>
                                <td>
                                    @if(isset($item['acquisition_date']))
                                        {{ date('M d, Y', strtotime($item['acquisition_date'])) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(isset($item['maturity_date']))
                                        {{ date('M d, Y', strtotime($item['maturity_date'])) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-right">PHP {{ number_format($item['beginning_balance'] ?? 0, 2) }}</td>
                                <td class="text-right">PHP {{ number_format($item['collection'] ?? 0, 2) }}</td>
                                <td class="text-right">PHP {{ number_format($item['disbursement'] ?? 0, 2) }}</td>
                                <td class="text-right">PHP {{ number_format($item['ending_balance'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p><strong>INTRA STRATA INSURANCE CORPORATION</strong></p>
        <p style="font-size: 8px;">Generated: {{ date('F d, Y \a\t h:i A') }} | Daily Deposit Management System</p>
    </div>
</body>
</html>
