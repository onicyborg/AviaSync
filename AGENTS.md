# Agents.md — Pedoman Umum Pengembangan Laravel 10 (Template)

Dokumen ini adalah **aturan wajib** (system prompt) untuk seluruh AI agent dan developer manusia yang bekerja pada sebuah codebase **Laravel 10**.

Target utama:
- Menjaga **konsistensi UI/UX** (Bootstrap + Metronic atau template setara) di seluruh modul.
- Menjaga konsistensi **pola CRUD berbasis Modal** (bukan halaman create/edit terpisah) untuk master data.
- Menjaga konsistensi **pola DataTables** untuk listing.
- Menjaga konsistensi **pola Controller** (resource controller, validasi via `$request->validate()`), serta **flash message** / JSON response.
- Menjaga konsistensi **multi-role access** menggunakan middleware `role:...`.

---

# 0) Ringkasan Tech Stack & UI Kit (Laravel 10)

- **Backend**
  - Laravel `^10.x`.
  - PHP `^8.1` (atau mengikuti requirement Laravel 10 yang kamu pakai).
  - Paket tambahan (opsional, sesuai kebutuhan proyek), contoh: `maatwebsite/excel` untuk export.

- **Frontend build**
  - Vite (`vite.config.js`) dengan entry umum:
    - `resources/css/app.css`
    - `resources/js/app.js`
  - Boleh memakai `axios`, **Fetch**, atau **jQuery AJAX**. Di pedoman ini, contoh utama untuk AJAX memakai **Fetch**.

- **UI Framework / Template**
  - Disarankan menggunakan Bootstrap 5 + template admin (mis. Metronic) agar komponen UI konsisten.
  - Vendor umum:
    - DataTables
    - FullCalendar (opsional)
  - Ikon: Bootstrap Icons (atau icon set lain yang konsisten).

**Aturan:** setiap halaman baru wajib `@extends('layouts.master')` (atau layout utama proyek) dan meletakkan JS halaman pada `@push('scripts')`.

---

# 1) POLA FRONTEND & IMPLEMENTASI UI

## 1.1 Struktur Blade, Layout Utama, dan Slot

- **Layout utama:** `resources/views/layouts/master.blade.php`
  - Halaman menaruh konten utama pada `@yield('content')`.
  - Halaman menambahkan CSS/JS lokal via `@stack('styles')` dan `@stack('scripts')`.
  - Sidebar dipanggil via `@include('layouts.sidebar')`.

**Snippet (layout master):**
```blade
<!-- resources/views/layouts/master.blade.php -->
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />

...
<div class="app-container container-fluid py-5">
    @yield('content')
</div>

...
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

@stack('scripts')
```

**Aturan implementasi view:**
- **Gunakan header halaman yang konsisten**:
  - Judul di kiri.
  - Tombol aksi utama di kanan.
- **Gunakan komponen Metronic/Bootstrap**:
  - `card`, `card-body`, `table-responsive`, `btn btn-primary`, dll.

Contoh pola header:
```blade
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data ...</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#...Modal" id="btnAdd...">
        <i class="bi bi-plus-lg me-2"></i>Tambah ...
    </button>
</div>
```

---

## 1.2 Pola CRUD di UI: **Modal Create/Edit + Modal Konfirmasi Delete**

Standar ini menggunakan **Modal** untuk create/edit, dan **modal konfirmasi** untuk delete. Untuk master data, hindari halaman `create.blade.php` atau `edit.blade.php` terpisah (kecuali memang ada keputusan desain yang berbeda).

### 1.2.1 Kerangka Modal Create/Edit

