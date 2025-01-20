<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>User Analytics Report</title>
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
        <h1>User Analytics Report</h1>
    </div>

    <div class="date-range">
        {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
    </div>

    <div class="metrics">
        <div class="metric">
            <strong>Total Users:</strong> {{ number_format($total_users) }}
        </div>
        <div class="metric">
            <strong>New Users:</strong> {{ number_format($new_users) }}
            <span>({{ $user_growth >= 0 ? '+' : '' }}{{ number_format($user_growth, 1) }}% vs previous period)</span>
        </div>
        <div class="metric">
            <strong>Active Users:</strong> {{ number_format($active_users) }}
            <span>({{ $active_users_growth >= 0 ? '+' : '' }}{{ number_format($active_users_growth, 1) }}% vs previous period)</span>
        </div>
    </div>

    <h2 class="section-title">User Engagement</h2>
    <div class="metrics">
        <div class="metric">
            <strong>Engagement Rate:</strong> {{ number_format($engagement_rate, 1) }}%
        </div>
        <div class="metric">
            <strong>Total Active Users:</strong> {{ number_format($total_active) }}
        </div>
        <div class="metric">
            <strong>Active Users in Period:</strong> {{ number_format($period_active) }}
        </div>
    </div>

    <h2 class="section-title">Daily New Users</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>New Users</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily_new_users as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                <td>{{ number_format($day->count) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
