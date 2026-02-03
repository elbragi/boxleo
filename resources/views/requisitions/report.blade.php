<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition Invoice</title>
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2px;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: auto;

        }
        table, th, td {
            border: 1px solid black;
            /* border: none; */
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 12px;

        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
    <row>
        <col>
            <img src="data:image/png;base64,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" alt="Company Logo" style="max-width: 150px;">
            <p>Requisition</p>
        </col>
    </row>
</div>

        
        @php
            $grandTotal = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <!-- <th>#</th> -->
                    <th>Requisition ID</th>
                    <th>Requesting Officer</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                    <th>Department</th>
                   
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Payment Code</th>
                  
                </tr>
            </thead>
            <tbody>
                @foreach($requisitions as $requisition)
                    @foreach($requisition->items as $index => $item)
                    @php
                        $grandTotal += $item->total_cost;
                    @endphp
                    <tr>
                        <!-- <td>{{ $index + 1 }}</td> -->
                        <td>{{ $requisition->id }}</td>
                        <td>{{ optional($requisition->user)->firstname }} {{ optional($requisition->user)->lastname }}</td>
                        <td>{{ $requisition->status }}</td>
                        <td>{{ $requisition->special_instructions }}</td>
                        <td>{{ optional(optional($requisition->user)->department)->name ?? 'N/A' }}</td>
                     

                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{{ number_format($item->total_cost, 2) }}</td>
                        <td>{{ $requisition->pop }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            
            <p>Grand Total: {{ number_format($grandTotal, 2) }}</p>
        </div>
    </div>
</body>
</html>