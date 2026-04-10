@extends('layouts.master')

@section('page_title', 'My Certifications')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Certifications</h3>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <span class="text-muted me-4">Your certification records</span>
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="bi bi-search fs-2 position-absolute ms-4"></i>
                    <input type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Cari nama / nomor sertifikat" id="cert_search" />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="cert_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Certificate Name</th>
                            <th>Certificate Number</th>
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($certifications as $cert)
                            @php
                                $isExpiringSoon = optional($cert->expiry_date)?->isBetween(now(), now()->addDays(30));
                                $isExpired = optional($cert->expiry_date)?->isBefore(now());
                                $badgeClass = $isExpired ? 'badge-light-danger' : ($isExpiringSoon ? 'badge-light-warning' : 'badge-light-success');
                                $statusLabel = $isExpired ? 'expired' : ($isExpiringSoon ? 'warning' : 'valid');
                            @endphp
                            <tr>
                                <td>{{ $cert->certificate_name }}</td>
                                <td>{{ $cert->certificate_number }}</td>
                                <td>{{ optional($cert->issue_date)?->format('d M Y') }}</td>
                                <td>{{ optional($cert->expiry_date)?->format('d M Y') }}</td>
                                <td><span class="badge {{ $badgeClass }} text-capitalize">{{ $statusLabel }}</span></td>
                                <td>
                                    @if(!empty($cert->attachment_path))
                                        <a href="{{ url('storage/' . ltrim($cert->attachment_path, '/')) }}" class="btn btn-sm btn-light-primary" target="_blank">
                                            View Document
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada sertifikasi.</td>
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
            var dt = jQuery('#cert_table').DataTable({
                pageLength: 10,
                ordering: true,
                language: { url: '' }
            });
            var $search = jQuery('#cert_search');
            $search.on('keyup change', function(){
                dt.search(this.value || '').draw();
            });
        }
    })();
</script>
@endpush