**Template wajib (contoh resource generik):**
```blade
<!-- resources/views/admin/<resource>/index.blade.php -->
<div class="modal fade" id="resourceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="resourceForm" method="POST" action="{{ route('<resource>.store') }}">
        @csrf
        <input type="hidden" name="_method" id="resourceFormMethod" value="POST">
        <input type="hidden" name="id" id="resource_id">

        <div class="modal-header">
          <h5 class="modal-title" id="resourceModalTitle">Tambah Data</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          ...fields...
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSaveResource">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

**Aturan:**
- **Selalu ada** `@csrf`.
- **Selalu ada** hidden `_method` untuk switch POST/PUT.
- Mode create/edit ditentukan dengan:
  - `form.action` diarahkan ke `route('resource.store')` (create) atau `url('resource') + '/' + id` (edit).
  - `formMethod.value` diset ke `POST` atau `PUT`.
  - `title.textContent` disesuaikan.

### 1.2.2 Kerangka Modal Konfirmasi Delete

**Template wajib:**
```blade
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Hapus ...</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Yakin ingin menghapus ... <strong id="delete_name">-</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

**Aturan:**
- Tombol delete pada tabel wajib menyimpan `data-id` dan `data-name`.
- JS wajib mengubah `deleteForm.action` ke URL `resource/{id}`.

---

## 1.3 Pola Data Display: DataTables

### 1.3.1 Konfigurasi DataTables

Standar default: gunakan **client-side DataTables** untuk data yang sudah dirender oleh Blade. Bila data besar, boleh beralih ke server-side, tapi pastikan konsisten di seluruh modul.

**Snippet (init DataTable):**
```js
$(document).ready(function() {
  $('#<resource>_table').DataTable({
    pageLength: 10,
    ordering: true,
    language: {
      url: ''
    }
  });
});
```

**Aturan:**
- Table markup mengikuti style Metronic:
  - `table align-middle table-row-dashed fs-6 gy-5`
  - `<thead>` memakai kelas `text-start text-muted fw-bold fs-7 text-uppercase gs-0`
- Inisialisasi DataTables dilakukan di `@push('scripts')` per halaman.

### 1.3.2 Reload DataTables Setelah CRUD

Standar default: CRUD master data dilakukan via submit form normal (bukan AJAX). Setelah submit, halaman redirect dan tabel tampil ulang.

---

## 1.4 Komponen UI Kustom / Kompleks

### 1.4.1 Image Input (Metronic `data-kt-image-input`)

Digunakan untuk upload foto guru/siswa dan profile.

**Snippet (teacher modal):**
```blade
<div class="image-input image-input-circle" data-kt-image-input="true"
     style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
  <div id="teacher_photo_wrapper" class="image-input-wrapper w-125px h-125px"></div>
  <label class="btn btn-icon btn-circle ..." data-kt-image-input-action="change">
    <input type="file" name="photo" id="teacher_photo" accept=".png, .jpg, .jpeg, .webp" />
  </label>
</div>
<div class="invalid-feedback d-block" data-field="photo"></div>
```

**Snippet (preview pakai FileReader):**
```js
photoInput?.addEventListener('change', function(){
  const f = photoInput.files && photoInput.files[0];
  if (!f) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    if (photoWrapper) photoWrapper.style.backgroundImage = `url('${e.target.result}')`;
  };
  reader.readAsDataURL(f);
});
```

### 1.4.2 Select2 untuk Combobox

Ada pola `select2` untuk pemilihan kelas pada modal siswa.

**Snippet:**
```js
$('#student_class_id').select2({
  dropdownParent: $('#studentModal'),
  width: '100%'
});
```

**Aturan:**
- Untuk select di dalam modal, **wajib** `dropdownParent: $('#<modalId>')` agar dropdown tidak tertutup modal.

### 1.4.3 Date Input

Filter pada log notifikasi memakai input native HTML5:
```blade
<input type="date" name="date_from" class="form-control">
```

---

# 2) PENANGANAN JAVASCRIPT & AJAX

## 2.1 Prinsip Umum

- JS per halaman ditaruh pada `@push('scripts')`.
- Untuk master data, submit form modal umumnya **non-AJAX** (HTTP form submit biasa) lalu controller melakukan `redirect()->route(...)->with(...)`.
- Untuk halaman profil, submit menggunakan **Fetch** + JSON response.

---

## 2.2 Pola Submit AJAX (Fetch) + Validasi

Gunakan pola ini untuk form yang butuh UX cepat (mis. profil, upload ringan, modal detail dengan update kecil).

