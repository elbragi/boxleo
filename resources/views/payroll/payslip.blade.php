<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $payslip->is_rider ? $payslip->rider_name : ($employee ? ($employee->firstname . ' ' . $employee->lastname) : 'Employee') }} - Payslip</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 5px;
            min-height: 570px; /* Force min-height for A5 */
        }
        .header-table {
            width: 100%;
            margin-bottom: 25px; /* Added more space */
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            color: #1976D2; /* Unified brand blue */
            font-size: 16px;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 1px 0;
            font-size: 9px;
            color: #555;
        }
        .payslip-title {
            text-align: right;
            vertical-align: top;
        }
        .payslip-title h3 {
            margin: 0;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .payslip-title h1 {
            margin: 3px 0 0 0;
            font-size: 15px;
            color: #1976D2;
        }
        
        /* Employee Summary */
        .summary-section {
            margin-bottom: 25px; /* Added more space */
        }
        .section-title {
            color: #000; /* Black for better contrast */
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-left: 3px solid #000;
            padding-left: 5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 3px 0;
            width: 50%;
            font-size: 10px;
        }
        .label {
            color: #666;
            width: 90px;
            display: inline-block;
        }
        .value {
            color: #000;
            font-weight: bold;
        }

        /* Income Details */
        .details-container {
            width: 100%;
            margin-bottom: 25px; /* Added more space */
            display: table;
            table-layout: fixed;
        }
        .col-left {
            display: table-cell;
            width: 48%;
            padding-right: 2%;
            vertical-align: top;
        }
        .col-right {
            display: table-cell;
            width: 48%;
            padding-left: 2%;
            vertical-align: top;
            border-left: 1px solid #eee;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th {
            text-align: left;
            padding: 5px;
            border-bottom: 1px solid #1976D2;
            font-weight: bold;
            color: #1976D2;
            font-size: 11px;
        }
        
        /* Color-code columns */
        .col-left .details-table th {
            color: #1976D2; /* Earnings Blue */
            border-bottom-color: #1976D2;
        }
        .col-right .details-table th {
            color: #d32f2f; /* Deductions Red */
            border-bottom-color: #d32f2f;
        }
        
        .details-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .amount-col {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 1px solid #ddd;
            border-bottom: none;
            padding-top: 5px;
            font-weight: bold;
            color: #1976D2; /* Unified brand blue */
        }
        .col-right .total-row td {
            color: #d32f2f; /* Total deductions Red */
        }
        
        /* Net Pay Box */
        .net-pay-box {
            background-color: #f5f7fa;
            border: 1px solid #1976D2;
            padding: 12px;
            margin-top: 25px; /* Added more space */
        }
        .net-pay-table {
            width: 100%;
        }
        .net-label {
            font-size: 12px;
            font-weight: bold;
            color: #1976D2;
        }
        .net-desc {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .net-amount {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #1976D2;
        }
        
        .amount-words {
            margin-top: 15px;
            text-align: right;
            font-size: 10px;
            font-style: italic;
            color: #555;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        
        /* Logo placeholder if image fails */
        .logo-placeholder {
            width: 150px;
            height: 60px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="60%" class="company-info">
                   <img src="{{ public_path('assets/img/logo.png') }}" alt="Boxleo" style="height: 50px; margin-bottom: 10px;">
                    <h2>Boxleo Courier & Fulfillment Services</h2>
                    <p>Akshrap Godowns, Gate A-2, JKIA Junction</p>
                    <p>Mombasa Road, Nairobi, Kenya</p>
                    <p>Email: info@boxleocourier.com | Tel: +254 711 082 433</p>
                </td>
                <td width="40%" class="payslip-title">
                    @if($payslip->is_rider && $payslip->start_date && $payslip->end_date)
                        <h3 style="text-transform: uppercase;">Payslip for {{ \Carbon\Carbon::parse($payslip->start_date)->format('M jS') }} to {{ \Carbon\Carbon::parse($payslip->end_date)->format('M jS') }}</h3>
                    @else
                        <h3>Payslip For the Month</h3>
                        <h1>{{ \Carbon\Carbon::createFromDate($payslip->year, $payslip->month, 1)->format('F Y') }}</h1>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Employee Summary -->
        <div class="summary-section">
            <div class="section-title">{{ $payslip->is_rider ? 'RIDER PAY SUMMARY' : 'EMPLOYEE PAY SUMMARY' }}</div>
            <table class="summary-table">
                <tr>
                    <td><span class="label">{{ $payslip->is_rider ? 'Rider Name' : 'Employee Name' }}</span> <span class="value">: {{ $payslip->is_rider ? $payslip->rider_name : ($employee ? ($employee->firstname . ' ' . $employee->lastname) : 'Employee') }}</span></td>
                    <td><span class="label">{{ $payslip->is_rider ? 'Rider ID' : 'Employee ID' }}</span> <span class="value">: {{ $employee ? ($employee->staffID ?? $employee->id) : 'RIDER' }}</span></td>
                </tr>
                @if(!$payslip->is_rider)
                <tr>
                    <td><span class="label">Department</span> <span class="value">: {{ $employee ? ($employee->department->name ?? 'N/A') : 'N/A' }}</span></td>
                    <td><span class="label">Designation</span> <span class="value">: {{ $employee ? ($employee->designation->name ?? 'N/A') : 'N/A' }}</span></td>
                </tr>
                @else
                <tr>
                    <td><span class="label">Deliveries</span> <span class="value">: {{ $payslip->deliveries_count }}</span></td>
                    <td><span class="label">Rate/Delivery</span> <span class="value">: KES {{ number_format($payslip->rate_per_delivery, 2) }}</span></td>
                </tr>
                @endif
                <tr>
                    <td><span class="label">Branch</span> <span class="value">: {{ $employee ? ($employee->unit->name ?? 'N/A') : 'Main Branch' }}</span></td>
                    <td><span class="label">Payment Mode</span> <span class="value">: {{ $payslip->payment_mode ?? ($employee ? ($employee->user_detail->payment_mode ?? 'Bank Transfer') : 'Mobile Money') }}</span></td>
                </tr>
                 <tr>
                    <td><span class="label">Bank</span> <span class="value">: {{ $payslip->bank ?? ($employee ? ($employee->user_detail->bank_name ?? 'N/A') : 'N/A') }}</span></td>
                    <td><span class="label">Account No</span> <span class="value">: {{ $payslip->bank_account ?? ($employee ? ($employee->user_detail->bank_account ?? 'N/A') : 'N/A') }}</span></td>
                </tr>
            </table>
        </div>

        <!-- Income Details -->
        <div class="section-title">Income Details</div>
        <div class="details-container">
            <!-- Earnings -->
            <div class="col-left">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Earnings</th>
                            <th class="amount-col">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $payslip->is_rider ? 'Delivery Pay' : 'Basic Pay' }}</td>
                            <td class="amount-col">{{ number_format($payslip->basic_pay ?? 0, 2) }}</td>
                        </tr>
                        @foreach($payslip->earnings as $earning)
                        <tr>
                            <td>{{ $earning->label }}</td>
                            <td class="amount-col">{{ number_format($earning->amount, 2) }}</td>
                        </tr>
                        @endforeach
                        
                        <tr class="total-row">
                            <td>Gross Earnings</td>
                            <td class="amount-col">KES {{ number_format($payslip->gross_pay ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions -->
            <div class="col-right">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Deductions</th>
                            <th class="amount-col">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($payslip->statutoryDeductions)
                            <!-- Statutory Deductions -->
                            @if(($payslip->statutoryDeductions->nssf ?? 0) > 0)
                            <tr>
                                <td>NSSF</td>
                                <td class="amount-col">{{ number_format($payslip->statutoryDeductions->nssf, 2) }}</td>
                            </tr>
                            @endif
                            @if(($payslip->statutoryDeductions->nhif ?? 0) > 0)
                            <tr>
                                <td>NHIF</td>
                                <td class="amount-col">{{ number_format($payslip->statutoryDeductions->nhif, 2) }}</td>
                            </tr>
                            @endif
                            @if(($payslip->statutoryDeductions->paye ?? 0) > 0)
                            <tr>
                                <td>PAYE (Tax)</td>
                                <td class="amount-col">{{ number_format($payslip->statutoryDeductions->paye, 2) }}</td>
                            </tr>
                            @endif
                            @if(($payslip->statutoryDeductions->housing_levy ?? 0) > 0)
                            <tr>
                                <td>Housing Levy</td>
                                <td class="amount-col">{{ number_format($payslip->statutoryDeductions->housing_levy, 2) }}</td>
                            </tr>
                            @endif
                        @endif
                        
                        @foreach($payslip->otherDeductions as $deduction)
                        <tr>
                            <td>
                                {{ $deduction->label }}
                                @if($deduction->comment)
                                <div style="font-size: 8px; font-weight: normal; font-style: italic; color: #d32f2f;">({{ $deduction->comment }})</div>
                                @endif
                            </td>
                            <td class="amount-col">{{ number_format($deduction->amount, 2) }}</td>
                        </tr>
                        @endforeach

        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td class="amount-col">KES {{ number_format($payslip->total_deductions ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Net Payable -->
        <div class="net-pay-box">
            <table class="net-pay-table">
                <tr>
                    <td align="left">
                        <div class="net-label">Total Net Payable</div>
                        <div class="net-desc">Gross Earnings - Total Deductions</div>
                    </td>
                    <td align="right">
                        <div class="net-amount">KES {{ number_format($payslip->net_pay ?? 0, 2) }}</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="amount-words">
            Amount in words: {{ $payslip->net_pay_words ?? 'Kenya Shillings Only' }}
        </div>
    </div>
</body>
</html>
