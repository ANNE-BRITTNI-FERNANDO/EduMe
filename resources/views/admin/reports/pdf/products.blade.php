<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Products Analytics Report</title>
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
        <h1>Products Analytics Report</h1>
    </div>

    <div class="date-range">
        {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
    </div>

    <div class="metrics">
        <div class="metric">
            <strong>Total Products:</strong> {{ number_format($total_products) }}
        </div>
        <div class="metric">
            <strong>New Products:</strong> {{ number_format($new_products) }}
            <span>({{ $products_growth >= 0 ? '+' : '' }}{{ number_format($products_growth, 1) }}% vs previous period)</span>
        </div>
        <div class="metric">
            <strong>Sold Products:</strong> {{ number_format($sold_products) }}
        </div>
        <div class="metric">
            <strong>Total Revenue:</strong> LKR {{ number_format($total_revenue, 2) }}
        </div>
    </div>

    <h2 class="section-title">Category Distribution</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Products</th>
                <th>% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($category_distribution as $category)
            <tr>
                <td>{{ $category->category }}</td>
                <td>{{ number_format($category->count) }}</td>
                <td>{{ number_format(($category->count / $total_products) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Daily New Products</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>New Products</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily_new_products as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                <td>{{ number_format($day->count) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section-title">Recent Products</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Listed By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_products as $product)
            <tr>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->category }}</td>
                <td>LKR {{ number_format($product->price, 2) }}</td>
                <td>{{ $product->is_sold ? 'Sold' : 'Available' }}</td>
                <td>{{ $product->user->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
