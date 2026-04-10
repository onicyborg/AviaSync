<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? 'Compliance Monitoring' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f1f1f; }
        h2 { margin-bottom: 0; }
        .meta { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #dcdcdc; padding: 8px; text-align: left; }
        th { background-color: #f3f3f3; text-transform: uppercase; font-size: 11px; }
        tbody tr:nth-child(even) { background-color: #fafafa; }
    </style>
</head>
<body>
    <h2>{{ $meta['title'] ?? 'Compliance Monitoring' }}</h2>
    <div class="meta">
        <div>Periode: {{ $meta['period'] ?? 'All Time' }}</div>
        <div>Generated at: {{ optional($meta['generated_at'] ?? null)->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Crew</th>
                <th>Employee ID</th>
                <th>Tipe</th>
                <th>Item</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Days Remaining</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['crew_name'] ?? '-' }}</td>
                    <td>{{ $row['employee_id'] ?? '-' }}</td>
                    <td>{{ $row['type'] ?? '-' }}</td>
                    <td>{{ $row['item_name'] ?? '-' }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                    <td>{{ $row['due_date'] ?? '-' }}</td>
                    <td>{{ $row['days_remaining'] ?? '-' }}</td>
                    <td>{{ $row['notes'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