### 2.2.1 Template Helper: clearValidation

**Snippet:**
```js
function clearValidation(scope) {
  (scope || document).querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  (scope || document).querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}
```

### 2.2.2 Submit Form Profile (Fetch)

**Snippet:**
```js
profileForm?.addEventListener('submit', function(e) {
  e.preventDefault();
  clearValidation(profileForm);

  const formData = new FormData(profileForm);
  formData.append('_method', 'PUT');

  fetch(routes.updateProfile, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: formData
  })
  .then(async (resp) => {
    const data = await resp.json();
    if (!resp.ok) throw { status: resp.status, data };
    window.toastr?.success?.(data.message || 'Berhasil disimpan');
  })
  .catch(err => {
    const errs = err?.data?.errors || {};
    Object.keys(errs).forEach(field => {
      const input = profileForm.querySelector(`[name="${field}"]`);
      input && input.classList.add('is-invalid');
      const fb = profileForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
      fb && (fb.textContent = errs[field][0]);
    });
    if (!Object.keys(errs).length) window.toastr?.error?.('Terjadi kesalahan.');
  });
});
```

**Aturan wajib untuk AJAX form di proyek Laravel 10 ini:**
- Gunakan `fetch()` + `FormData`.
- Gunakan header `X-CSRF-TOKEN` dari `csrf_token()`.
- Bila metode `PUT/PATCH/DELETE`, gunakan `_method` pada FormData.
- Handling error:
  - Baca `err.data.errors` (struktur Laravel validation).
  - Tandai field dengan `.is-invalid`.
  - Render pesan ke elemen `.invalid-feedback[data-field="..."]`.
- Notifikasi: gunakan `window.toastr?.success?.(...)` / `window.toastr?.error?.(...)`.

---

## 2.3 Notifikasi UI (Toastr) dan Flash Message

Untuk halaman non-AJAX (redirect), pola notifikasi di view adalah:

**Snippet (flash success/error/errors):**
```blade
@if(session('success'))
<script>
  (function(){
    var msg = @json(session('success'));
    if (window.toastr && toastr.success) { toastr.success(msg); }
    else { console.log('SUCCESS:', msg); }
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
```

**Aturan:**
- Bila controller memakai `with('success')` / `with('error')`, view wajib menyediakan render Toastr seperti ini (atau mengikuti halaman yang sudah ada).

---

## 2.4 Modal Detail dengan Fetch (Read-only)

Ada pola fetch saat modal dibuka (`show.bs.modal`) untuk mengambil detail log.

**Snippet:**
```js
modal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const id = button.getAttribute('data-id');
  fetch("{{ url('/notification-logs') }}/" + id)
    .then(res => res.json())
    .then(data => { body.innerHTML = `...`; })
    .catch(() => { body.innerHTML = '<div class="text-danger">Gagal memuat detail.</div>'; });
});
```

**Aturan:**
- Untuk modal detail/read-only, **boleh** fetch on-open dan render HTML template string.
- Pastikan escape minimal untuk konten raw (contoh: `.replace(/</g,'&lt;')`).

---

# 3) KONVENSI CONTROLLER & VALIDASI

## 3.1 Struktur Controller

- Controller berada di `app/Http/Controllers`.
- Banyak modul master data menggunakan `Route::resource(...)->except(['show'])`.
- Pola method yang dipakai:
  - `index()` untuk render listing.
  - `store(Request $request)` untuk create.
  - `update(Request $request, string $id)` untuk update.
  - `destroy(string $id)` untuk delete.

**Snippet (resource controller generik):**
```php
public function index()
{
    $items = Model::query()->latest()->get();
    return view('admin.<resource>.index', compact('items'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required','string','max:50'],
    ]);

    Model::create($validated);

    return redirect()->route('<resource>.index')->with('success', 'Data berhasil ditambahkan');
}
```

**Aturan:**
- Untuk master data, controller **mengembalikan redirect** + flash message.
- Data untuk view dikirim via `compact(...)`.

---

