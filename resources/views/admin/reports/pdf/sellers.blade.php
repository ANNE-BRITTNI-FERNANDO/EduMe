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
        <div class="metric">
            <strong>Total Sellers:</strong> {{ number_format($total_sellers) }}
        </div>
        <div class="metric">
            <strong>New Sellers:</strong> {{ number_format($new_sellers) }}
            <span>({{ $sellers_growth >= 0 ? '+' : '' }}{{ number_format($sellers_growth, 1) }}% vs previous period)</span>
        </div>
        <div class="metric">
            <strong>Active Sellers:</strong> {{ number_format($active_sellers) }}
        </div>
    </div>

    <h2 class="section-title">Top Sellers</h2>
    <table>
        <thead>
            <tr>
                <th>Seller</th>
                <th>Products Sold</th>
                <th>Bundles Sold</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_sellers as $seller)
            <tr>
                <td>{{ $seller->name }}</td>
                <td>{{ number_format($seller->products_sold) }}</td>
                <td>{{ number_format($seller->bundles_sold) }}</td>
                <td>LKR {{ number_format($seller->product_revenue + $seller->bundle_revenue, 2) }}</td>
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
                <th>Seller</th>
                <th>Products</th>
                <th>Bundles</th>
                <th>Joined Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_sellers as $seller)
            <tr>
                <td>{{ $seller->name }}</td>
                <td>{{ number_format($seller->products_count) }}</td>
                <td>{{ number_format($seller->bundles_count) }}</td>
                <td>{{ $seller->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
