<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $employee->firstname }} {{ $employee->lastname }} - Payslip</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            color: #d32f2f; /* Red company color */
            font-size: 20px;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #555;
        }
        .payslip-title {
            text-align: right;
            vertical-align: top;
        }
        .payslip-title h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .payslip-title h1 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #000;
        }
        
        /* Employee Summary */
        .summary-section {
            margin-bottom: 20px;
        }
        .section-title {
            color: #d32f2f;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 4px 0;
            width: 25%;
            font-size: 11px;
        }
        .label {
            color: #666;
            width: 100px;
            display: inline-block;
        }
        .value {
            color: #000;
            font-weight: normal;
        }

        /* Income Details */
        .details-container {
            width: 100%;
            margin-bottom: 20px;
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
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
            font-weight: normal;
            color: #666;
        }
        .details-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
        }
        .amount-col {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 1px solid #ddd;
            border-bottom: none;
            padding-top: 10px;
            font-weight: bold;
        }
        
        /* Net Pay Box */
        .net-pay-box {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            margin-top: 20px;
        }
        .net-pay-table {
            width: 100%;
        }
        .net-label {
            font-size: 14px;
            color: #666;
        }
        .net-desc {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }
        .net-amount {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            color: #333;
        }
        
        .amount-words {
            margin-top: 15px;
            text-align: right;
            font-size: 11px;
            font-style: italic;
            color: #555;
            border-top: 1px solid #eee;
            padding-top: 10px;
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
                    <h3>Payslip For the Month</h3>
                    <h1>{{ date('F Y') }}</h1>
                </td>
            </tr>
        </table>

        <!-- Employee Summary -->
        <div class="summary-section">
            <div class="section-title">Employee Pay Summary </div>
            <table class="summary-table">
                <tr>
                    <td><span class="label">Employee Name</span> <span class="value">: {{ $employee->firstname }} {{ $employee->lastname }}</span></td>
                    <td><span class="label">Employee ID</span> <span class="value">: {{ $employee->emp_id ?? $employee->id }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Department</span> <span class="value">: {{ $employee->department->name ?? 'N/A' }}</span></td>
                    <td><span class="label">Designation</span> <span class="value">: {{ $employee->designation->name ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Branch</span> <span class="value">: {{ $employee->unit->name ?? 'N/A' }}</span></td>
                    <td><span class="label">Payment Mode</span> <span class="value">: {{ $employee->employee_detail->payment_mode ?? 'Bank Transfer' }}</span></td>
                </tr>
                 <tr>
                    <td><span class="label">Bank</span> <span class="value">: {{ $employee->employee_detail->bank_name ?? 'N/A' }}</span></td>
                    <td><span class="label">Account No</span> <span class="value">: {{ $employee->employee_detail->bank_account ?? 'N/A' }}</span></td>
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
                            <td>Basic Pay</td>
                            <td class="amount-col">{{ number_format($employee->employee_salary->basic_salary ?? 0, 2) }}</td>
                        </tr>
                        @foreach($employee->earnings as $earning)
                        <tr>
                            <td>{{ $earning->earningType->name ?? 'Allowance' }}</td>
                            <td class="amount-col">{{ number_format($earning->amount, 2) }}</td>
                        </tr>
                        @endforeach
                        
                        <!-- Spacers to align height if needed -->
                        @if($employee->earnings->count() < 3)
                            <tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>
                        @endif
                        
                        <tr class="total-row">
                            <td>Gross Earnings</td>
                            <td class="amount-col">KES {{ number_format(($employee->employee_salary->basic_salary ?? 0) + $employee->earnings->sum('amount'), 2) }}</td>
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
                        <!-- Statutory Deductions -->
                        @if(($employee->employee_salary->nssf ?? 0) > 0)
                        <tr>
                            <td>NSSF</td>
                            <td class="amount-col">{{ number_format($employee->employee_salary->nssf, 2) }}</td>
                        </tr>
                        @endif
                        @if(($employee->employee_salary->nhif ?? 0) > 0)
                        <tr>
                            <td>NHIF</td>
                            <td class="amount-col">{{ number_format($employee->employee_salary->nhif, 2) }}</td>
                        </tr>
                        @endif
                        @if(($employee->employee_salary->paye ?? 0) > 0)
                        <tr>
                            <td>PAYE (Tax)</td>
                            <td class="amount-col">{{ number_format($employee->employee_salary->paye, 2) }}</td>
                        </tr>
                        @endif
                        
                        @foreach($employee->deductions as $deduction)
                        <tr>
                            <td>{{ $deduction->deductionType->name ?? 'Deduction' }}</td>
                            <td class="amount-col">{{ number_format($deduction->amount, 2) }}</td>
                        </tr>
                        @endforeach

                         <!-- Spacers to align height -->
                        @if($employee->deductions->count() < 1)
                            <tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>
                        @endif

                        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td class="amount-col">KES {{ number_format(($employee->employee_salary->nssf ?? 0) + ($employee->employee_salary->nhif ?? 0) + ($employee->employee_salary->paye ?? 0) + $employee->deductions->sum('amount'), 2) }}</td>
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
                        <div class="net-amount">KES {{ number_format($employee->employee_salary->net_salary ?? 0, 2) }}</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="amount-words">
            Amount in words: {{ $employee->employee_salary->net_salary_words ?? 'Kenya Shillings Only' }}
        </div>
    </div>
</body>
</html>