## 3.2 Validasi Input

### 3.2.1 Status Form Request

- Standar ini memilih validasi langsung pada controller via `$request->validate([...])` untuk menjaga pola tetap sederhana.

**Aturan wajib:**
- Jika menambah endpoint baru, gunakan validasi inline dengan `$request->validate([...])`.
- Jangan memperkenalkan FormRequest terpisah kecuali ada keputusan arsitektur eksplisit.

### 3.2.2 Validasi Bisnis Tambahan

Contoh validasi bisnis setelah validation rule:

**Snippet:**
```php
if (!empty($validated['homeroom_teacher_id'])) {
    $isTeacher = User::where('id', $validated['homeroom_teacher_id'])
        ->where('role', 'teacher')
        ->exists();
    if (!$isTeacher) {
        return back()->with('error', 'Role tidak sesuai.')->withInput();
    }
}
```

---

## 3.3 Standar Response

### 3.3.1 Redirect + Flash Message (Non-AJAX)

**Snippet:**
```php
return redirect()->route('<resource>.index')->with('success', 'Data berhasil ditambahkan');
```

### 3.3.2 JSON Response (AJAX)

Digunakan pada form AJAX.

**Snippet:**
```php
return response()->json([
    'message' => 'Berhasil disimpan',
    'data' => $data,
]);
```

Untuk error upload, controller mengembalikan struktur mirip validation:
```php
return response()->json([
    'message' => 'Gagal upload foto',
    'errors' => ['photo' => ['...']],
], 500);
```

**Aturan:**
- Response JSON **wajib** punya `message`.
- Jika error field-level, **wajib** gunakan `errors: { field: [msg] }` agar UI dapat menampilkan per-field.

---

# 4) MIDDLEWARE & AUTENTIKASI MULTI-ROLE

## 4.1 Middleware Role

- Alias middleware `role` didaftarkan pada `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    ...
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
```

- Implementasi `RoleMiddleware` ada di `app/Http/Middleware/RoleMiddleware.php`.

**Snippet:**
```php
public function handle(Request $request, Closure $next, ...$roles)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    foreach ($roles as $role) {
        if (Auth::user()->role == $role) {
            return $next($request);
        }
    }

    return abort(403, 'Unauthorized');
}
```

**Aturan:**
- Role check berbasis kolom `users.role` (string) dengan nilai sesuai kebutuhan proyek (contoh: `admin`, `teacher`, `student`).
- Unauthorized ditangani dengan `abort(403, 'Unauthorized')`.

---

## 4.2 Penerapan di Routes

Pola route group multi-role ada di `routes/web.php`.

**Snippet:**
```php
Route::group(['middleware' => ['auth','role:admin']], function () {
    Route::resource('batches', BatchController::class)->except(['show']);
    Route::resource('classes', SchoolClassController::class)->except(['show']);
    Route::resource('teachers', TeacherController::class)->except(['show']);
    Route::resource('students', StudentController::class)->except(['show']);
});

Route::group(['middleware' => ['auth','role:teacher']], function () {
    Route::prefix('teacher/attendances')->name('teacher.attendances.')->group(function () {
        Route::get('/', [TeacherAttendanceHistoryController::class, 'index'])->name('index');
        ...
    });

    Route::resource('teacher/students', TeacherStudentController::class)
        ->except(['show'])->names('teacher.students');
});

Route::group(['middleware' => ['auth','role:student']], function () {
    Route::get('/student/attendances', [StudentAttendanceController::class, 'index'])
        ->name('student.attendances.index');
});
```

**Aturan:**
- Admin: resource route sederhana (tanpa prefix khusus).
- Teacher: modul yang scoped memakai `prefix('teacher/...')` dan penamaan `teacher.*`.
- Student: prefix `student/...` dan penamaan `student.*`.

---

# 5) KONVENSI MODEL & ROUTING

## 5.1 Konvensi Model

### 5.1.1 Mass Assignment

Project menggunakan `$fillable` (bukan `$guarded`).

