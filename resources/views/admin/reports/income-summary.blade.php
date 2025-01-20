<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1d4ed8;
            margin-bottom: 10px;
        }
        .header p {
            color: #6b7280;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
        }
        .stat-card h3 {
            color: #6b7280;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .stat-card p {
            color: #111827;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Income Summary Report</h1>
        <p>Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>RM {{ number_format($total_revenue, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Monthly Revenue ({{ date('Y') }})</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly_revenue as $revenue)
                    <tr>
                        <td>{{ date('F', mktime(0, 0, 0, $revenue->month, 1)) }}</td>
                        <td>RM {{ number_format($revenue->revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Payment Methods</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Number of Transactions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_methods as $method)
                    <tr>
                        <td>{{ $method->payment_method }}</td>
                        <td>{{ number_format($method->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_transactions as $transaction)
                    <tr>
                        <td>#{{ $transaction->id }}</td>
                        <td>{{ $transaction->user->name }}</td>
                        <td>RM {{ number_format($transaction->total_amount, 2) }}</td>
                        <td>{{ $transaction->payment_method }}</td>
                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report is generated automatically by the system. For any queries, please contact the administrator.</p>
    </div>
</body>
</html>
