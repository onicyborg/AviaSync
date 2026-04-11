# AviaSync — Panduan Setup Local Development

Dokumentasi ini menjelaskan cara menjalankan AviaSync (Laravel 10 + PostgreSQL + UUID + Metronic) di komputer lokal, dari clone sampai aplikasi bisa diakses.

Referensi standar pengembangan: lihat `AGENTS.md`.

---

## 1) Prasyarat

- PHP 8.1+
- Composer 2.x
- Node.js 18+ dan npm
- PostgreSQL 13+
- Ekstensi PHP umum Laravel (minimal):
  - `pdo_pgsql`
  - `openssl`
  - `mbstring`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `fileinfo`

---

## 2) Clone Project

```bash
git clone https://github.com/onicyborg/AviaSync.git
cd AviaSync
```

---

## 3) Install Dependency

```bash
composer install
npm install
```

---

## 4) Konfigurasi Environment

Salin env dan generate key:

```bash
cp .env.example .env
php artisan key:generate
```

Konfigurasi DB (PostgreSQL) di `.env` (contoh):

```env
APP_NAME=AviaSync
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aviasync
DB_USERNAME=postgres
DB_PASSWORD=postgres

# IMPORTANT:
# Jangan set ASSET_URL (karena URL storage publik di proyek ini wajib pakai url('storage/...'))
```

---

## 5) Buat Database

Buat database sesuai `DB_DATABASE` (contoh: `aviasync`).

---

## 6) Migrasi + Seeder

```bash
php artisan migrate --seed
```

Jika ingin reset total database:

```bash
php artisan migrate:fresh --seed
```

Seeder membuat:
- 1 akun admin
- 1 akun crew untuk testing
- data demo (crew, certifications, health records, flight schedules)

---

## 7) Storage Symlink

Wajib agar file di `storage/app/public` bisa diakses via `public/storage`.

```bash
php artisan storage:link
```

Catatan:
- Di proyek ini, link file lampiran wajib memakai `url('storage/...')` (bukan `asset()`), supaya tidak terpengaruh konfigurasi `ASSET_URL`.

---

## 8) Jalankan Vite (Asset)

Mode development:

```bash
npm run dev
```

Atau build production:

```bash
npm run build
```

---

## 9) Jalankan Server Laravel

```bash
php artisan serve
```

Lalu akses:
- `http://127.0.0.1:8000`

---

## 10) Akun Login Default

- Admin:
  - Email: `admin@aviasync.com`
  - Password: `Qwerty123*`
- Crew:
  - Email: `crew@aviasync.com`
  - Password: `Qwerty123*`

---

## 11) Troubleshooting

### A) Error koneksi PostgreSQL saat migrate/seed
- Pastikan Postgres sedang running.
- Pastikan database sudah dibuat.
- Pastikan `.env` benar.

### B) `storage` tidak bisa diakses / gambar tidak tampil
- Jalankan:
  - `php artisan storage:link`
- Pastikan `public/storage` benar-benar terbentuk.

### C) Error seed: unique `flight_number`
- Jika terjadi, jalankan ulang dengan database bersih:
  - `php artisan migrate:fresh --seed`

### D) Asset tidak muncul
- Pastikan `npm run dev` berjalan tanpa error.
- Coba bersihkan cache:
  - `php artisan optimize:clear`

### E) Halaman error 500
- Cek log:
  - `storage/logs/laravel.log`

---

## 12) Command yang Sering Dipakai

```bash
# Clear cache
php artisan optimize:clear

# Reset DB + seed
php artisan migrate:fresh --seed

# Run local server
php artisan serve
```

