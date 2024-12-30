<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Summary Report</title>
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
            color: #7c3aed;
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
        <h1>User Summary Report</h1>
        <p>Generated on {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p>{{ number_format($total_users) }}</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>User Types</h2>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Number of Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user_types as $type)
                    <tr>
                        <td>{{ ucfirst($type->role) }}</td>
                        <td>{{ number_format($type->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Registration Trend (Last 30 Days)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>New Registrations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registration_trend as $trend)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($trend->date)->format('M d, Y') }}</td>
                        <td>{{ number_format($trend->count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Recent Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
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
