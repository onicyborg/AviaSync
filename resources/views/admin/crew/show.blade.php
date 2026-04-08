@extends('layouts.master')

@php
    $crewAvatarPlaceholder = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><circle cx="60" cy="60" r="60" fill="#eff2f5"/><circle cx="60" cy="44" r="24" fill="#c7d2fe"/><path d="M20 110c6-26 32-32 40-32s34 6 40 32" fill="#a5b4fc"/></svg>');
@endphp

@section('page_title', 'Detail Crew')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <a href="{{ route('admin.crew.index') }}" class="btn btn-light mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0">Detail Crew</h3>
            <span class="text-muted">{{ $crew->user->name ?? '-' }} - {{ $crew->employee_id }}</span>
        </div>
        <div class="text-muted">
            Terakhir diperbarui: {{ optional($crew->updated_at)->format('d M Y H:i') ?? '-' }}
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

    @if($errors && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-5">
        <div class="card-body">
            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#crew_profile_tab">Profil & Akun</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#crew_certification_tab">Certifications</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#crew_health_tab">Health Records</a>
                </li>
            </ul>

            <div class="tab-content" id="crewDetailTabs">
                <div class="tab-pane fade show active" id="crew_profile_tab">
                    <div class="row g-5">
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    @php
                                        $photoPath = trim((string) ($crew->profile_picture ?? ''));
                                        $photoPath = ltrim($photoPath, '/');
                                        $photoPath = preg_replace('/^storage\//', '', $photoPath);
                                        $photoUrl = !empty($photoPath) ? url('storage/' . $photoPath) : null;
                                    @endphp
                                    <div class="symbol symbol-150px symbol-circle mb-3">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="Foto" class="rounded-circle w-150px h-150px object-fit-cover">
                                        @else
                                            <span class="symbol-label bg-light">
                                                <img src="{{ $crewAvatarPlaceholder }}" alt="placeholder" class="w-100 h-100 rounded-circle">
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="fw-bold mb-1">{{ $crew->user->name ?? '-' }}</h4>
                                    <div class="text-muted">{{ $crew->position }}</div>
                                    <div class="badge {{ ($crew->status ?? 'active') === 'active' ? 'badge-light-success' : 'badge-light-danger' }} mt-3">
                                        {{ ucfirst($crew->status ?? 'active') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h5 class="fw-bold">Update Profil & Akun</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.crew.update', $crew->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="redirect_to" value="detail">
                                        <div class="row g-5">
                                            <div class="col-md-6">
                                                <label class="form-label">Nama</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $crew->user->name ?? '') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $crew->user->email ?? '') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Employee ID</label>
                                                <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $crew->employee_id) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Posisi</label>
                                                <input type="text" name="position" class="form-control" value="{{ old('position', $crew->position) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Base Location</label>
                                                <input type="text" name="base_location" class="form-control" value="{{ old('base_location', $crew->base_location) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" {{ old('status', $crew->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $crew->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Password Baru (opsional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Isi untuk reset password">
                                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Foto Profil</label>
                                                <input type="file" name="profile_picture" class="form-control" accept=".png,.jpg,.jpeg,.webp">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-5">
                                            <button class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="crew_certification_tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Daftar Sertifikasi</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#certificationModal" data-mode="create">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Sertifikasi
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama</th>
                                    <th>Nomor</th>
                                    <th>Tanggal Terbit</th>
                                    <th>Tanggal Expired</th>
                                    <th>Status</th>
                                    <th>Dokumen</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($crew->certifications as $cert)
                                    <tr>
                                        <td>{{ $cert->certificate_name }}</td>
                                        <td>{{ $cert->certificate_number }}</td>
                                        <td>{{ optional($cert->issue_date)->format('d M Y') }}</td>
                                        <td>{{ optional($cert->expiry_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge badge-light-{{ $cert->status === 'valid' ? 'success' : ($cert->status === 'expired' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($cert->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $certPath = trim((string) ($cert->attachment_path ?? ''));
                                                $certPath = ltrim($certPath, '/');
                                                $certPath = preg_replace('/^storage\//', '', $certPath);
                                            @endphp
                                            @if(!empty($certPath))
                                                <a href="{{ url('storage/' . $certPath) }}" target="_blank" class="btn btn-sm btn-light-primary">View Document</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-light-primary me-2" data-bs-toggle="modal" data-bs-target="#certificationModal"
                                                data-mode="edit"
                                                data-action="{{ route('admin.crew.certifications.update', [$crew->id, $cert->id]) }}"
                                                data-name="{{ $cert->certificate_name }}"
                                                data-number="{{ $cert->certificate_number }}"
                                                data-issue_date="{{ optional($cert->issue_date)->format('Y-m-d') }}"
                                                data-expiry_date="{{ optional($cert->expiry_date)->format('Y-m-d') }}"
                                                data-status="{{ $cert->status }}">
                                                Edit
                                            </button>
                                            <button class="btn btn-sm btn-light-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteCertificationModal"
                                                data-action="{{ route('admin.crew.certifications.destroy', [$crew->id, $cert->id]) }}"
                                                data-label="{{ $cert->certificate_name }}">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-10">Belum ada sertifikasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="crew_health_tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Daftar Rekam Medis</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#healthRecordModal" data-mode="create">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Rekam Medis
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Tanggal</th>
                                    <th>Examiner</th>
                                    <th>Status</th>
                                    <th>Next Checkup</th>
                                    <th>Catatan</th>
                                    <th>Dokumen</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($crew->healthRecords as $record)
                                    <tr>
                                        <td>{{ optional($record->checkup_date)->format('d M Y') }}</td>
                                        <td>{{ $record->medical_examiner }}</td>
                                        <td>
                                            <span class="badge badge-light-{{ $record->status === 'fit' ? 'success' : ($record->status === 'unfit' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($record->next_checkup_date)->format('d M Y') ?? '-' }}</td>
                                        <td>{{ $record->notes ?? '-' }}</td>
                                        <td>
                                            @php
                                                $recordPath = trim((string) ($record->attachment_path ?? ''));
                                                $recordPath = ltrim($recordPath, '/');
                                                $recordPath = preg_replace('/^storage\//', '', $recordPath);
                                            @endphp
                                            @if(!empty($recordPath))
                                                <a href="{{ url('storage/' . $recordPath) }}" target="_blank" class="btn btn-sm btn-light-primary">View Document</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-light-primary me-2" data-bs-toggle="modal" data-bs-target="#healthRecordModal"
                                                data-mode="edit"
                                                data-action="{{ route('admin.crew.health-records.update', [$crew->id, $record->id]) }}"
                                                data-checkup_date="{{ optional($record->checkup_date)->format('Y-m-d') }}"
                                                data-examiner="{{ $record->medical_examiner }}"
                                                data-status="{{ $record->status }}"
                                                data-next_checkup_date="{{ optional($record->next_checkup_date)->format('Y-m-d') }}"
                                                data-notes="{{ $record->notes }}">
                                                Edit
                                            </button>
                                            <button class="btn btn-sm btn-light-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteHealthModal"
                                                data-action="{{ route('admin.crew.health-records.destroy', [$crew->id, $record->id]) }}"
                                                data-label="{{ optional($record->checkup_date)->format('d M Y') ?? $record->medical_examiner }}">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-10">Belum ada rekam medis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.crew.partials.modals', ['crew' => $crew])

    <div class="modal fade" id="confirmDeleteCertificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteCertificationForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Sertifikasi</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus sertifikasi <strong id="certDeleteLabel">-</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteHealthModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteHealthForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Rekam Medis</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus rekam medis <strong id="healthDeleteLabel">-</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            (function(){
                var msg = @json(session('success'));
                if (window.toastr && toastr.success) { toastr.success(msg); }
                else { console.log('SUCCESS:', msg); }
            })();
        </script>
    @endif

    @if(session('error'))
        <script>
            (function(){
                var msg = @json(session('error'));
                if (window.toastr && toastr.error) { toastr.error(msg); }
                else { console.error('ERROR:', msg); }
            })();
        </script>
    @endif

    @if($errors && $errors->any())
        <script>
            (function(){
                var errs = @json($errors->all());
                var msg = errs.join('\n');
                if (window.toastr && toastr.error) { toastr.error(msg); }
                else { console.error('ERRORS:', msg); }
            })();
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const certModal = document.getElementById('certificationModal');
            const healthModal = document.getElementById('healthRecordModal');
            const deleteCertModal = document.getElementById('confirmDeleteCertificationModal');
            const deleteHealthModal = document.getElementById('confirmDeleteHealthModal');
            let activeCertTrigger = null;
            let activeHealthTrigger = null;

            document.addEventListener('click', function(event) {
                const certTrigger = event.target.closest('[data-bs-target="#certificationModal"]');
                if (certTrigger) {
                    activeCertTrigger = certTrigger;
                }

                const healthTrigger = event.target.closest('[data-bs-target="#healthRecordModal"]');
                if (healthTrigger) {
                    activeHealthTrigger = healthTrigger;
                }
            }, true);

            certModal?.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget || activeCertTrigger;
                const form = certModal.querySelector('form');
                if (!form) return;
                const mode = button?.getAttribute('data-mode') || 'create';
                form.reset();
                form.querySelector('[name="_method"]').value = mode === 'edit' ? 'PUT' : 'POST';
                form.setAttribute('action', mode === 'edit' ? button?.getAttribute('data-action') : form.dataset.storeAction);
                certModal.querySelector('.modal-title').textContent = mode === 'edit' ? 'Edit Certification' : 'Tambah Certification';
                form.querySelectorAll('.edit-attachment-hint').forEach(el => {
                    el.classList.toggle('d-none', mode !== 'edit');
                });

                if (mode === 'edit' && button) {
                    form.querySelector('[name="certificate_name"]').value = button.getAttribute('data-name') || '';
                    form.querySelector('[name="certificate_number"]').value = button.getAttribute('data-number') || '';
                    form.querySelector('[name="issue_date"]').value = button.getAttribute('data-issue_date') || '';
                    form.querySelector('[name="expiry_date"]').value = button.getAttribute('data-expiry_date') || '';
                    form.querySelector('[name="status"]').value = button.getAttribute('data-status') || 'valid';
                }
            });

            healthModal?.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget || activeHealthTrigger;
                const form = healthModal.querySelector('form');
                if (!form) return;
                const mode = button?.getAttribute('data-mode') || 'create';
                form.reset();
                form.querySelector('[name="_method"]').value = mode === 'edit' ? 'PUT' : 'POST';
                form.setAttribute('action', mode === 'edit' ? button?.getAttribute('data-action') : form.dataset.storeAction);
                healthModal.querySelector('.modal-title').textContent = mode === 'edit' ? 'Edit Health Record' : 'Tambah Health Record';
                form.querySelectorAll('.edit-attachment-hint').forEach(el => {
                    el.classList.toggle('d-none', mode !== 'edit');
                });

                if (mode === 'edit' && button) {
                    form.querySelector('[name="checkup_date"]').value = button.getAttribute('data-checkup_date') || '';
                    form.querySelector('[name="medical_examiner"]').value = button.getAttribute('data-examiner') || '';
                    form.querySelector('[name="status"]').value = button.getAttribute('data-status') || 'fit';
                    form.querySelector('[name="next_checkup_date"]').value = button.getAttribute('data-next_checkup_date') || '';
                    form.querySelector('[name="notes"]').value = button.getAttribute('data-notes') || '';
                }
            });

            deleteCertModal?.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const form = deleteCertModal.querySelector('form');
                if (!button || !form) return;
                form.setAttribute('action', button.getAttribute('data-action'));
                const label = deleteCertModal.querySelector('#certDeleteLabel');
                if (label) label.textContent = button.getAttribute('data-label') || '-';
            });

            deleteHealthModal?.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const form = deleteHealthModal.querySelector('form');
                if (!button || !form) return;
                form.setAttribute('action', button.getAttribute('data-action'));
                const label = deleteHealthModal.querySelector('#healthDeleteLabel');
                if (label) label.textContent = button.getAttribute('data-label') || '-';
            });
        });
    </script>
@endpush
