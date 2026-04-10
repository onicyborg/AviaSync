@extends('layouts.master')

@section('page_title', 'System Logs')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">System Logs</h3>
            <span class="text-muted">Pantau aktivitas request yang terekam oleh AviaSync.</span>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed" id="systemLogsTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Method</th>
                            <th>URL</th>
                            <th>IP Address</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ optional($log->created_at)->format('d M Y H:i:s') }}</td>
                                <td>{{ optional($log->user)->name ?? 'Guest' }}</td>
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
                                <td><span class="badge {{ $badgeClass }}">{{ $method }}</span></td>
                                <td class="text-truncate" style="max-width: 460px;">
                                    {{ $log->url }}
                                </td>
                                <td>{{ $log->ip_address ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-sm btn-light-primary btn-log-detail"
                                        data-bs-toggle="modal"
                                        data-bs-target="#logDetailModal"
                                        data-user="{{ optional($log->user)->name ?? 'Guest' }}"
                                        data-email="{{ optional($log->user)->email ?? '-' }}"
                                        data-method="{{ strtoupper($log->method ?? '-') }}"
                                        data-url="{{ $log->url }}"
                                        data-action="{{ $log->action }}"
                                        data-table="{{ $log->table_name }}"
                                        data-record="{{ $log->record_id }}"
                                        data-ip="{{ $log->ip_address ?? '-' }}"
                                        data-created="{{ optional($log->created_at)->format('d M Y H:i:s') }}"
                                        data-payload='@json($log->request_payload ?? [])'
                                        data-old='@json($log->old_values ?? [])'
                                        data-new='@json($log->new_values ?? [])'>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">Belum ada log tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Log</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-muted fs-8">User</div>
                            <div class="fw-semibold" id="logDetailUser">-</div>
                            <div class="text-gray-600" id="logDetailEmail">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted fs-8">Waktu</div>
                            <div class="fw-semibold" id="logDetailCreated">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted fs-8">Method</div>
                            <div class="fw-semibold">
                                <span class="badge badge-light-secondary" id="logDetailMethodBadge">-</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted fs-8">IP Address</div>
                            <div class="fw-semibold" id="logDetailIp">-</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted fs-8">URL</div>
                            <div class="fw-semibold" id="logDetailUrl">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted fs-8">Action</div>
                            <div class="fw-semibold" id="logDetailAction">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted fs-8">Table / Record</div>
                            <div class="fw-semibold" id="logDetailTable">-</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h6 class="fw-bold">Payload</h6>
                        <pre class="bg-light rounded p-3" id="logDetailPayload">-</pre>
                    </div>
                    <div class="mt-4">
                        <h6 class="fw-bold">Old Values</h6>
                        <pre class="bg-light rounded p-3" id="logDetailOld">-</pre>
                    </div>
                    <div class="mt-4">
                        <h6 class="fw-bold">New Values</h6>
                        <pre class="bg-light rounded p-3" id="logDetailNew">-</pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.DataTable) {
                new window.DataTable('#systemLogsTable');
            }

            const modal = document.getElementById('logDetailModal');
            const detailButtons = document.querySelectorAll('.btn-log-detail');

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || '-';
            };

            const formatJson = (data) => {
                try {
                    const parsed = typeof data === 'string' ? JSON.parse(data) : data;
                    if (!parsed || Object.keys(parsed).length === 0) return '-';
                    return JSON.stringify(parsed, null, 2);
                } catch (e) {
                    return '-';
                }
            };

            const methodBadgeClass = (method) => {
                switch (method) {
                    case 'GET':
                        return 'badge-light-primary';
                    case 'POST':
                        return 'badge-light-success';
                    case 'PUT':
                    case 'PATCH':
                        return 'badge-light-warning';
                    case 'DELETE':
                        return 'badge-light-danger';
                    default:
                        return 'badge-light-secondary';
                }
            };

            detailButtons.forEach(button => {
                button.addEventListener('click', () => {
                    setText('logDetailUser', button.getAttribute('data-user'));
                    setText('logDetailEmail', button.getAttribute('data-email'));
                    setText('logDetailIp', button.getAttribute('data-ip'));
                    setText('logDetailUrl', button.getAttribute('data-url'));
                    setText('logDetailAction', button.getAttribute('data-action'));
                    setText('logDetailTable', `${button.getAttribute('data-table')} / ${button.getAttribute('data-record')}`);
                    setText('logDetailCreated', button.getAttribute('data-created'));

                    const method = button.getAttribute('data-method') || '-';
                    const badge = document.getElementById('logDetailMethodBadge');
                    if (badge) {
                        badge.textContent = method;
                        badge.className = `badge ${methodBadgeClass(method)}`;
                    }

                    document.getElementById('logDetailPayload').textContent = formatJson(button.getAttribute('data-payload'));
                    document.getElementById('logDetailOld').textContent = formatJson(button.getAttribute('data-old'));
                    document.getElementById('logDetailNew').textContent = formatJson(button.getAttribute('data-new'));
                });
            });
        });
    </script>
@endpush
