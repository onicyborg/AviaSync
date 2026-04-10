<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? 'Flight Hours Summary' }}</title>
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
    <h2>{{ $meta['title'] ?? 'Flight Hours Summary' }}</h2>
    <div class="meta">
        <div>Periode: {{ $meta['period'] ?? 'All Time' }}</div>
        <div>Generated at: {{ optional($meta['generated_at'] ?? null)->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Nama Crew</th>
                <th>Posisi</th>
                <th>Base</th>
                <th>Total Hours</th>
                <th>Status</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['employee_id'] ?? '-' }}</td>
                    <td>{{ $row['name'] ?? '-' }}</td>
                    <td>{{ $row['position'] ?? '-' }}</td>
                    <td>{{ $row['base_location'] ?? '-' }}</td>
                    <td>{{ number_format($row['total_hours'] ?? 0, 1) }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                    <td>{{ $row['updated_at'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
