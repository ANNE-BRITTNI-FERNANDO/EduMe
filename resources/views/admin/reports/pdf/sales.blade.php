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
        <h1>Sales Report</h1>
    </div>

    <div class="date-range">
        {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
    </div>

    <div class="metrics">
        <div class="metric">
            <strong>Total Revenue:</strong> LKR {{ number_format($total_revenue, 2) }}
        </div>
        <div class="metric">
            <strong>Total Orders:</strong> {{ number_format($total_orders) }}
        </div>
        <div class="metric">
            <strong>Average Order Value:</strong> LKR {{ number_format($total_orders > 0 ? $total_revenue / $total_orders : 0, 2) }}
        </div>
    </div>

    <h2 class="section-title">Sales by Category</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Number of Sales</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($category_sales as $category)
            <tr>
                <td>{{ $category->category }}</td>
                <td>{{ $category->count }}</td>
                <td>LKR {{ number_format($category->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Payment Methods</h2>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th>Number of Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment_methods as $method)
            <tr>
                <td>{{ $method->payment_method }}</td>
                <td>{{ $method->count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Recent Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->user->name }}</td>
                <td>LKR {{ number_format($order->total_amount, 2) }}</td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
