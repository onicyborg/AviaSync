<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? 'Flight Status Overview' }}</title>
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
    <h2>{{ $meta['title'] ?? 'Flight Status Overview' }}</h2>
    <div class="meta">
        <div>Periode: {{ $meta['period'] ?? 'All Time' }}</div>
        <div>Generated at: {{ optional($meta['generated_at'] ?? null)->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Flight</th>
                <th>Route</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Status</th>
                <th>Crew Assigned</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['flight_number'] ?? '-' }}</td>
                    <td>{{ $row['route'] ?? '-' }}</td>
                    <td>{{ $row['departure_time'] ?? '-' }}</td>
                    <td>{{ $row['arrival_time'] ?? '-' }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                    <td>{{ $row['crew_count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
