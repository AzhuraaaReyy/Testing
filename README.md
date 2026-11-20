# Sistem Manajemen Sekolah

[![CI](https://github.com/AzhuraaaReyy/Testing/actions/workflows/ci.yml/badge.svg)](https://github.com/AzhuraaaReyy/Testing/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5-FDAE4B)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0-6BA539?logo=openapiinitiative&logoColor=white)

Aplikasi web untuk mengelola data inti sekolah: **Siswa**, **Kelas**, dan **Guru**, beserta relasi
antardata dan laporan per Kelas. Tersedia tiga pintu akses: **panel Admin** (Filament), **portal
Guru/Siswa** (Blade, hanya-baca), dan **REST API v1** (Laravel Sanctum) yang terdokumentasi dengan
OpenAPI sehingga dapat diuji lewat Postman, OWASP ZAP, dan alat sejenis.

Seluruh antarmuka, pesan validasi, dan pesan error berbahasa Indonesia.

---

## Daftar Isi

- [Fitur](#fitur)
- [Peran dan Hak Akses](#peran-dan-hak-akses)
- [Teknologi](#teknologi)
- [Struktur Proyek](#struktur-proyek)
- [Model Data](#model-data)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Penggunaan](#penggunaan)
- [REST API](#rest-api)
- [Pengujian API dengan Postman](#pengujian-api-dengan-postman)
- [Pengujian Otomatis](#pengujian-otomatis)
- [CI/CD](#cicd)
- [Keamanan](#keamanan)
- [Deploy ke Production](#deploy-ke-production)
- [Lisensi](#lisensi)

---

## Fitur

| Area | Fitur |
| --- | --- |
| Autentikasi | Login terpisah untuk Admin (`/admin/login`) dan Guru/Siswa (`/login`), pesan gagal umum, maksimal 3 kegagalan per email lalu dikunci 15 menit, batas per IP, pemberitahuan session berakhir |
| Dashboard | Ringkasan total Siswa, Kelas, dan Guru aktif, serta distribusi per Kelas |
| Data Master | CRUD Siswa, Kelas, dan Guru dengan validasi, konfirmasi batal/hapus, dan kolom nomor urut |
| Trash | Data yang dihapus masuk Trash (soft delete) dan dapat dipulihkan; relasi Kelas dilepas agar data tetap konsisten |
| Akun portal | Admin dapat membuat akun login untuk Guru/Siswa langsung dari form data |
| Laporan | Tiga laporan per Kelas: Siswa per Kelas, Guru per Kelas, serta gabungan Siswa–Kelas–Guru (10 Kelas per halaman) |
| Portal Guru/Siswa | Dashboard dan tiga laporan, dibatasi ke Kelas yang diajar (Guru) atau Kelas sendiri (Siswa) |
| REST API v1 | Login token, ringkasan, laporan, dan CRUD + Trash untuk Admin; spesifikasi OpenAPI 3 |

## Peran dan Hak Akses

| Peran | Pintu masuk | Akses |
| --- | --- | --- |
| **Admin** | `/admin` dan API | Seluruh fungsi: Dashboard, CRUD, Trash, dan tiga laporan untuk seluruh Kelas |
| **Guru** | `/portal` dan API | Hanya-baca: Dashboard dan tiga laporan untuk Kelas yang diajar |
| **Siswa** | `/portal` dan API | Hanya-baca: Dashboard dan tiga laporan untuk Kelasnya sendiri |

Akun Guru/Siswa yang membuka panel Admin (atau sebaliknya) diarahkan ke pintunya sendiri dengan
penjelasan, bukan halaman 403.

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13, Eloquent ORM |
| Panel Admin | Filament 5, Livewire 4 |
| Portal | Laravel Blade, TypeScript (strict), Tailwind CSS 4, Vite |
| API | REST JSON v1, Laravel Sanctum (Bearer token), OpenAPI 3.0 |
| Database | MySQL 8 (aplikasi), SQLite in-memory (test) |
| Kualitas kode | Pest 4, PHPStan/Larastan level 8, Laravel Pint |
| CI/CD | GitHub Actions, Dependabot |

## Struktur Proyek

```text
.
├── app/
│   ├── Actions/          # Logika bisnis (satu aksi = satu kelas): Auth, DataMaster, Laporan, Dashboard, Admin
│   ├── Console/Commands/ # Perintah artisan, mis. sekolah:buat-admin
│   ├── DTOs/             # Objek data antarlapisan (Dashboard, Laporan, Navigation)
│   ├── Enums/            # Role, PintuLogin, Tingkat, JenisKelamin, JenisLaporan, Icon
│   ├── Exceptions/       # ApiExceptionRenderer: error API JSON berbahasa Indonesia
│   ├── Filament/         # Panel Admin: Resources (Siswa, Kelas, Guru), Pages, Widgets, Forms, Tables
│   ├── Http/
│   │   ├── Controllers/  # Portal (Blade) dan Api/ (REST v1)
│   │   ├── Middleware/   # EnsureRole, PreventSensitiveCaching, SecurityHeaders
│   │   ├── Requests/     # Satu-satunya tempat aturan validasi (dipakai Filament, portal, dan API)
│   │   └── Resources/    # JSON Resource untuk respons API
│   ├── Models/           # User, Siswa, Kelas, Guru
│   ├── Providers/        # AppServiceProvider dan Filament/AdminPanelProvider
│   ├── Services/         # KelasAccessService (pembatasan Kelas per peran), NavigationService
│   ├── Support/          # Helper kecil (Format, SesiLogin)
│   └── View/Components/  # Komponen Blade (Molecules, Organisms)
├── bootstrap/app.php     # Routing, middleware global, dan penanganan exception
├── config/               # Konfigurasi Laravel, Sanctum, session, dll.
├── database/
│   ├── factories/        # Data palsu untuk test dan seeder
│   ├── migrations/       # Skema: kelas, siswa, guru, guru_kelas, users (role), token API
│   └── seeders/          # Data contoh pengembangan (ditolak di production)
├── docs/api/openapi.yaml # Spesifikasi REST API (impor ke Postman)
├── lang/id/              # Terjemahan Indonesia (validasi, error, Filament)
├── resources/
│   ├── css/              # Design token, komponen, dan tema Filament (Tailwind 4)
│   ├── js/               # TypeScript: komponen UI, layanan HTTP, types/contracts.ts
│   └── views/            # Blade: auth, portal, laporan, halaman error, komponen
├── routes/
│   ├── web.php           # Login portal dan halaman portal
│   ├── api.php           # REST API v1
│   └── console.php       # Perintah dan jadwal (pembersihan token)
├── tests/
│   ├── Feature/          # Admin, Api, Portal, Laporan, alur login, keamanan
│   └── Unit/             # Arsitektur, sinkronisasi kontrak TS, terjemahan, CI, .gitignore
└── .github/              # Workflow CI dan Dependabot
```

### Konvensi Arsitektur

- **Logika bisnis** berada di `app/Actions` atau `app/Services`, bukan di controller atau Filament.
- **Validasi** hanya di `app/Http/Requests`. Panel Admin, portal, dan API memakai FormRequest yang
  sama, sehingga aturan dan pesannya selalu identik.
- **Kontrak frontend**: setiap Enum, DTO, dan FormRequest dicerminkan di
  `resources/js/types/contracts.ts`, dijaga oleh `tests/Unit/ContractSyncTest.php`.
- Aturan ini diperiksa otomatis oleh `tests/Unit/ArsitekturTest.php`.

## Model Data

```text
kelas 1 ──< siswa             (satu Siswa berada di satu Kelas)
kelas >──< guru  (guru_kelas)  (satu Guru dapat mengajar banyak Kelas)
users ──── siswa | guru        (akun portal opsional untuk Siswa/Guru)
```

| Tabel | Kolom utama |
| --- | --- |
| `kelas` | `nama_kelas` (unik), `tingkat` (X, XI, XII), `jurusan`, `keterangan` |
| `siswa` | `nis` (unik), `nama`, `jenis_kelamin` (L/P), `alamat`, `kelas_id` |
| `guru` | `nip` (opsional, unik), `nama`, `jenis_kelamin`, `alamat`, `no_telepon` |
| `guru_kelas` | `guru_id`, `kelas_id` (pasangan unik) |
| `users` | `name`, `email`, `password`, `role` (admin/guru/siswa), `siswa_id`, `guru_id` |

Siswa, Kelas, dan Guru memakai soft delete (Trash).

## Instalasi

### Prasyarat

- PHP 8.3 atau 8.4 dengan ekstensi `mbstring`, `intl`, `pdo_mysql`, `pdo_sqlite`, `zip`, `fileinfo`, `dom`, `xml`
- Composer 2
- Node.js 24 dan npm
- MySQL 8

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/AzhuraaaReyy/Testing.git
cd Testing

# 2. Buat database MySQL kosong (nama bawaan di .env.example)
mysql -u root -e "CREATE DATABASE testing_sekolah"

# 3. Pasang dependency, buat .env, generate APP_KEY, migrasi, dan build aset
composer run setup

# 4. Isi data contoh (hanya untuk pengembangan)
php artisan db:seed
```

Sesuaikan `DB_*` di `.env` bila kredensial MySQL Anda berbeda.

## Menjalankan Aplikasi

```bash
composer run dev
```

Atau jalankan terpisah di dua terminal:

```bash
php artisan serve   # http://localhost:8000
npm run dev         # Vite (hot reload aset)
```

### Akun Contoh

Dibuat oleh `php artisan db:seed`. Password semua akun: `password`. **Hanya untuk pengembangan lokal.**

| Peran | Email | Halaman login |
| --- | --- | --- |
| Admin | `admin@sekolah.test` | http://localhost:8000/admin/login |
| Guru | `guru@sekolah.test` | http://localhost:8000/login |
| Siswa | `siswa@sekolah.test` | http://localhost:8000/login |

Seeder juga membuat 14 Kelas, Siswa dan Guru acak, serta beberapa data di Trash dan kasus tepi
(Kelas tanpa Siswa/Guru, Guru di banyak Kelas, Guru tanpa NIP).

### Membuat Akun Admin

```bash
php artisan sekolah:buat-admin
```

Password diminta secara interaktif (tidak lewat opsi, agar tidak tersimpan di riwayat shell):
minimal 8 karakter, memuat huruf dan angka.

## Penggunaan

### Panel Admin (`/admin`)

1. Login di `/admin/login` dengan akun Admin.
2. **Dashboard**: ringkasan total Siswa, Kelas, Guru, dan distribusi per Kelas.
3. **Data Master → Kelas / Siswa / Guru**: tambah, lihat, ubah, dan hapus data. Bagian *Akun Portal*
   pada form Siswa/Guru membuat akun login portal (kosongkan email bila tidak perlu).
4. **Trash**: buka tab **Trash** di halaman daftar Kelas/Siswa/Guru untuk melihat dan memulihkan data. Memulihkan Siswa
   wajib memilih Kelas aktif; memulihkan Guru wajib memilih minimal satu Kelas aktif.
5. **Laporan**: Daftar Siswa per Kelas, Daftar Guru per Kelas, dan Daftar Siswa, Kelas, dan Guru.

### Portal Guru/Siswa (`/portal`)

1. Login di `/login` dengan akun Guru atau Siswa.
2. Dashboard menampilkan ringkasan sesuai peran.
3. Menu laporan menampilkan data yang dibatasi ke Kelas yang diajar (Guru) atau Kelas sendiri (Siswa).
   Portal bersifat hanya-baca.

## REST API

- **Base URL**: `http://localhost:8000/api/v1`
- **Spesifikasi lengkap**: [`docs/api/openapi.yaml`](docs/api/openapi.yaml)
- **Autentikasi**: `POST /login` mengembalikan `token`; kirim pada setiap permintaan sebagai
  `Authorization: Bearer <token>`. Token berlaku 120 menit dan dicabut saat `POST /logout`.

### Daftar Endpoint

| Metode | Endpoint | Akses | Keterangan |
| --- | --- | --- | --- |
| POST | `/login` | Publik | Login, mendapatkan Bearer token |
| POST | `/logout` | Semua peran | Mencabut token yang dipakai |
| GET | `/me` | Semua peran | Akun yang sedang login |
| GET | `/ringkasan` | Semua peran | Total Siswa, Kelas, dan Guru aktif |
| GET | `/laporan/{jenis}` | Semua peran | `siswa-per-kelas`, `guru-per-kelas`, `siswa-kelas-guru` (dibatasi per peran) |
| GET, POST | `/siswa` | Admin | Daftar (`?page=`, `?trash=1`) dan tambah Siswa |
| GET, PUT, DELETE | `/siswa/{id}` | Admin | Detail, ubah, hapus (ke Trash) |
| POST | `/siswa/{id}/pulihkan` | Admin | Pulihkan dari Trash (`kelas_id` wajib) |
| GET, POST | `/kelas` | Admin | Daftar dan tambah Kelas |
| GET, PUT, DELETE | `/kelas/{id}` | Admin | Detail, ubah, hapus (ke Trash) |
| POST | `/kelas/{id}/pulihkan` | Admin | Pulihkan dari Trash |
| GET, POST | `/guru` | Admin | Daftar dan tambah Guru |
| GET, PUT, DELETE | `/guru/{id}` | Admin | Detail, ubah, hapus (ke Trash) |
| POST | `/guru/{id}/pulihkan` | Admin | Pulihkan dari Trash (`kelas` minimal satu id) |

Daftar data memakai pagination 10 data per halaman (`data`, `links`, `meta`).

### Kode Status

| Kode | Arti |
| --- | --- |
| 200 / 201 | Berhasil / data dibuat |
| 400 | Permintaan tidak valid (mis. Host header asing di production) |
| 401 | Belum login atau token tidak valid/kedaluwarsa |
| 403 | Peran tidak memiliki akses (mis. Guru/Siswa membuka endpoint CRUD) |
| 404 | Endpoint atau data tidak ditemukan |
| 409 | Bentrok aturan bisnis (mis. Kelas masih memiliki Siswa aktif) |
| 422 | Validasi gagal, atau login dikunci sementara |
| 429 | Melebihi 60 permintaan per menit |

Semua error berformat `{ "message": "..." }` berbahasa Indonesia; error validasi (422) menambahkan
objek `errors` per field.

## Pengujian API dengan Postman

### 1. Impor Spesifikasi

1. Jalankan aplikasi (`php artisan serve`) dan pastikan data contoh sudah di-seed.
2. Di Postman: **Import → Files → pilih `docs/api/openapi.yaml`** → pilih *Postman Collection*.
3. Postman membuat koleksi **Sistem Manajemen Sekolah API** berisi seluruh endpoint, lengkap dengan
   contoh body.

### 2. Atur Variabel Koleksi

Buka koleksi → tab **Variables**:

| Variabel | Nilai |
| --- | --- |
| `baseUrl` | `http://localhost:8000/api/v1` |
| `token` | *(kosong, terisi otomatis setelah login)* |

Lalu di tab **Authorization** koleksi pilih **Bearer Token** dengan nilai `{{token}}`, dan pastikan
setiap request memakai *Inherit auth from parent*. Tambahkan juga header `Accept: application/json`.

### 3. Simpan Token Otomatis Setelah Login

Pada request `POST /login`, buka tab **Scripts → Post-response** dan isi:

```javascript
pm.test('Login berhasil', () => pm.response.to.have.status(200));

const data = pm.response.json();
pm.collectionVariables.set('token', data.token);
```

Body request login (raw, JSON):

```json
{ "email": "admin@sekolah.test", "password": "password" }
```

Setelah dikirim, semua request lain dalam koleksi otomatis memakai token tersebut.

### 4. Contoh Body Request

Tambah Kelas (`POST /kelas`):

```json
{ "nama_kelas": "X IPA 3", "tingkat": "X", "jurusan": "IPA", "keterangan": "Ruang 101" }
```

Tambah Siswa beserta akun portal (`POST /siswa`):

```json
{
  "nis": "00123",
  "nama": "Ani Lestari",
  "jenis_kelamin": "P",
  "alamat": "Jl. Melati 1",
  "kelas_id": 1,
  "akun": { "email": "ani@sekolah.test", "password": "rahasia123" }
}
```

Tambah Guru (`POST /guru`):

```json
{
  "nip": "198001012005011001",
  "nama": "Budi Santoso",
  "jenis_kelamin": "L",
  "alamat": "Jl. Mawar 2",
  "no_telepon": "081234567890",
  "kelas": [1, 2]
}
```

### 5. Skenario Uji yang Disarankan

| No | Skenario | Langkah | Hasil yang diharapkan |
| --- | --- | --- | --- |
| 1 | Login Admin | `POST /login` dengan akun Admin | 200, berisi `token` |
| 2 | Login gagal | Password salah | 422, pesan umum *"Email atau password yang Anda masukkan salah."* |
| 3 | Penguncian login | Password salah 3 kali untuk email yang sama | Percobaan ke-4: 422 *"Terlalu banyak percobaan login…"* |
| 4 | Tanpa token | `GET /me` tanpa header Authorization | 401 |
| 5 | Akses berdasarkan peran | Login Siswa → `GET /siswa` | 403 |
| 6 | Laporan terbatas | Login Siswa → `GET /laporan/siswa-per-kelas` | Hanya Kelas milik Siswa tersebut |
| 7 | Validasi | `POST /siswa` dengan body kosong | 422 dengan `errors` per field |
| 8 | CRUD lengkap | Tambah → lihat → ubah → hapus → `GET /siswa?trash=1` → pulihkan | 201 → 200 → 200 → 200 *"Data berhasil dipindahkan ke Trash."* → muncul di Trash → 200 |
| 9 | Aturan bisnis | Hapus Kelas yang masih memiliki Siswa aktif | 409 |
| 10 | Logout | `POST /logout`, lalu `GET /me` dengan token yang sama | 200 *"Anda berhasil keluar dari sistem."*, lalu 401 |

> Batas login: 3 kegagalan per email (15 menit) dan 20 kegagalan per IP. Bila terkunci saat
> menguji, tunggu 15 menit atau jalankan `php artisan cache:clear`.

### Alat Lain

Spesifikasi yang sama dapat diimpor ke **OWASP ZAP** (*Import → Import an OpenAPI definition*),
Insomnia, atau Swagger UI. Untuk ZAP, isi header `Authorization: Bearer <token>` agar endpoint
terlindungi ikut dipindai.

## Pengujian Otomatis

Test berjalan di SQLite in-memory dan tidak menyentuh database pengembangan.

```bash
php artisan test                 # seluruh test (Pest)
php artisan test --filter=Siswa  # sebagian test
composer test                    # bersihkan cache config lalu jalankan test
```

Pemeriksaan kualitas yang sama dengan CI:

```bash
composer validate --strict
vendor/bin/pint --test           # format kode
composer analyse                 # PHPStan level 8
npm run typecheck                # TypeScript strict
npm run build                    # build aset produksi
```

Menjalankan test di MySQL (seperti job CI `mysql`):

```bash
mysql -u root -e "CREATE DATABASE testing_sekolah_ci"
DB_CONNECTION=mysql DB_DATABASE=testing_sekolah_ci DB_HOST=127.0.0.1 DB_USERNAME=root php artisan test
```

### Cakupan Test

| Folder | Yang diuji |
| --- | --- |
| `tests/Feature/Admin` | Panel Admin, CRUD, integritas data, Bahasa Indonesia, kesesuaian PRD, perintah buat-admin |
| `tests/Feature/Api` | Autentikasi token, CRUD API, error JSON, laporan, sinkronisasi OpenAPI, pembersihan token |
| `tests/Feature/Portal` | Login dan pembatasan akses portal |
| `tests/Feature/Laporan` | Isi dan urutan laporan per Kelas |
| `tests/Feature/*.php` | Alur login lintas pintu, header keamanan, Host tepercaya, waktu login, mode debug, seeder |
| `tests/Unit` | Aturan arsitektur, kontrak TypeScript, terjemahan, workflow CI, `.gitignore` |

## CI/CD

Workflow [`.github/workflows/ci.yml`](.github/workflows/ci.yml) berjalan pada setiap push ke `main`
dan setiap Pull Request:

1. **kualitas**: `composer validate`, Pint, PHPStan level 8, TypeScript, dan lockfile.
2. **keamanan**: `composer audit` dan `npm audit`.
3. **test**: Pest di SQLite (PHP 8.3 dan 8.4) serta MySQL 8.4.

Semua Action dikunci ke SHA commit. [Dependabot](.github/dependabot.yml) memperbarui dependency
Composer, npm, dan GitHub Actions setiap minggu.

## Keamanan

- Pembatasan login per email (3 kali / 15 menit) dan per IP (20 kali / 15 menit) di semua pintu.
- Waktu respons login gagal diseragamkan, sehingga email terdaftar tidak dapat ditebak.
- Header keamanan di semua respons: `Content-Security-Policy`, `X-Frame-Options`,
  `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, serta HSTS di production.
  CSP dibedakan per area: portal ketat berbasis nonce, API tanpa sumber daya apa pun, dan panel
  Admin sengaja masih mengizinkan script/style inline serta `unsafe-eval` karena dibutuhkan
  Livewire/Alpine (lihat `app/Http/Middleware/SecurityHeaders.php`).
- CORS API hanya untuk origin `APP_URL` (tambahan via `CORS_ALLOWED_ORIGINS`); token API berprefix
  `sekolah_` dan maksimal 5 token aktif per akun.
- Validasi Host header dan penyaringan URL tujuan setelah login (mencegah open redirect).
- Aturan password: minimal 8 karakter, memuat huruf dan angka; di production dicek terhadap daftar
  password bocor (Have I Been Pwned, k-anonymity).
- Seeder data contoh ditolak di production; token API kedaluwarsa dihapus otomatis setiap hari.
- Session portal dan token API lama otomatis dicabut saat Admin mengganti password akun portal.
- Kejadian keamanan (login gagal/terkunci/berhasil, akses lintas peran, perubahan akun) dicatat di
  `storage/logs/security-YYYY-MM-DD.log`, dengan email disamarkan dan tanpa password.
- Error tidak membocorkan detail internal saat `APP_DEBUG=false`.

Temukan celah keamanan? Mohon laporkan secara pribadi kepada pemilik repository, jangan melalui
issue publik.

## Deploy ke Production

Panduan lengkap ada di bagian atas [`.env.example`](.env.example). Ringkasnya:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate        # hanya sekali, saat instalasi pertama
php artisan migrate --force
php artisan sekolah:buat-admin  # jangan memakai db:seed di production
php artisan optimize
```

Wajib diperhatikan:

- `.env`: `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` berisi domain asli (Host lain ditolak).
- Gunakan HTTPS. Cookie session otomatis `Secure` di production.
- Di balik reverse proxy/load balancer: atur `trustProxies` agar IP pengguna dan HTTPS terbaca benar.
- File statis (`/build`, `robots.txt`) dilayani web server tanpa melewati Laravel. Apache sudah
  diatur lewat `public/.htaccess`; untuk Nginx tambahkan di blok `server`:

  ```nginx
  add_header X-Content-Type-Options "nosniff" always;
  ```
- Pasang cron scheduler:

  ```cron
  * * * * * cd /path/ke/aplikasi && php artisan schedule:run >> /dev/null 2>&1
  ```

## Lisensi

Proyek ini dibangun di atas [Laravel](https://laravel.com) yang berlisensi MIT. Lisensi proyek
mengikuti ketentuan pemilik repository.
