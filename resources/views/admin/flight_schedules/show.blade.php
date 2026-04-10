@extends('layouts.master')

@section('page_title', 'Detail Flight Schedule')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <a href="{{ route('admin.flight-schedules.index') }}" class="btn btn-light mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <h3 class="fw-bold mb-0">{{ $flight->flight_number }} &mdash; {{ $flight->origin }} → {{ $flight->destination }}</h3>
                @if($isCrewComplete)
                    <span class="badge badge-light-success">Crew Readiness: Ready</span>
                @else
                    <span class="badge badge-light-warning">Crew Readiness: Incomplete</span>
                @endif
            </div>
            <span class="text-muted">Departure {{ optional($flight->departure_time)->format('d M Y H:i') }} • Arrival {{ optional($flight->arrival_time)->format('d M Y H:i') }}</span>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmAutoAssignModal">
                <i class="bi bi-stars me-2"></i>Auto-Assign Crew
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-5 mb-5">
        <div class="col-xl-5">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="fw-bold mb-0">Informasi Penerbangan</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span class="text-muted d-block">Flight Number</span>
                        <span class="fw-bold fs-5">{{ $flight->flight_number }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="text-muted d-block">Route</span>
                        <span class="fw-bold fs-5">{{ $flight->origin }} → {{ $flight->destination }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="text-muted d-block">Departure</span>
                        <span class="fw-semibold">{{ optional($flight->departure_time)->format('d M Y H:i') }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="text-muted d-block">Arrival</span>
                        <span class="fw-semibold">{{ optional($flight->arrival_time)->format('d M Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-muted d-block">Status</span>
                        <span class="badge badge-light-{{ $flight->status === 'active' ? 'success' : ($flight->status === 'cancelled' ? 'danger' : 'primary') }}">
                            {{ ucfirst($flight->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="fw-bold mb-0">Crew Composition</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        @if($isCrewComplete)
                            <div class="symbol symbol-45px symbol-circle me-4">
                                <span class="symbol-label bg-light-success">
                                    <i class="bi bi-check-lg text-success fs-2"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold fs-5">Ready for Departure</div>
                                <div class="text-muted">Seluruh komposisi kru sudah terpenuhi.</div>
                            </div>
                        @else
                            <div class="symbol symbol-45px symbol-circle me-4">
                                <span class="symbol-label bg-light-warning">
                                    <i class="bi bi-exclamation-lg text-warning fs-2"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold fs-5">Crew belum lengkap</div>
                                <div class="text-muted">Posisi yang masih perlu diisi:</div>
                                <ul class="mt-2 text-muted mb-0">
                                    @foreach($missingRoles as $role)
                                        <li>{{ $role }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="border-top pt-4">
                        <div class="fw-bold mb-2">Kebutuhan Minimum</div>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($requiredRoles as $role => $count)
                                <span class="badge badge-light">{{ $role }} &times; {{ $count }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <div class="card-title">
                <h4 class="fw-bold mb-0">Daftar Crew Ditugaskan</h4>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignCrewModal">
                <i class="bi bi-plus-circle me-2"></i>Assign Manual
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Role</th>
                            <th>Nama</th>
                            <th>Position</th>
                            <th>Base</th>
                            <th>Assigned At</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($flight->crews as $crew)
                            <tr>
                                <td>{{ $crew->pivot->role_in_flight }}</td>
                                <td>{{ $crew->user->name ?? '-' }}</td>
                                <td>{{ $crew->position }}</td>
                                <td>{{ $crew->base_location }}</td>
                                <td>{{ optional($crew->pivot->assigned_at)->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-sm btn-light-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmRemoveCrewModal"
                                        data-action="{{ route('admin.flight-schedules.remove-crew', [$flight->id, $crew->pivot->id]) }}"
                                        data-label="{{ $crew->user->name ?? $crew->employee_id }}">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">Belum ada crew yang ditugaskan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmAutoAssignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.flight-schedules.auto-assign', $flight->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Penugasan Otomatis</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Auto-assign akan memilih kru sesuai regulasi dan ketersediaan.<br>
                            Lanjutkan proses penugasan otomatis untuk penerbangan <strong>{{ $flight->flight_number }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ya, Jalankan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignCrewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.flight-schedules.assign-crew', $flight->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Crew Manual</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Pilih Crew</label>
                        <select class="form-select select2-crew" name="crew_id" required data-placeholder="-- Pilih Crew --">
                            <option value="">-- Pilih Crew --</option>
                            @foreach($availableCrews as $crewOption)
                                <option value="{{ $crewOption->id }}">
                                    {{ $crewOption->user->name ?? $crewOption->employee_id }} &mdash; {{ $crewOption->position }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-8 mt-2">Crew otomatis ditempatkan sesuai posisinya (Captain, FO, Purser, Flight Attendant).</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmRemoveCrewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="removeCrewForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Crew dari Penerbangan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus <strong id="removeCrewLabel">-</strong> dari penerbangan ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@push('scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const removeCrewModal = document.getElementById('confirmRemoveCrewModal');
            const assignCrewModal = document.getElementById('assignCrewModal');

            removeCrewModal?.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const form = removeCrewModal.querySelector('#removeCrewForm');
                if (!button || !form) return;

                form.setAttribute('action', button.getAttribute('data-action'));
                const label = removeCrewModal.querySelector('#removeCrewLabel');
                if (label) label.textContent = button.getAttribute('data-label') || '-';
            });

            if (window.$ && assignCrewModal) {
                window.$(assignCrewModal).on('shown.bs.modal', function () {
                    window.$('.select2-crew').select2({
                        dropdownParent: window.$('#assignCrewModal'),
                        width: '100%',
                        placeholder: '-- Pilih Crew --'
                    });
                });
            }
        });
    </script>
@endpush