**Snippet (Student):**
```php
protected $fillable = [
  'user_id', 'nis', 'full_name', 'class_id', 'parent_name', 'parent_email',
];
```

**Aturan:**
- Model baru wajib mendefinisikan `$fillable` secara eksplisit.

### 5.1.2 UUID

Banyak model memakai trait `App\Traits\UsesUuid`.

**Snippet:**
```php
use App\Traits\UsesUuid;

class Student extends Model
{
    use HasFactory, UsesUuid;
}
```

**Aturan:**
- Jika menambah model baru, cek apakah tabel memakai UUID. Bila ya, gunakan `UsesUuid`.

### 5.1.3 Penamaan Relasi

Relasi memakai nama yang jelas dan konsisten:
- `Student::schoolClass()` (belongsTo)
- `SchoolClass::students()` (hasMany)
- `User::homeroomClasses()` (hasMany)
- `User::student()` (hasOne)

**Snippet:**
```php
public function schoolClass(): BelongsTo
{
    return $this->belongsTo(SchoolClass::class, 'class_id');
}
```

---

## 5.2 Konvensi Routing & Naming

- Route `resource()` dipakai untuk master data.
- Route group memakai middleware array dan kadang `prefix` + `name()`.
- Naming mengikuti Laravel default untuk resource:
  - `batches.index`, `batches.store`, `batches.update`, dll.
- Untuk modul teacher yang resource tetapi path mengandung slash, gunakan `->names('teacher.students')`.

**Aturan:**
- Jika membuat route baru untuk role tertentu:
  - Masukkan ke group `['middleware' => ['auth','role:<role>']]`.
  - Pakai prefix konsisten `teacher/...` atau `student/...`.
  - Pastikan `name()` mengikuti namespace `teacher.*` atau `student.*`.

---

# 6) Template Wajib untuk Modul CRUD Baru (Checklist)

Gunakan checklist ini setiap kali menambah modul master data baru.

- **View**
  - `@extends('layouts.master')`
  - Header halaman konsisten (`h3` + tombol `Tambah` membuka modal)
  - Table markup Metronic + `id="<resource>_table"`
  - Modal Create/Edit:
    - `<form method="POST" action="{{ route('<resource>.store') }}">`
    - `@csrf`
    - hidden `_method` id `...FormMethod`
  - Modal Delete Confirm:
    - `<form id="deleteForm" method="POST">` + `@method('DELETE')`
  - `@push('scripts')`:
    - init DataTable
    - handler tombol add/edit/delete yang set `form.action`, `_method`, isi input, set title
  - Flash message Toastr untuk `session('success')`, `session('error')`, dan `$errors->any()`

- **Controller**
  - Resource controller methods: `index`, `store`, `update`, `destroy`
  - Validasi inline via `$request->validate([...])`
  - Persist via `Model::create($validated)` / `$model->update($validated)`
  - Response: `redirect()->route('<resource>.index')->with('success', '...')`

- **Routes**
  - Tambahkan pada group role yang sesuai.
  - Gunakan `Route::resource(...)->except(['show'])`.

---

# 7) Larangan / Anti-Pattern

- Jangan membuat halaman `create.blade.php` / `edit.blade.php` untuk master data jika pola modul sejenis memakai modal.
- Jangan mengubah layout global tanpa alasan kuat; semua halaman harus tetap kompatibel dengan `layouts.master`.
- Jangan menambahkan FormRequest baru tanpa kebutuhan dan keputusan arsitektur, karena standar ini memilih `$request->validate()`.
- Jangan memperkenalkan library notifikasi baru (mis. SweetAlert) untuk modul baru; gunakan `toastr` sebagaimana yang sudah dipakai.

---

# Status

Dokumen `Agents.md` ini adalah **template pedoman**. Saat dipakai di proyek lain:

- Pastikan path layout, asset, dan vendor UI mengikuti struktur proyek tersebut.
- Jika role/prefix route berbeda, sesuaikan bagian middleware dan routing.
- Jika proyek memilih server-side DataTables atau FormRequest, buat keputusan arsitektur eksplisit dan konsisten di seluruh modul.