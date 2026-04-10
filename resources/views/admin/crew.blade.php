@extends('layouts.master')

@php
    $crewAvatarPlaceholder = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><circle cx="60" cy="60" r="60" fill="#eff2f5"/><circle cx="60" cy="44" r="24" fill="#c7d2fe"/><path d="M20 110c6-26 32-32 40-32s34 6 40 32" fill="#a5b4fc"/></svg>');
@endphp

@section('page_title', 'Manage Crew')

@push('styles')
    <style>
        .crew-avatar-input .image-input-wrapper {
            border: 1px dashed var(--bs-gray-400);
            background-size: cover;
            background-position: center;
        }

        [data-bs-theme="dark"] .crew-avatar-input .image-input-wrapper {
            border-color: rgba(255, 255, 255, 0.25);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Manage Crew</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crewModal" id="btnAddCrew">
            <i class="bi bi-plus-lg me-2"></i>Tambah Crew
        </button>
    </div>

    <div class="card card-flush">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="bi bi-search fs-2 position-absolute ms-4"></i>
                    <input type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Cari nama atau Employee ID" id="crew_search" />
                </div>
            </div>
        </div>
        <div class="card-body py-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="crew_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Employee ID</th>
                            <th>Posisi</th>
                            <th>Base</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($crews as $crew)
                            @php
                                $photoPath = trim((string) ($crew->profile_picture ?? ''));
                                $photoPath = ltrim($photoPath, '/');
                                $photoPath = preg_replace('/^storage\//', '', $photoPath);

                                $photoUrl = !empty($photoPath)
                                    ? url('storage/' . $photoPath)
                                    : null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="symbol symbol-45px symbol-circle">
                                        @if(!empty($photoUrl))
                                            <img alt="Foto" src="{{ $photoUrl }}" />
                                        @else
                                            <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                {{ strtoupper(substr($crew->user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold">{{ $crew->user->name ?? '-' }}</span>
                                        <span class="text-muted fs-8">{{ $crew->user->email ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>{{ $crew->employee_id }}</td>
                                <td>{{ $crew->position }}</td>
                                <td>{{ $crew->base_location }}</td>
                                <td>
                                    @if(($crew->status ?? null) === 'active')
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.crew.show', $crew->id) }}" class="btn btn-light-primary me-2">
                                        Detail
                                    </a>
                                    <button type="button"
                                        class="btn btn-light-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal"
                                        data-delete_url="{{ route('admin.crew.destroy', $crew->id) }}"
                                        data-name="{{ $crew->user->name ?? '-' }}">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="crewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="crewForm" method="POST" action="{{ route('admin.crew.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="crewFormMethod" value="POST">
                    <input type="hidden" name="id" id="crew_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="crewModalTitle">Tambah Crew</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label">Foto Profil</label>
                            <div class="image-input image-input-circle crew-avatar-input" data-kt-image-input="true" id="crew_photo_container">
                                <div id="crew_photo_wrapper" class="image-input-wrapper w-125px h-125px"
                                    style="background-image: url('{{ $crewAvatarPlaceholder }}');"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-30px h-30px bg-body shadow"
                                    data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Ganti foto">
                                    <i class="bi bi-pencil-fill fs-7"></i>
                                    <input type="file" name="profile_picture" id="crew_profile_picture" accept=".png, .jpg, .jpeg, .webp" />
                                    <input type="hidden" name="avatar_remove" />
                                </label>
                            </div>
                            <div class="text-muted fs-8 mt-2">Format: png/jpg/jpeg/webp. Maks: 2MB.</div>
                        </div>

                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="crew_name" value="{{ old('name') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="crew_email" value="{{ old('email') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" name="employee_id" id="crew_employee_id" value="{{ old('employee_id') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Posisi</label>
                                <input type="text" class="form-control" name="position" id="crew_position" value="{{ old('position') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Location</label>
                                <input type="text" class="form-control" name="base_location" id="crew_base_location" value="{{ old('base_location') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="crew_status">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveCrew">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Crew</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus crew <strong id="delete_name">-</strong>?</p>
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
        (function(){
            var crewPhotoPlaceholder = @json($crewAvatarPlaceholder);

            function setPhoto(url) {
                var wrapper = document.getElementById('crew_photo_wrapper');
                var container = document.getElementById('crew_photo_container');
                if (!wrapper) return;
                var finalUrl = url || crewPhotoPlaceholder;
                wrapper.style.backgroundImage = "url('" + finalUrl + "')";
                container && container.classList.toggle('has-photo', Boolean(url));
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
                    var dt = jQuery('#crew_table').DataTable({
                        pageLength: 10,
                        ordering: true,
                    });
                    var $search = jQuery('#crew_search');
                    $search.on('keyup change', function(){
                        dt.search(this.value || '').draw();
                    });
                }

                var modalEl = document.getElementById('crewModal');
                var form = document.getElementById('crewForm');
                var method = document.getElementById('crewFormMethod');
                var title = document.getElementById('crewModalTitle');

                var inputId = document.getElementById('crew_id');
                var inputName = document.getElementById('crew_name');
                var inputEmail = document.getElementById('crew_email');
                var inputEmployeeId = document.getElementById('crew_employee_id');
                var inputPosition = document.getElementById('crew_position');
                var inputBase = document.getElementById('crew_base_location');
                var inputStatus = document.getElementById('crew_status');
                var inputPhoto = document.getElementById('crew_profile_picture');

                modalEl && modalEl.addEventListener('show.bs.modal', function (event) {
                    var btn = event.relatedTarget;
                    var mode = btn && btn.getAttribute('data-mode');

                    if (!form || !method || !title) return;

                    if (!mode || mode === 'create') {
                        title.textContent = 'Tambah Crew';
                        form.action = @json(route('admin.crew.store'));
                        method.value = 'POST';
                        inputId && (inputId.value = '');
                        inputName && (inputName.value = '');
                        inputEmail && (inputEmail.value = '');
                        inputEmployeeId && (inputEmployeeId.value = '');
                        inputPosition && (inputPosition.value = '');
                        inputBase && (inputBase.value = '');
                        inputStatus && (inputStatus.value = 'active');
                        inputPhoto && (inputPhoto.value = '');
                        setPhoto(null);
                        return;
                    }

                    title.textContent = 'Edit Crew';
                    form.action = btn.getAttribute('data-update_url') || form.action;
                    method.value = 'PUT';
                    inputId && (inputId.value = btn.getAttribute('data-id') || '');
                    inputName && (inputName.value = btn.getAttribute('data-name') || '');
                    inputEmail && (inputEmail.value = btn.getAttribute('data-email') || '');
                    inputEmployeeId && (inputEmployeeId.value = btn.getAttribute('data-employee_id') || '');
                    inputPosition && (inputPosition.value = btn.getAttribute('data-position') || '');
                    inputBase && (inputBase.value = btn.getAttribute('data-base_location') || '');
                    inputStatus && (inputStatus.value = btn.getAttribute('data-status') || 'active');
                    inputPhoto && (inputPhoto.value = '');
                    setPhoto(btn.getAttribute('data-photo_url'));
                });

                inputPhoto && inputPhoto.addEventListener('change', function(){
                    var f = inputPhoto.files && inputPhoto.files[0];
                    if (!f) return;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        setPhoto(e.target && e.target.result ? e.target.result : null);
                    };
                    reader.readAsDataURL(f);
                });

                var deleteModalEl = document.getElementById('confirmDeleteModal');
                deleteModalEl && deleteModalEl.addEventListener('show.bs.modal', function (event) {
                    var btn = event.relatedTarget;
                    var url = btn && btn.getAttribute('data-delete_url');
                    var name = btn && btn.getAttribute('data-name');
                    var deleteForm = document.getElementById('deleteForm');
                    var deleteName = document.getElementById('delete_name');
                    deleteForm && url && (deleteForm.action = url);
                    deleteName && (deleteName.textContent = name || '-');
                });
            });
        })();
    </script>
@endpush
