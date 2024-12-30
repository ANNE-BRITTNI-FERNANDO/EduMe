<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .date-range {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .metrics {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .metric {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin: 10px 0;
        }
        .metric-label {
            color: #718096;
            font-size: 14px;
        }
        .growth {
            color: #48bb78;
            font-size: 12px;
        }
        .growth.negative {
            color: #f56565;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 600;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tfoot {
            font-weight: bold;
            background-color: #f7fafc;
        }
        .section-title {
            margin: 30px 0 15px;
            color: #2d3748;
            font-size: 20px;
            font-weight: 600;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #718096;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
    </div>

    <div class="date-range">
        {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
    </div>

    <div class="metrics">
        <div class="metric">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">LKR {{ number_format($total_revenue, 2) }}</div>
        </div>
        <div class="metric">
            <div class="metric-label">Total Orders</div>
            <div class="metric-value">{{ number_format($total_orders) }}</div>
        </div>
    </div>

    <h2 class="section-title">Revenue Trend</h2>
    @if($revenue_trend->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenue_trend as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y') }}</td>
                        <td>LKR {{ number_format((float)$item['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>LKR {{ number_format($revenue_trend->sum('revenue'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No revenue data available for the selected period</div>
    @endif

    <h2 class="section-title">Sales by Location</h2>
    @if($location_sales->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Orders</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($location_sales as $item)
                    <tr>
                        <td>{{ $item['location'] }}</td>
                        <td>{{ number_format($item['count']) }}</td>
                        <td>LKR {{ number_format((float)$item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ number_format($location_sales->sum('count')) }}</td>
                    <td>LKR {{ number_format($location_sales->sum('total'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No location data available for the selected period</div>
    @endif

    <h2 class="section-title">Recent Orders</h2>
    @if($orders->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>LKR {{ number_format($order->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No orders available for the selected period</div>
    @endif
</body>
</html>
