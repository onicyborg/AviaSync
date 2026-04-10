@extends('layouts.master')

@section('page_title', 'Flight Schedules')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Flight Schedules</h3>
            <span class="text-muted">Kelola jadwal penerbangan dan status readiness crew.</span>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-mode="create">
            <i class="bi bi-plus-lg me-2"></i>Tambah Jadwal
        </button>
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
        <div class="card-body">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Cari Flight Code</label>
                    <input type="text" class="form-control" id="filterFlight" placeholder="Misal: AV123">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Origin</label>
                    <select class="form-select" id="filterOrigin">
                        <option value="">Semua Origin</option>
                        @foreach($origins as $origin)
                            <option value="{{ $origin }}">{{ $origin }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Destination</label>
                    <select class="form-select" id="filterDestination">
                        <option value="">Semua Destination</option>
                        @foreach($destinations as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex">
                    <button type="button" class="btn btn-light flex-grow-1" id="filterReset">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed" id="flightSchedulesTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Status</th>
                            <th>Crew Readiness</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($flightSchedules as $schedule)
                            @php
                                $roleCounts = $schedule->crews->groupBy(fn($crew) => $crew->pivot->role_in_flight)->map->count();
                                $missingRoles = [];
                                foreach ($requiredRoles as $role => $requiredCount) {
                                    if (($roleCounts[$role] ?? 0) < $requiredCount) {
                                        $missingRoles[] = $role;
                                    }
                                }
                                $isComplete = empty($missingRoles);
                            @endphp
                            <tr
                                data-flight="{{ $schedule->flight_number }}"
                                data-origin="{{ $schedule->origin }}"
                                data-destination="{{ $schedule->destination }}"
                                data-status="{{ $schedule->status }}"
                            >
                                <td>
                                    <div class="fw-bold">{{ $schedule->flight_number }}</div>
                                    <div class="text-muted fs-8">{{ $schedule->id }}</div>
                                </td>
                                <td>{{ $schedule->origin }} → {{ $schedule->destination }}</td>
                                <td>{{ optional($schedule->departure_time)->format('d M Y H:i') }}</td>
                                <td>{{ optional($schedule->arrival_time)->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $schedule->status === 'active' ? 'success' : ($schedule->status === 'cancelled' ? 'danger' : 'primary') }}">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($isComplete)
                                        <span class="badge badge-light-success">Ready</span>
                                    @else
                                        <span class="badge badge-light-warning">Incomplete</span>
                                        <div class="text-muted fs-8">Missing: {{ implode(', ', $missingRoles) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.flight-schedules.show', $schedule->id) }}" class="btn btn-sm btn-light-primary me-2">Detail</a>
                                    <button class="btn btn-sm btn-light me-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#scheduleModal"
                                        data-mode="edit"
                                        data-action="{{ route('admin.flight-schedules.update', $schedule->id) }}"
                                        data-flight_number="{{ $schedule->flight_number }}"
                                        data-origin="{{ $schedule->origin }}"
                                        data-destination="{{ $schedule->destination }}"
                                        data-departure_time="{{ optional($schedule->departure_time)->format('Y-m-d\TH:i') }}"
                                        data-arrival_time="{{ optional($schedule->arrival_time)->format('Y-m-d\TH:i') }}"
                                        data-status="{{ $schedule->status }}">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-light-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal"
                                        data-action="{{ route('admin.flight-schedules.destroy', $schedule->id) }}"
                                        data-label="{{ $schedule->flight_number }}">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-10">Belum ada data jadwal penerbangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.flight-schedules.store') }}" id="scheduleForm">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jadwal</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Flight Number</label>
                                <input type="text" name="flight_number" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Origin</label>
                                <input type="text" name="origin" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destination</label>
                                <input type="text" name="destination" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departure Time</label>
                                <input type="datetime-local" name="departure_time" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Arrival Time</label>
                                <input type="datetime-local" name="arrival_time" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach($statusOptions as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Jadwal Penerbangan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus jadwal <strong id="deleteLabel">-</strong>?</p>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterState = {
                flight: '',
                origin: '',
                destination: '',
                status: '',
            };

            const stripHtml = (value) => (value || '').replace(/<[^>]*>/g, '').trim();
            const parseRoute = (route) => {
                const parts = (route || '').split('→').map(part => part.trim());
                return {
                    origin: parts[0] || '',
                    destination: parts[1] || '',
                };
            };

            const customFilter = function (settings, data) {
                const flightValue = stripHtml(data[0] || '').toUpperCase();
                if (filterState.flight && !flightValue.includes(filterState.flight)) {
                    return false;
                }

                const routeValue = stripHtml(data[1] || '');
                const routeParts = parseRoute(routeValue);
                if (filterState.origin && routeParts.origin !== filterState.origin) {
                    return false;
                }
                if (filterState.destination && routeParts.destination !== filterState.destination) {
                    return false;
                }

                const statusValue = stripHtml(data[4] || '').toLowerCase();
                if (filterState.status && statusValue !== filterState.status) {
                    return false;
                }

                return true;
            };

            if (typeof window.DataTable?.ext === 'function') {
                window.DataTable.ext('search', 'flightFilters', customFilter);
            } else if (window.DataTable?.ext?.search) {
                window.DataTable.ext.search.push(customFilter);
            }

            const table = new window.DataTable('#flightSchedulesTable');
            const modal = document.getElementById('scheduleModal');
            const deleteModal = document.getElementById('confirmDeleteModal');
            const form = document.getElementById('scheduleForm');
            const flightInput = document.getElementById('filterFlight');
            const originSelect = document.getElementById('filterOrigin');
            const destinationSelect = document.getElementById('filterDestination');
            const statusSelect = document.getElementById('filterStatus');
            const resetButton = document.getElementById('filterReset');

            const applyFilters = () => table.draw();

            flightInput?.addEventListener('input', function (event) {
                filterState.flight = (event.target.value || '').trim().toUpperCase();
                applyFilters();
            });

            originSelect?.addEventListener('change', function (event) {
                filterState.origin = (event.target.value || '').trim();
                applyFilters();
            });

            destinationSelect?.addEventListener('change', function (event) {
                filterState.destination = (event.target.value || '').trim();
                applyFilters();
            });

            statusSelect?.addEventListener('change', function (event) {
                filterState.status = (event.target.value || '').trim().toLowerCase();
                applyFilters();
            });

            resetButton?.addEventListener('click', function () {
                filterState.flight = '';
                filterState.origin = '';
                filterState.destination = '';
                filterState.status = '';
                if (flightInput) flightInput.value = '';
                if (originSelect) originSelect.value = '';
                if (destinationSelect) destinationSelect.value = '';
                if (statusSelect) statusSelect.value = '';
                applyFilters();
            });

            modal?.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const mode = button?.getAttribute('data-mode') || 'create';
                form.reset();
                form.setAttribute('action', form.getAttribute('action'));
                form.querySelector('[name="_method"]').value = 'POST';
                modal.querySelector('.modal-title').textContent = mode === 'edit' ? 'Edit Jadwal' : 'Tambah Jadwal';

                if (mode === 'edit' && button) {
                    form.setAttribute('action', button.getAttribute('data-action'));
                    form.querySelector('[name="_method"]').value = 'PUT';
                    form.querySelector('[name="flight_number"]').value = button.getAttribute('data-flight_number');
                    form.querySelector('[name="origin"]').value = button.getAttribute('data-origin');
                    form.querySelector('[name="destination"]').value = button.getAttribute('data-destination');
                    form.querySelector('[name="departure_time"]').value = button.getAttribute('data-departure_time');
                    form.querySelector('[name="arrival_time"]').value = button.getAttribute('data-arrival_time');
                    form.querySelector('[name="status"]').value = button.getAttribute('data-status');
                }
            });

            deleteModal?.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                deleteModal.querySelector('#deleteLabel').textContent = button.getAttribute('data-label') || '-';
                const deleteForm = deleteModal.querySelector('#deleteForm');
                deleteForm.setAttribute('action', button.getAttribute('data-action'));
            });
        });
    </script>
@endpush
