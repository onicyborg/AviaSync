@extends('layouts.master')

@section('page_title', 'Reports')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Reports &amp; Exports</h3>
            <span class="text-muted">Unduh ringkasan operasional crew dan penerbangan dalam format PDF atau Excel.</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h4 class="fw-bold mb-0">Filter Laporan</h4>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.reports.export') }}" id="reportForm">
                @csrf
                <input type="hidden" name="export_format" id="exportFormat" value="pdf">
                <div class="row g-5">
                    <div class="col-md-4">
                        <label class="form-label">Report Type</label>
                        <select class="form-select" name="report_type" id="reportType" required data-placeholder="Pilih Jenis Laporan">
                            <option value="">Pilih Jenis Laporan</option>
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-8 mt-2">Gunakan Select2 untuk pencarian cepat sesuai modul laporan.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <div class="border-top mt-5 pt-5 d-flex flex-wrap gap-3">
                    <button type="button" class="btn btn-danger" id="btnExportPdf">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF
                    </button>
                    <button type="button" class="btn btn-success" id="btnExportExcel">
                        <i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.$) {
                window.$('#reportType').select2({
                    placeholder: 'Pilih Jenis Laporan'
                });
            }

            const form = document.getElementById('reportForm');
            const formatInput = document.getElementById('exportFormat');
            const pdfButton = document.getElementById('btnExportPdf');
            const excelButton = document.getElementById('btnExportExcel');

            const submitWithFormat = (format) => {
                formatInput.value = format;
                form.submit();
            };

            pdfButton?.addEventListener('click', () => submitWithFormat('pdf'));
            excelButton?.addEventListener('click', () => submitWithFormat('excel'));
        });
    </script>
@endpush
