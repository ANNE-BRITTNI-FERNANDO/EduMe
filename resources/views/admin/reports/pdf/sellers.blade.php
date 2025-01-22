<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sellers Analytics Report</title>
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
        }
        .metric {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .section-title {
            margin: 20px 0 10px;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sellers Analytics Report</h1>
    </div>

    <div class="date-range">
        {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
    </div>

    <div class="metrics">
        <h2 class="section-title">Overview</h2>
        <div class="metric">
            <strong>Total Sellers:</strong> {{ number_format($total_sellers) }}
        </div>
        <div class="metric">
            <strong>New Sellers:</strong> {{ number_format($new_sellers) }}
            <span>({{ $sellers_growth >= 0 ? '+' : '' }}{{ number_format($sellers_growth, 1) }}% vs previous period)</span>
        </div>
        <div class="metric">
            <strong>Active Sellers:</strong> {{ number_format($active_sellers) }}
            <span>({{ $total_sellers > 0 ? number_format(($active_sellers / $total_sellers) * 100, 1) : 0 }}% activity rate)</span>
        </div>
        <div class="metric">
            <strong>Average Products per Seller:</strong> {{ number_format($avg_products_per_seller, 1) }}
        </div>
    </div>

    <div class="metrics">
        <h2 class="section-title">Performance Metrics</h2>
        <div class="metric">
            <strong>Response Rate:</strong> {{ number_format($seller_metrics['response_rate'], 1) }}%
        </div>
        <div class="metric">
            <strong>Order Fulfillment Rate:</strong> {{ number_format($seller_metrics['fulfillment_rate'], 1) }}%
        </div>
        <div class="metric">
            <strong>Customer Satisfaction:</strong> {{ number_format($seller_metrics['satisfaction_rate'], 1) }}%
        </div>
    </div>

    <h2 class="section-title">Top Sellers</h2>
    <table>
        <thead>
            <tr>
                <th>Seller</th>
                <th>Products</th>
                <th>Active Products</th>
                <th>Revenue</th>
                <th>Avg Rating</th>
                <th>Response Rate</th>
                <th>Fulfillment Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_sellers as $seller)
            <tr>
                <td>{{ $seller->name }}</td>
                <td>{{ number_format($seller->total_products) }}</td>
                <td>{{ number_format($seller->active_products) }}</td>
                <td>LKR {{ number_format($seller->total_revenue ?? 0, 2) }}</td>
                <td>{{ number_format($seller->avg_rating ?? 0, 1) }}</td>
                <td>{{ number_format($seller->response_rate ?? 0, 1) }}%</td>
                <td>{{ number_format($seller->fulfillment_rate ?? 0, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Daily New Sellers</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>New Sellers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily_new_sellers as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                <td>{{ number_format($day->count) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Recent Sellers</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>New Sellers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily_new_sellers as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                <td>{{ number_format($day->count) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
