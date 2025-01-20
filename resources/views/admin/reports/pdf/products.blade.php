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
        .sub-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
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
            <div class="metric-label">Total Items</div>
            <div class="metric-value">{{ number_format($total_products + $total_bundles) }}</div>
            <div class="sub-text">
                {{ number_format($total_products) }} Products<br>
                {{ number_format($total_bundles) }} Bundles
            </div>
        </div>

        <div class="metric">
            <div class="metric-label">New Items</div>
            <div class="metric-value">{{ number_format($new_products + $new_bundles) }}</div>
            <div class="sub-text">
                Products: {{ number_format($new_products) }}
                <div class="growth {{ $products_growth < 0 ? 'negative' : '' }}">
                    {{ $products_growth > 0 ? '+' : '' }}{{ number_format($products_growth, 1) }}% vs previous
                </div>
                Bundles: {{ number_format($new_bundles) }}
                <div class="growth {{ $bundles_growth < 0 ? 'negative' : '' }}">
                    {{ $bundles_growth > 0 ? '+' : '' }}{{ number_format($bundles_growth, 1) }}% vs previous
                </div>
            </div>
        </div>

        <div class="metric">
            <div class="metric-label">Sold Items</div>
            <div class="metric-value">{{ number_format($sold_products + $sold_bundles) }}</div>
            <div class="sub-text">
                {{ number_format($sold_products) }} Products<br>
                {{ number_format($sold_bundles) }} Bundles
            </div>
        </div>

        <div class="metric">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">LKR {{ number_format($total_product_revenue + $total_bundle_revenue, 2) }}</div>
            <div class="sub-text">
                Products: LKR {{ number_format($total_product_revenue, 2) }}<br>
                Bundles: LKR {{ number_format($total_bundle_revenue, 2) }}
            </div>
        </div>
    </div>

    <h2 class="section-title">Category Distribution</h2>
    @if($category_distribution->isNotEmpty())
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
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ number_format($category_distribution->sum('count')) }}</td>
                    <td>100%</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">No category data available for the selected period</div>
    @endif

    <h2 class="section-title">Daily New Products</h2>
    @if($daily_new_products->isNotEmpty())
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
    @else
        <div class="no-data">No daily new products data available for the selected period</div>
    @endif

    <h2 class="section-title">Recent Products</h2>
    @if($recent_products->isNotEmpty())
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
    @else
        <div class="no-data">No recent products data available for the selected period</div>
    @endif
</body>
</html>
