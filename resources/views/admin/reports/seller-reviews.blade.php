<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Seller Reviews Report</title>
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
            color: #b45309;
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
        .rating {
            color: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Seller Reviews Report</h1>
        <p>Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Average Rating</h3>
                <p>{{ number_format($average_rating, 1) }} / 5.0</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Rating Distribution</h2>
        <table>
            <thead>
                <tr>
                    <th>Rating</th>
                    <th>Number of Reviews</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rating_distribution as $rating)
                    <tr>
                        <td class="rating">
                            {{ str_repeat('★', $rating->rating) }}{{ str_repeat('☆', 5 - $rating->rating) }}
                        </td>
                        <td>{{ number_format($rating->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top Rated Sellers</h2>
        <table>
            <thead>
                <tr>
                    <th>Seller Name</th>
                    <th>Average Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_sellers as $seller)
                    <tr>
                        <td>{{ $seller->seller->name }}</td>
                        <td class="rating">
                            {{ number_format($seller->average_rating, 1) }}
                            ({{ str_repeat('★', round($seller->average_rating)) }}{{ str_repeat('☆', 5 - round($seller->average_rating)) }})
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Recent Reviews</h2>
        <table>
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_reviews as $review)
                    <tr>
                        <td>{{ $review->seller->name }}</td>
                        <td>{{ $review->user->name }}</td>
                        <td class="rating">
                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                        </td>
                        <td>{{ $review->created_at->format('M d, Y') }}</td>
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
