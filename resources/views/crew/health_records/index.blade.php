@extends('layouts.master')

@section('page_title', 'My Health Records')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Health Records</h3>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <span class="text-muted me-4">Your medical checkup history</span>
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="bi bi-search fs-2 position-absolute ms-4"></i>
                    <input type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Cari rekam medis" id="health_search" />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="health_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Checkup Date</th>
                            <th>Medical Examiner</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Next Checkup Date</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($healthRecords as $record)
                            @php
                                $status = strtolower((string)($record->status ?? ''));
                                $badgeClass = $status === 'fit' ? 'badge-light-success' : ($status === 'restricted' ? 'badge-light-warning' : 'badge-light-danger');
                                $nextDate = optional($record->next_checkup_date);
                                $isSoon = $nextDate ? $nextDate->isBetween(now(), now()->addDays(30)) : false;
                                $nextDateHtml = $nextDate ? $nextDate->format('d M Y') : '-';
                            @endphp
                            <tr>
                                <td>{{ optional($record->checkup_date)?->format('d M Y') }}</td>
                                <td>{{ $record->medical_examiner }}</td>
                                <td><span class="badge {{ $badgeClass }} text-capitalize">{{ $status ?: '-' }}</span></td>
                                <td>{{ $record->notes ?: '-' }}</td>
                                <td>
                                    @if($nextDate)
                                        <span class="{{ $isSoon ? 'fw-bold text-danger' : '' }}">{{ $nextDateHtml }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($record->attachment_path))
                                        <a href="{{ url('storage/' . ltrim($record->attachment_path, '/')) }}" class="btn btn-sm btn-light-primary" target="_blank">
                                            View Document
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada rekam medis.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function(){
        if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
            var dt = jQuery('#health_table').DataTable({
                pageLength: 10,
                ordering: true,
                language: { url: '' }
            });
            var $search = jQuery('#health_search');
            $search.on('keyup change', function(){
                dt.search(this.value || '').draw();
            });
        }
    })();
</script>
@endpush
