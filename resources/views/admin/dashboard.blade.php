@extends('layouts.master')

@section('page_title', 'Dashboard')

@section('content')
    <div class="mb-5">
        <h1 class="fw-bold fs-2qx mb-3">Dashboard AviaSync</h1>
        <div class="text-gray-600">Flight Crew Scheduling System</div>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-md-4">
            <div class="card card-flush h-100">
                <div class="card-body py-5">
                    <div class="text-gray-500 fw-semibold mb-2"><i class="bi bi-people me-2"></i>Total Crew</div>
                    <div class="fs-2 fw-bold">{{ $totalCrew ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-flush h-100">
                <div class="card-body py-5">
                    <div class="text-gray-500 fw-semibold mb-2"><i class="bi bi-airplane-engines me-2"></i>Active Flights</div>
                    <div class="fs-2 fw-bold">{{ $activeFlights ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-flush h-100">
                <div class="card-body py-5">
                    <div class="text-gray-500 fw-semibold mb-2"><i class="bi bi-shield-check me-2"></i>Crew Fit & Ready</div>
                    <div class="fs-2 fw-bold">{{ $readyCrew ?? '-' }}</div>
                    <div class="text-muted fs-8">Status crew aktif dengan hasil medical "fit".</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold">Aktivitas Terbaru</h3>
            </div>
        </div>
        <div class="card-body py-5">
            @if(!empty($recentLogs ?? []) && count($recentLogs))
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6">
                        <thead>
                        <tr class="text-start text-gray-500 fw-semibold text-uppercase gs-0">
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Method</th>
                            <th>URL</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                        @foreach($recentLogs as $log)
                            <tr>
                                <td>{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ optional($log->user)->name ?? 'Guest' }}</td>
                                <td>
                                    @php
                                        $method = strtoupper($log->method ?? '-');
                                        $badgeClass = match ($method) {
                                            'GET' => 'badge-light-primary',
                                            'POST' => 'badge-light-success',
                                            'PUT', 'PATCH' => 'badge-light-warning',
                                            'DELETE' => 'badge-light-danger',
                                            default => 'badge-light-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $method }}</span>
                                </td>
                                <td class="text-truncate" style="max-width: 420px;">
                                    {{ $log->url }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('admin.system-logs.index') }}" class="btn btn-link text-primary fw-semibold">
                        Lihat Semua Aktivitas <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            @else
                <div class="text-muted">Belum ada aktivitas.</div>
            @endif
        </div>
    </div>
@endsection
