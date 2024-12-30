<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Listings Report</title>
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
            color: #047857;
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
        <h1>Product Listings Report</h1>
        <p>Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Products by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Number of Products</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products_by_category as $category)
                    <tr>
                        <td>{{ $category->category }}</td>
                        <td>{{ number_format($category->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Products by Location</h2>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Number of Products</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products_by_location as $location)
                    <tr>
                        <td>{{ $location->location }}</td>
                        <td>{{ number_format($location->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Number of Products</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_categories as $category)
                    <tr>
                        <td>{{ $category->category }}</td>
                        <td>{{ number_format($category->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Recent Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Date Listed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->user->name }}</td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->created_at->format('M d, Y') }}</td>
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
