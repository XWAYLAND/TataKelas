# TataKelas

**Workspace Murid & Sistem Pembelajaran** — TataKelas adalah web yang membantu murid mengatur workspace belajar pribadinya sekaligus mengikuti sistem pembelajaran berbasis classroom. Murid menyimpan folder, referensi, dan catatan secara privat; guru membagikan materi dan tugas, menerima pengumpulan, lalu memberi nilai dan feedback.

TataKelas dibangun dengan Laravel 12 dan Blade. Setiap pengguna hanya dapat melihat dan mengubah data miliknya sendiri. Folder, resource belajar, catatan, dan foto diisolasi per akun.

> Dokumen kebutuhan produk tersedia di [`docs/PRD.md`](docs/PRD.md).

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Role Pengguna](#role-pengguna)
- [Teknologi](#teknologi)
- [Arsitektur Singkat](#arsitektur-singkat)
- [Struktur Direktori](#struktur-direktori)
- [Prasyarat](#prasyarat)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Seeding Data Contoh](#seeding-data-contoh)
- [Akun Demo](#akun-demo)
- [Daftar Route](#daftar-route)
- [Model Data](#model-data)
- [Aturan Validasi](#aturan-validasi)
- [Keamanan dan Isolasi Data](#keamanan-dan-isolasi-data)
- [Pengujian](#pengujian)
- [Troubleshooting](#troubleshooting)
- [Optimasi Performa](#optimasi-performa)
- [Status Implementasi dan Keterbatasan](#status-implementasi-dan-keterbatasan)
- [Roadmap](#roadmap)
- [Lisensi](#lisensi)

---

## Tentang Proyek

TataKelas menggabungkan dua ruang belajar: workspace privat murid untuk mengelola bahan belajarnya sendiri, dan learning system berbasis classroom untuk materi, tugas, pengumpulan, serta penilaian.

Masalah yang ingin diselesaikan:

- Resource belajar tersebar di banyak browser, chat, dan aplikasi catatan.
- Bookmark kehilangan konteks setelah beberapa minggu.
- Catatan dan screenshot tidak tersimpan bersama resource terkait.
- Data pribadi tidak boleh terlihat oleh pengguna lain.

TataKelas mengelompokkan bahan belajar ke dalam folder, misalnya `Laravel Development`, `JavaScript & React`, classroom `Biology 101`, atau tugas per pertemuan.

## Fitur Utama

### Autentikasi

- Registrasi akun dengan nama, email, dan password.
- Login dengan email dan password, termasuk opsi **Remember me**.
- Tanpa opsi **Remember me**, cookie sesi berakhir saat browser ditutup; opsi tersebut membuat login tetap tersimpan pada perangkat pribadi.
- Menutup tab saja tidak mengakhiri sesi browser. Perilaku memulihkan sesi saat browser dibuka kembali juga bergantung pada pengaturan browser.
- Ganti kata sandi melalui halaman akun dengan verifikasi kata sandi saat ini.
- Logout aman dengan invalidasi session dan regenerasi CSRF token.
- Rate limiting login dan registrasi: maksimal **6 percobaan per menit**.
- Password di-hash otomatis oleh Laravel.
- Pengguna pertama yang mendaftar akan mengklaim folder lama yang belum memiliki pemilik.
- Akun yang mendaftar mendapat role `student`; role `teacher` atau `admin` diberikan melalui proses administrasi, bukan dari form registrasi.

### Manajemen Folder

- Membuat, melihat, mengubah, dan menghapus folder.
- Setiap folder memiliki nama, deskripsi opsional, dan warna identitas.
- Warna folder dapat dipilih lewat color picker atau preset.
- Dashboard menampilkan kartu folder, jumlah resource, dan ringkasan statistik.
- Halaman detail folder menampilkan tab **Resources**, **Notes**, dan **Photos** dengan pagination terpisah per tab.
- Menghapus folder akan menghapus seluruh resource, catatan, metadata foto, dan **file fisik foto** di dalamnya.
- Folder privat hanya dapat dilihat pemiliknya, termasuk saat pemiliknya seorang murid. Guru dan admin tidak mendapat akses ke folder privat murid.

### Classroom dan LMS

- Guru dan admin dapat membuat classroom dengan kode gabung.
- Murid dapat bergabung menggunakan kode dari guru.
- Guru lain dapat bergabung ke classroom sebagai **co-teacher** melalui kode kelas yang sama. Co-teacher dapat mengelola materi dan menilai submission; hanya pemilik classroom yang dapat mengubah pengaturan atau mengeluarkan co-teacher. Co-teacher juga dapat keluar sendiri.
- Guru/admin dapat membagikan folder materi atau tugas ke classroom.
- Materi mendukung tautan dan file PDF/Office yang diunduh melalui route terautentikasi oleh anggota kelas.
- **Materi PDF dapat dipratinjau langsung di browser** (preview) tanpa perlu diunduh, melalui route terautentikasi `resources.preview`.
- Tugas memiliki instruksi dan tenggat, serta menerima jawaban teks, URL, atau beberapa berkas sekaligus.
- Pengumpulan terlambat tetap diterima dan diberi status `late`; guru/admin classroom memberi nilai 0–100 dan feedback.
- File materi dan submission disimpan di disk privat, bukan URL publik.

### Manajemen Resource

- Menambah bookmark/resource ke folder.
- Jenis resource: `url`, `file`, `video`, `documentation`, `assignment_instruction`.
- **Field upload berkas hanya tampil ketika jenis resource adalah `file` (PDF/dokumen)**; memilih jenis lain menyembunyikan field tersebut secara otomatis. Perilaku yang sama berlaku pada form materi/tugas classroom, termasuk ketika form memuat data lama (validasi error).
- Mengubah resource berkas tanpa mengunggah ulang berkas baru.
- **PDF dapat dipratinjau di browser** melalui route terautentikasi `resources.preview` tanpa proses download; tombol unduh tetap tersedia.
- Status baca: `to_read`, `reading`, `completed`.
- Mengubah status baca langsung dari daftar resource.
- Memindahkan resource ke folder lain.
- Mencari resource berdasarkan judul, URL, atau catatan.
- Memfilter resource berdasarkan folder, jenis, dan status baca.
- Pagination 20 item per halaman.

### Administrasi Sistem (Admin)

- **Dashboard admin** `/admin` dengan ringkasan akun per role/status, classroom, resource, submission belum dinilai, akun baru 30 hari, dan aktivitas admin terbaru.
- **Direktori pengguna** `/admin/users` dengan pencarian nama/email, filter role, status, verifikasi email, rentang tanggal bergabung, sorting, pagination, dan ringkasan jumlah per kategori.
- **Detail pengguna** `/admin/users/{id}` berisi classroom yang diajar/diikuti/dibantu, jumlah data terkait, login terakhir, status akun, dan riwayat audit akun tersebut.
- **Penangguhan akun (suspend)** dengan alasan opsional. Akun ditangguhkan:
  - tidak dapat login (validasi saat `AuthController@login` menolak kredensial yang benar);
  - langsung di-logout dari sesi aktif melalui middleware `active` yang melekat pada seluruh route terautentikasi;
  - remember-me dihapus karena `remember_token` dirotasi.
- **Reaktivasi akun** mengembalikan status aktif dan menghapus alasan penangguhan.
- **Aksi massal** suspend/reactivate/ubah role hingga 100 akun, dengan laporan jumlah terproses dan daftar akun yang dilewati beserta alasannya.
- **Guards keamanan akun**: admin tidak dapat menangguhkan akunnya sendiri, admin aktif terakhir tidak dapat ditangguhkan, role/hapus akun dalam keadaan tertentu ditolak (detail di bagian [Keamanan](#keamanan-dan-isolasi-data)).
- **Export CSV** daftar pengguna dan audit log mengikuti filter yang aktif.
- **Audit log** tindakan admin (perubahan role, reset password, penangguhan, reaktivasi, penghapusan akun) dengan filter tanggal/aksi/pencarian, tautan ke akun terkait, dan export CSV. Password sementara tidak pernah dicatat di audit log.
- **Reset password sementara**: password baru dihasilkan random (hanya tampil sekali di UI) dan sesi remember-me lama dicabut.

### Manajemen Catatan

- Membuat, melihat, mengubah, dan menghapus catatan di dalam folder.
- Halaman **All Notes** untuk melihat seluruh catatan lintas folder.
- Halaman detail catatan dengan isi lengkap, prioritas, dan tautan folder.
- Prioritas catatan: `low`, `medium`, `high`.
- Mencari catatan berdasarkan judul atau isi.
- Memfilter catatan berdasarkan prioritas dan folder.
- Pagination 15 item per halaman.

### Manajemen Foto

- Upload foto ke folder dengan judul dan deskripsi opsional.
- Halaman **All Photos** dengan tampilan grid, pencarian, filter folder, dan pagination.
- Halaman detail foto dengan preview ukuran penuh dan metadata file.
- Format yang didukung: JPEG, JPG, PNG, GIF, WEBP.
- Ukuran maksimal **5 MB**.
- Nama file disimpan dengan UUID untuk menghindari tabrakan nama.
- Foto baru disimpan secara privat di `storage/app/private/photos`.
- Foto hanya dapat diakses melalui route terautentikasi `photos.file`, bukan URL publik langsung.
- Mengganti foto akan menghapus file lama.
- Menghapus foto akan menghapus metadata dan file terkait.

### Antarmuka

- Responsif untuk desktop dan mobile.
- Mode terang dan gelap dengan preferensi tersimpan di `localStorage`.
- Notifikasi flash message untuk sukses, error, dan info.
- Konfirmasi sebelum menghapus data.
- Tampilan badge warna untuk tipe resource, status baca, dan prioritas catatan.

## Role Pengguna

| Role | Akses |
| --- | --- |
| **Guest** | Hanya dapat membuka halaman login dan registrasi. |
| **Student** | Mengelola workspace sendiri, bergabung ke classroom, melihat materi kelas, dan mengumpulkan tugas. |
| **Teacher** | Semua akses workspace pribadi, membuat classroom, membagikan materi/tugas, melihat submission kelas, dan memberi nilai. |
| **Admin** | Fungsi pengajaran pada classroom yang dibuatnya (materi, tugas, penilaian, co-teaching) **dan** manajemen akun lintas sistem melalui panel `/admin`: ubah role, reset password, suspend/reactivate, aksi massal, export CSV, dan audit log. Tidak otomatis dapat melihat workspace privat pengguna lain. |

TataKelas menganut prinsip **least privilege**: konten personal hanya dapat diakses pemilik, sedangkan folder LMS hanya tersedia untuk pengajar classroom dan murid yang terdaftar. Matriks akses tersedia di [`docs/PRD.md`](docs/PRD.md#7-role-dan-hak-akses).

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Bahasa | PHP 8.2+ |
| Framework | Laravel 12 |
| Templating | Blade |
| Database | MySQL (konfigurasi `.env` saat ini) atau SQLite |
| Styling | Tailwind CSS 4 + DaisyUI 5 via Vite (fallback CDN jika build belum tersedia) |
| Ikon | Font Awesome 6 (CDN) |
| JavaScript | Vanilla JavaScript inline |
| Build tool | Vite 6 |
| Testing | PHPUnit 11 |
| Penyimpanan file | Laravel Filesystem; foto, materi kelas, dan submission disimpan privat |
| Autentikasi | Session-based Laravel Auth |

> Catatan: layout memakai hasil build Vite jika `public/build/manifest.json` tersedia. Jika belum, layout otomatis memakai Tailwind CDN. Font Awesome tetap dimuat dari CDN.

Antarmuka memakai **DaisyUI 5** di atas Tailwind CSS 4 dengan dua tema bawaan yang disesuaikan palet TataKelas:

- Tema `light` dan `dark` diatur di `resources/css/app.css`.
- Tombol tema menyinkronkan class `.dark` pada `<html>` (untuk varian `dark:` Tailwind) sekaligus atribut `data-theme` (untuk komponen DaisyUI).
- Shell aplikasi memakai sidebar tetap di desktop, drawer di mobile, top bar sticky, serta dropdown pengguna.
- Halaman autentikasi memakai layout terpisah di `resources/views/layouts/auth.blade.php`.
- Pagination memakai view kustom DaisyUI di `resources/views/vendor/pagination/tailwind.blade.php`.

## Arsitektur Singkat

TataKelas menggunakan pola MVC Laravel:

- **Route** di `routes/web.php` membagi akses menjadi `guest` dan `auth`; grup `auth` dilindungi middleware `active` dan akses admin dijaga middleware `role:admin`.
- **Controller** menangani alur CRUD dan mengembalikan view atau redirect.
- **Form Request** menangani validasi dan pesan error.
- **Model Eloquent** menyimpan relasi dan aturan kepemilikan data.
- **Global Scope** pada `Folder`, `Resource`, `Note`, dan `Photo` membatasi query ke data milik pengguna yang sedang login.
- **Policy** mengatur akses folder classroom dan submission sesuai role (pemilik, co-teacher, anggota kelas).
- **Blade View** menyusun antarmuka dengan layout utama `layouts.app`.
- **Storage** menyimpan file foto, materi classroom, dan submission secara privat dan menyajikannya melalui controller.

Alur kepemilikan data:

```text
User 1 ──< Folder ──< Resource
                  ├──< Note
                  └──< Photo
```

Semua data anak mengikuti pemilik folder. Jika folder tidak terlihat oleh pengguna, resource, catatan, dan foto di dalamnya juga tidak terlihat.

## Struktur Direktori

```text
TataKelas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── FolderController.php
│   │   │   ├── NoteController.php
│   │   │   ├── PhotoController.php
│   │   │   ├── ResourceController.php
│   │   │   ├── ClassroomController.php
│   │   │   ├── SubmissionController.php
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AdminUserController.php
│   │   │   ├── AdminAuditLogController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserHasRole.php      # alias `role:...`
│   │   │   └── EnsureAccountIsActive.php  # alias `active`, auto-logout akun ditangguhkan
│   │   └── Requests/            # Validasi form
│   └── Models/
│       ├── AdminAuditLog.php
│       ├── Classroom.php
│       ├── Folder.php
│       ├── Note.php
│       ├── Photo.php
│       ├── Resource.php
│       ├── Submission.php
│       ├── SubmissionFile.php
│       └── User.php
├── database/
│   ├── migrations/              # Skema users, folders, resources, notes, photos, classrooms, submissions, admin_audit_logs, dll.
│   ├── seeders/                 # Data contoh (DemoSeeder + seeder per fitur)
│   └── database.sqlite          # Database SQLite opsional
├── docs/
│   └── PRD.md                   # Product Requirements Document
├── resources/
│   ├── css/
│   │   └── app.css              # Tailwind + konfigurasi tema DaisyUI
│   └── views/
│       ├── admin/               # Dashboard, direktori/detail pengguna, audit log
│       ├── auth/                # Login dan registrasi
│       ├── classrooms/          # Daftar, detail classroom, detail materi/tugas
│       ├── folders/             # Dashboard dan CRUD folder
│       ├── layouts/             # Layout utama dan layout autentikasi
│       ├── notes/               # Form catatan
│       ├── partials/            # Flash message
│       ├── photos/              # Form foto
│       ├── resources/           # Daftar, form, dan detail resource
│       ├── submissions/         # Hasil penilaian
│       └── vendor/pagination/   # Pagination kustom DaisyUI
├── routes/
│   └── web.php
├── storage/
│   └── app/private/             # File privat: foto, materi classroom, submission
├── tests/
│   └── Feature/                 # Autentikasi, kepemilikan, classroom, admin, export, preview
├── .env.example
└── README.md
```

## Prasyarat

Pastikan environment sudah memiliki:

- **PHP 8.2** atau lebih baru.
- Ekstensi PHP: `pdo`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, dan driver database yang dipakai (`pdo_mysql` atau `pdo_sqlite`).
- **Composer 2**.
- **Node.js 18+** dan **npm** (opsional, untuk build asset Vite).
- MySQL/MariaDB atau SQLite.
- Web server lokal seperti Laragon, XAMPP, Valet, atau `php artisan serve`.

Cek versi:

```bash
php -v
composer --version
node -v
npm -v
```

## Instalasi

1. **Clone repository**

   ```bash
   git clone <URL_REPOSITORY> TataKelas
   cd TataKelas
   ```

2. **Install dependency PHP**

   ```bash
   composer install
   ```

3. **Buat file environment**

   Windows (Command Prompt):

   ```bash
   copy .env.example .env
   ```

   PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

   Linux/macOS:

   ```bash
   cp .env.example .env
   ```

4. **Generate application key**

   ```bash
   php artisan key:generate
   ```

5. **Sesuaikan identitas aplikasi**

   Ubah di `.env`:

   ```env
   APP_NAME=TataKelas
   APP_URL=http://127.0.0.1:8000
   ```

6. **Konfigurasi database**

   Lihat bagian [Konfigurasi Database](#konfigurasi-database).

7. **Jalankan migrasi**

   ```bash
   php artisan migrate
   ```

8. **Atur kata sandi akun demo**

   Isi `TATAKELAS_DEMO_PASSWORD` pada `.env` dengan kata sandi lokal minimal 12 karakter sebelum menjalankan seeder.

9. **(Opsional) Install dependency frontend**

   ```bash
   npm install
   ```

   Build asset:

   ```bash
   npm run build
   ```

   Atau jalankan mode development:

   ```bash
   npm run dev
   ```

10. **Jalankan server**

   ```bash
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`.

## Konfigurasi Database

### Opsi A: MySQL/MariaDB

Buat database terlebih dahulu:

```sql
CREATE DATABASE folderin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atur `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=folderin
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan:

```bash
php artisan migrate
```

### Opsi B: SQLite

SQLite cocok untuk demo atau pengembangan cepat.

1. Buat file database:

   PowerShell:

   ```powershell
   New-Item database/database.sqlite -ItemType File -Force
   ```

   Linux/macOS:

   ```bash
   touch database/database.sqlite
   ```

2. Atur `.env`:

   ```env
   DB_CONNECTION=sqlite
   # DB_DATABASE boleh dikosongkan; Laravel memakai database/database.sqlite
   ```

3. Jalankan:

   ```bash
   php artisan migrate
   ```

> Jika `php artisan migrate` gagal dengan pesan `could not find driver`, aktifkan ekstensi `pdo_sqlite` atau `pdo_mysql` di `php.ini`, lalu restart terminal/web server.

### Variabel Environment Penting

| Variabel | Contoh | Keterangan |
| --- | --- | --- |
| `APP_NAME` | `TataKelas` | Nama aplikasi |
| `APP_ENV` | `local` | Environment aplikasi |
| `APP_DEBUG` | `true` | Tampilkan detail error; set `false` di produksi |
| `APP_URL` | `http://127.0.0.1:8000` | URL dasar aplikasi |
| `DB_CONNECTION` | `mysql` / `sqlite` | Driver database |
| `DB_DATABASE` | `folderin` | Nama database atau path SQLite |
| `TATAKELAS_DEMO_PASSWORD` | (rahasia lokal, minimal 12 karakter) | Kata sandi bersama akun demo; jangan gunakan di produksi |
| `SESSION_DRIVER` | `database` | Media penyimpanan session |
| `SESSION_EXPIRE_ON_CLOSE` | `true` | Cookie session berakhir saat browser ditutup |
| `FILESYSTEM_DISK` | `local` | Disk default untuk file |
| `QUEUE_CONNECTION` | `database` | Driver queue |

## Menjalankan Aplikasi

Mode paling sederhana:

```bash
php artisan serve
```

Akses:

- Aplikasi: `http://127.0.0.1:8000`
- Login: `http://127.0.0.1:8000/login`
- Registrasi: `http://127.0.0.1:8000/register`

Jika memakai Laragon, letakkan project di `C:\laragon\www\TataKelas` dan akses melalui virtual host atau `http://localhost/TataKelas/public`.

Untuk pengembangan frontend dengan hot reload:

```bash
npm run dev
```

Untuk menjalankan server, queue listener, log viewer, dan Vite sekaligus:

```bash
composer run dev
```

Perintah tersebut menjalankan `php artisan serve`, `queue:listen`, `pail`, dan `npm run dev` secara bersamaan.

### Perintah Artisan Berguna

| Perintah | Fungsi |
| --- | --- |
| `php artisan route:list` | Melihat seluruh route yang terdaftar |
| `php artisan migrate` | Menjalankan migrasi yang belum dijalankan |
| `php artisan migrate:fresh --seed` | Menghapus seluruh tabel, migrasi ulang, dan seeding |
| `php artisan db:seed` | Mengisi akun dan data demo TataKelas |
| `php artisan optimize:clear` | Membersihkan cache config, route, view, dan event |
| `php artisan storage:link` | Membuat symlink storage publik (untuk foto lama) |
| `php artisan test` | Menjalankan test suite |

> `migrate:fresh` akan **menghapus seluruh data**. Gunakan hanya pada lingkungan pengembangan.

### Penyimpanan Foto

Upload foto baru disimpan di:

```text
storage/app/private/photos
```

Foto disajikan melalui route terautentikasi, sehingga **tidak perlu** `storage:link` untuk foto baru.

Namun, jika terdapat foto lama yang tersimpan di `storage/app/public`, jalankan:

```bash
php artisan storage:link
```

## Seeding Data Contoh

Seeder demo dapat dijalankan berulang kali tanpa menggandakan data. Seeder hanya boleh dijalankan pada environment `local` atau `testing` dan tidak menghapus data yang sudah ada.

Atur kata sandi demo lokal di `.env` (minimal 12 karakter):

```bash
TATAKELAS_DEMO_PASSWORD=isi-dengan-kata-sandi-lokal-yang-kuat
```

Kemudian, setelah migrasi selesai:

```bash
php artisan db:seed
```

Seeder membuat akun demo berikut; semuanya memakai kata sandi lokal yang ditentukan di `TATAKELAS_DEMO_PASSWORD`:

| Role | Email |
| --- | --- |
| Admin | `admin@tatakelas.test` |
| Guru | `guru@tatakelas.test` |
| Murid | `murid1@tatakelas.test` |
| Murid | `murid2@tatakelas.test` |

Data contoh meliputi workspace privat per akun, resource, catatan, foto privat, satu classroom, materi, tugas dengan tenggat, serta submission berstatus menunggu dan sudah dinilai. Folder privat tetap dimiliki masing-masing pengguna dan tidak dibagikan ke guru/admin. Kode gabung classroom demo adalah `TATAKELAS`.

Seeder demo juga dipecah per fitur. `php artisan db:seed` menjalankan semuanya melalui `DemoSeeder` sesuai urutan dependensi:

| Seeder | Data yang dibuat |
| --- | --- |
| `DemoUserSeeder` | Akun admin, guru, dan dua murid demo |
| `DemoClassroomSeeder` | Classroom demo beserta anggota murid |
| `DemoPersonalFolderSeeder` | Folder privat, resource, catatan, dan foto |
| `DemoLearningMaterialSeeder` | Folder materi classroom dan resource-nya |
| `DemoAssignmentSeeder` | Folder tugas, tenggat, dan instruksi tugas |
| `DemoSubmissionSeeder` | Submission dan nilai dari murid |

Untuk menjalankan satu fitur saja, jalankan seeder prasyaratnya lebih dulu:

```bash
php artisan db:seed --class=DemoUserSeeder
php artisan db:seed --class=DemoClassroomSeeder
php artisan db:seed --class=DemoAssignmentSeeder
php artisan db:seed --class=DemoSubmissionSeeder
```

Seeder tidak mengosongkan database. Hindari `migrate:fresh` pada database yang berisi data penting; untuk mencoba seeder, cukup jalankan `php artisan db:seed`.

## Daftar Route

Semua route aplikasi berada di `routes/web.php`.

### Route publik (guest)

| Method | URI | Nama | Fungsi |
| --- | --- | --- | --- |
| GET | `/login` | `login` | Form login |
| POST | `/login` | - | Proses login (throttle 6/menit) |
| GET | `/register` | `register` | Form registrasi |
| POST | `/register` | - | Proses registrasi (throttle 6/menit) |

### Route terautentikasi

Grup route di bawah dilindungi middleware `auth` dan `active` (akun ditangguhkan langsung diarahkan ke halaman login).

#### Akun

| Method | URI | Nama | Fungsi |
| --- | --- | --- | --- |
| POST | `/logout` | `logout` | Logout |
| GET/PUT | `/account/password` | `account.password.edit`, `account.password.update` | Ganti kata sandi dengan verifikasi |
| GET | `/` | - | Redirect admin ke dashboard admin, role lain ke dashboard folder |

#### Admin — panel administrasi (`role:admin`, prefix `/admin`)

| Method | URI | Nama | Fungsi |
| --- | --- | --- | --- |
| GET | `/admin` | `admin.index` | Dashboard admin (ringkasan akun, classroom, aktivitas admin) |
| GET | `/admin/users` | `admin.users.index` | Direktori pengguna dengan pencarian, filter, sorting, pagination |
| GET | `/admin/users/export` | `admin.users.export` | Export CSV daftar pengguna mengikuti filter |
| PATCH | `/admin/users/bulk` | `admin.users.bulk` | Aksi massal suspend/reactivate/ubah role |
| GET | `/admin/users/{user}` | `admin.users.show` | Detail pengguna dan riwayat auditnya |
| PATCH | `/admin/users/{user}/role` | `admin.users.role` | Ubah role pengguna |
| POST | `/admin/users/{user}/reset-password` | `admin.users.reset-password` | Buat password sementara |
| PATCH | `/admin/users/{user}/suspend` | `admin.users.suspend` | Tangguhkan akun (blokir login + auto-logout) |
| PATCH | `/admin/users/{user}/reactivate` | `admin.users.reactivate` | Aktifkan kembali akun |
| DELETE | `/admin/users/{user}` | `admin.users.destroy` | Hapus akun (dengan guard data terkait) |
| GET | `/admin/audit-logs` | `admin.audit-logs.index` | Audit log tindakan admin dengan filter tanggal/aksi/search |
| GET | `/admin/audit-logs/export` | `admin.audit-logs.export` | Export CSV audit log mengikuti filter |

#### Classroom dan LMS

| Method | URI | Nama | Fungsi |
| --- | --- | --- | --- |
| GET/POST | `/classrooms/join` | `classrooms.join` | Student bergabung dengan kode; teacher/admin bergabung sebagai co-teacher |
| GET | `/classrooms` | `classrooms.index` | Daftar classroom |
| GET | `/classrooms/{classroom}` | `classrooms.show` | Materi, tugas, co-teacher, dan peserta classroom |
| GET | `/classrooms/{classroom}/folders/{folder}` | `classrooms.folders.show` | Detail materi/tugas classroom |
| POST | `/classrooms` | `classrooms.store` | Teacher/Admin membuat classroom |
| POST | `/classrooms/{classroom}/folders` | `classrooms.folders.store` | Buat folder materi/tugas |
| DELETE | `/classrooms/{classroom}/co-teachers/{coTeacher}` | `classrooms.co-teachers.destroy` | Pemilik mengeluarkan co-teacher |
| DELETE | `/classrooms/{classroom}/co-teacher` | `classrooms.co-teacher.leave` | Co-teacher keluar sendiri |
| POST | `/classrooms/{classroom}/folders/{folder}/resources` | `classrooms.folders.resources.store` | Tambah tautan/berkas materi |
| POST | `/folders/{folder}/submissions` | `folders.submissions.store` | Student mengumpulkan tugas (teks/URL/berkas) |
| DELETE | `/folders/{folder}/submissions` | `folders.submissions.destroy` | Student menarik submission |
| PATCH | `/submissions/{submission}/grade` | `submissions.grade` | Teacher/Admin memberi nilai dan feedback |
| GET | `/resources/{resource}/file` | `resources.file` | Unduh resource setelah otorisasi |
| GET | `/resources/{resource}/preview` | `resources.preview` | Pratinjau PDF materi inline di browser |
| GET | `/submission-files/{file}` | `submissions.files.download` | Unduh berkas submission setelah otorisasi |

#### Workspace pribadi (`role:teacher,student`)

| Method | URI | Nama | Fungsi |
| --- | --- | --- | --- |
| GET/POST | `/folders` | `folders.index`, `folders.store` | Daftar dan tambah folder |
| GET | `/folders/create` | `folders.create` | Form folder |
| GET | `/folders/{folder}` | `folders.show` | Detail folder |
| GET/PUT/DELETE | `/folders/{folder}/edit`, `/folders/{folder}` | `folders.edit`, `folders.update`, `folders.destroy` | Ubah/hapus folder |
| GET/POST | `/resources` | `resources.index`, `resources.store` | Daftar dan tambah resource |
| GET | `/resources/create` | `resources.create` | Form resource |
| GET | `/resources/{resource}` | `resources.show` | Detail resource |
| GET/PUT/DELETE | `/resources/{resource}/edit`, `/resources/{resource}` | `resources.edit`, `resources.update`, `resources.destroy` | Ubah/hapus resource |
| PATCH | `/resources/{resource}/status` | `resources.update-status` | Ubah status baca |
| PATCH | `/resources/{resource}/move` | `resources.move` | Pindah folder |
| GET/POST | `/notes` | `notes.index`, `notes.store` | Daftar dan tambah catatan |
| GET | `/notes/create` | `notes.create` | Form catatan |
| GET | `/notes/{note}` | `notes.show` | Detail catatan |
| GET/PUT/DELETE | `/notes/{note}/edit`, `/notes/{note}` | `notes.edit`, `notes.update`, `notes.destroy` | Ubah/hapus catatan |
| GET | `/photos/{photo}/file` | `photos.file` | Menyajikan file foto privat |
| GET/POST | `/photos` | `photos.index`, `photos.store` | Daftar dan upload foto |
| GET | `/photos/create` | `photos.create` | Form upload foto |
| GET | `/photos/{photo}` | `photos.show` | Detail foto |
| GET/PUT/DELETE | `/photos/{photo}/edit`, `/photos/{photo}` | `photos.edit`, `photos.update`, `photos.destroy` | Ubah/hapus foto |



## Model Data

### `users`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `name` | string | Nama pengguna |
| `email` | string | Unik |
| `password` | string | Hash password |
| `role` | string | `admin`, `teacher`, `student`; registrasi publik selalu `student` |
| `status` | string | `active` (default) atau `suspended`; berindeks |
| `suspended_at` | timestamp nullable | Waktu akun ditangguhkan |
| `suspension_reason` | text nullable | Alasan penangguhan, diisi admin |
| `last_login_at` | timestamp nullable | Waktu login terakhir, diisi saat login berhasil; berindeks |
| `remember_token` | string | Token remember me; dirotasi saat suspend/reset password |
| `created_at`, `updated_at` | timestamp | Waktu dibuat/diubah |

### `folders`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `user_id` | bigint nullable | Pemilik folder; nullable untuk data lama |
| `classroom_id` | bigint nullable | Classroom pemilik folder bersama |
| `visibility` | enum | `private`, `class_material`, `assignment` |
| `due_date` | datetime nullable | Tenggat folder tugas |
| `name` | string | Nama folder |
| `description` | text nullable | Deskripsi |
| `color_code` | string(7) | Kode hex, default `#3B82F6` |
| `created_at`, `updated_at` | timestamp | Waktu dibuat/diubah |

### `resources`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `folder_id` | bigint | Foreign key ke folder, cascade delete |
| `title` | string | Judul resource |
| `url` | text nullable | URL resource |
| `file_path` | string nullable | Berkas privat |
| `resource_type` | enum | `url`, `file`, `video`, `documentation`, `assignment_instruction` |
| `notes` | text nullable | Catatan pribadi |
| `read_status` | enum | `to_read`, `reading`, `completed` |
| `created_at`, `updated_at` | timestamp | Waktu dibuat/diubah |

### `classrooms`, `classroom_user`, dan `classroom_co_teacher`

Classroom dimiliki oleh `teacher_id`. Student terdaftar melalui tabel pivot `classroom_user`; pengajar pengikut (co-teacher) melalui tabel pivot `classroom_co_teacher`. Kode classroom bersifat unik untuk proses bergabung (murid bergabung, guru lain bergabung sebagai co-teacher). Hanya pemilik classroom yang dapat mengeluarkan co-teacher; co-teacher dapat keluar sendiri.

### `submissions` dan `submission_files`

Submission terkait folder tugas dan student. Satu student memiliki satu submission per tugas yang dapat diperbarui atau ditarik; status mencakup `submitted`, `late`, dan `graded`.

| Kolom | Keterangan |
| --- | --- |
| `submission_text`, `submitted_url` | Isi submission (minimal salah satu; berkas juga diperbolehkan) |
| `grade` | Nilai integer 0–100 |
| `teacher_feedback` | Feedback opsional |
| `status` | `submitted`, `late`, atau `graded` |

Berkas submission disimpan terpisah di tabel `submission_files` sehingga satu submission dapat memuat beberapa berkas:

| Kolom | Keterangan |
| --- | --- |
| `submission_id` | Foreign key ke submission, cascade delete |
| `file_path` | Path berkas di disk privat |
| `original_name` | Nama asli berkas saat diunggah |
| `size` | Ukuran berkas dalam byte (nullable) |

### `notes`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `folder_id` | bigint | Foreign key ke folder, cascade delete |
| `title` | string | Judul catatan |
| `content` | text | Isi catatan |
| `priority` | enum | `low`, `medium`, `high` |
| `created_at`, `updated_at` | timestamp | Waktu dibuat/diubah |

### `photos`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `folder_id` | bigint | Foreign key ke folder, cascade delete |
| `title` | string | Judul foto |
| `filename` | string | Nama file asli |
| `filepath` | string | Path file di storage |
| `mime_type` | string nullable | MIME type |
| `file_size` | bigint nullable | Ukuran file dalam byte |
| `description` | text nullable | Deskripsi |
| `created_at`, `updated_at` | timestamps | Waktu dibuat/diubah |

### `admin_audit_logs`

Catatan tindakan administratif admin. Snapshot nama/email aktor dan target disimpan agar riwayat tetap terbaca meski akun dihapus (foreign key `nullOnDelete`).

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `actor_user_id` | bigint nullable | Admin yang bertindak; `null` bila akun dihapus |
| `actor_name`, `actor_email` | string | Snapshot identitas aktor |
| `target_user_id` | bigint nullable | Akun yang menjadi objek tindakan |
| `target_name`, `target_email` | string nullable | Snapshot identitas target |
| `action` | string | `role_changed`, `password_reset`, `account_deleted`, `account_suspended`, `account_reactivated` |
| `details` | json nullable | Detail tindakan; tidak pernah memuat password |
| `created_at`, `updated_at` | timestamps | Waktu; berindeks komposit `(action, created_at)` dan `(target_user_id, created_at)` |

## Aturan Validasi

### Folder

- `name`: wajib, string, maksimal 255 karakter.
- `description`: opsional, string, maksimal 1.000 karakter.
- `color_code`: opsional, harus format hex 6 digit seperti `#3B82F6`.

### Resource

- `folder_id`: wajib dan harus folder privat milik pengguna yang login.
- `title`: wajib, maksimal 255 karakter.
- `url`: URL valid untuk resource tautan; dapat kosong untuk berkas.
- `resource_type`: `url`, `file`, `video`, `documentation`, atau `assignment_instruction`.
- `file`: opsional atau wajib sesuai jenis resource; PDF/Office maksimal 20 MB.
- `notes`: opsional, maksimal 5.000 karakter.
- `read_status`: wajib, salah satu dari `to_read`, `reading`, `completed`.

### Catatan

- `folder_id`: wajib dan harus folder milik pengguna yang login.
- `title`: wajib, maksimal 255 karakter.
- `content`: wajib, maksimal 50.000 karakter.
- `priority`: wajib, salah satu dari `low`, `medium`, `high`.

### Foto

- `folder_id`: wajib dan harus folder milik pengguna yang login.
- `title`: wajib, maksimal 255 karakter.
- `photo`: wajib saat upload, opsional saat edit; harus gambar; format `jpeg`, `jpg`, `png`, `gif`, `webp`; maksimal 5.120 KB (5 MB).
- `description`: opsional, maksimal 1.000 karakter.

## Keamanan dan Isolasi Data

TataKelas menerapkan beberapa lapisan perlindungan:

1. **Middleware `auth`** melindungi seluruh route aplikasi. Pengguna yang belum login diarahkan ke halaman login.
2. **Middleware `guest`** mencegah pengguna yang sudah login membuka halaman login/registrasi.
3. **Middleware `active`** pada seluruh route terautentikasi mengakhiri sesi akun yang ditangguhkan: user di-logout, session di-invalidate, dan CSRF token diregenerasi.
4. **Middleware `role:admin`** dan `role:teacher,admin`, `role:student`, dll. memisahkan akses per role; panel admin hanya untuk admin.
5. **Global scope dan policies** membatasi folder privat pada pemiliknya dan classroom pada teacher pemilik serta student terdaftar. Admin tidak mendapat akses tambahan ke folder privat murid.
6. **Validasi foreign key** membatasi operasi personal ke folder privat milik pengguna; resource LMS hanya dapat dikelola teacher/admin classroom.
7. **File privat** (foto, materi, submission) disimpan di disk local dan dilayani lewat route yang memeriksa policy, bukan melalui URL public.
8. **CSRF token** pada semua form POST/PUT/PATCH/DELETE.
9. **Password hashing** otomatis melalui cast `hashed` pada model `User`.
10. **Rate limiting** login dan registrasi untuk mengurangi brute force.
11. **Session regeneration** saat login dan registrasi; invalidasi session saat logout.
12. **Transaksi database** saat registrasi, pengklaiman folder lama, dan seluruh aksi administrasi admin (dengan `lockForUpdate`).
13. **Anti-akun-kunci diri**: admin tidak dapat menangguhkan/menghapus/mengubah role akunnya sendiri, admin aktif terakhir tidak dapat ditangguhkan/dihapus/diturunkan rolenya, dan mengubah role menjadi `student` ditolak selama akun masih mengelola atau mengajar classroom.
14. **Audit log admin**: setiap tindakan administrasi dicatat dengan snapshot identitas; password sementara tidak pernah dicatat di log.

### Klaim Folder Lama

Migrasi `add_user_id_to_folders_table` menambahkan `user_id` nullable. Folder yang dibuat sebelum fitur multi-user ada tidak memiliki pemilik. Saat pengguna **pertama** mendaftar:

- Semua folder dengan `user_id = null` diklaim oleh pengguna tersebut.
- Proses berjalan dalam transaksi dan memakai `lockForUpdate()`.
- Pengguna kedua dan seterusnya tidak mengklaim folder lama.

### Catatan Foto Lama

Foto yang diunggah sebelum perubahan penyimpanan privat dapat berada di `storage/app/public` dan masih dapat diakses melalui URL publik lama. Pindahkan file tersebut ke penyimpanan privat sebelum aplikasi dipakai banyak pengguna.

## Pengujian

Jalankan test suite:

```bash
php artisan test
```

Atau:

```bash
vendor/bin/phpunit
```

Test yang tersedia (70 test / 380 assertion):

- `tests/Feature/ExampleTest.php` — halaman welcome, redirect dashboard, halaman auth.
- `tests/Feature/OwnershipTest.php` — klaim folder lama, isolasi folder/resource antar pengguna.
- `tests/Feature/PasswordUpdateTest.php` — ganti kata sandi dengan verifikasi kata sandi saat ini.
- `tests/Feature/LoginSessionTest.php` — cookie sesi vs remember me, default landing admin.
- `tests/Feature/ClassroomVisibilityTest.php` — visibilitas folder privat/kelas, submission dan grading, visibilitas resource.
- `tests/Feature/CoTeacherTest.php` — co-teacher join/keluar/dikeluarkan dan batas otorisasinya.
- `tests/Feature/SubmissionTest.php` — multi-file submission, replace attachment, tarik submission.
- `tests/Feature/ResourcePreviewTest.php` — preview PDF inline, penolakan untuk non-PDF, otorisasi preview.
- `tests/Feature/ResourceManagementTest.php` — edit resource berkas tanpa re-upload.
- `tests/Feature/TataKelasDemoSeederTest.php` — idempotensi dan batasan seeder demo.
- `tests/Feature/AdminAccountManagementTest.php` — direktori admin, ubah role, guard self/last-admin/co-teacher, reset password, hapus akun, audit log.
- `tests/Feature/AdminUserStatusTest.php` — suspend/reactivate, login ditolak, auto-logout sesi, guard akun sendiri, bulk action.
- `tests/Feature/AdminUserDetailTest.php` — halaman detail pengguna, classroom dan jumlah data, riwayat audit, pencatatan login terakhir.
- `tests/Feature/AdminExportsTest.php` — CSV export pengguna dan audit log mengikuti filter.
- `tests/Feature/AdminDashboardTest.php` — dashboard admin dan pembatasan aksesnya.
- `tests/Feature/AdminAuditLogFilterTest.php` — filter audit log tanggal/aksi dan label detail.

Konfigurasi `phpunit.xml` default memakai SQLite in-memory. Karena environment pengembangan tidak memuat `pdo_sqlite`, suite diverifikasi memakai database MySQL test terpisah:

```powershell
$env:DB_CONNECTION='mysql'; $env:DB_DATABASE='folderin_test'; $env:DB_USERNAME='root'; $env:DB_PASSWORD='';
php artisan test
```

Verifikasi terakhir: **70 test / 380 assertion** lulus memakai MySQL test database. Untuk CI, gunakan jalur ini atau aktifkan `pdo_sqlite`.

## Troubleshooting

### `could not find driver`

Driver database untuk PHP belum aktif.

- MySQL: aktifkan `pdo_mysql`.
- SQLite: aktifkan `pdo_sqlite`.

Cek dengan:

```bash
php -m
```

### `SQLSTATE[HY000] [1049] Unknown database`

Database belum dibuat. Buat database `folderin` terlebih dahulu, atau sesuaikan `DB_DATABASE` di `.env`.

### `419 Page Expired`

Penyebab umum:

- CSRF token tidak terkirim.
- Session driver bermasalah.
- Cookie/session kedaluwarsa.

Pastikan form memiliki `@csrf`, lalu coba:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Foto tidak muncul

1. Pastikan route `photos.file` dapat diakses setelah login.
2. Periksa file di `storage/app/private/photos`.
3. Jika foto lama berada di storage publik, jalankan `php artisan storage:link`.
4. Pastikan permission folder `storage` dan `bootstrap/cache` dapat ditulis oleh web server.

### Halaman detail atau daftar catatan/foto tidak tampil

1. Pastikan `public/build/manifest.json` ada dengan menjalankan `npm run build`.
2. Jika memakai mode pengembangan, jalankan `npm run dev`.
3. Jalankan `php artisan optimize:clear` setelah mengubah konfigurasi atau view.

### Asset Tailwind atau DaisyUI tidak tampil

Layout memakai hasil build Vite bila `public/build/manifest.json` tersedia. Jalankan:

```bash
npm install
npm run build
```

Pastikan `daisyui` terpasang sebagai dev dependency. Jika build belum tersedia, layout otomatis memakai Tailwind CDN sehingga aplikasi masih dapat tampil selama ada koneksi internet, tetapi komponen DaisyUI memerlukan hasil build Vite. Font Awesome selalu dimuat dari CDN.

## Optimasi Performa

- **Hindari N+1 query**: relasi folder dimuat dengan eager loading pada daftar resource, catatan, dan foto.
- **Hitung data dengan SQL**: jumlah resource, catatan, dan foto pada halaman folder dihitung memakai `withCount` (scalar subquery), bukan dengan memuat seluruh baris lalu menghitungnya di PHP.
- **Pagination per tab**: tab Resources, Notes, dan Photos memuat 15/12/12 baris per halaman, sehingga folder besar tidak memuat seluruh data sekaligus.
- **Indeks database**: ditambahkan indeks komposit untuk `folders(user_id, created_at)`, `resources(folder_id, created_at)`, `notes(folder_id, created_at)`, dan `photos(folder_id, created_at)`, serta indeks filter `resource_type`, `read_status`, dan `priority`.
- **Query lebih ringan**: dropdown pindah resource hanya mengambil kolom `id` dan `name`.
- **Asset produksi**: Tailwind CSS dan DaisyUI dikompilasi serta diminifikasi oleh Vite (sekitar 28 KB gzip), dengan fallback CDN hanya jika build belum tersedia.
- **Gambar**: foto pada daftar dan tab folder memakai `loading="lazy"` agar gambar di luar viewport tidak langsung diunduh.
- **Filter tetap aktif saat berpindah halaman**: paginator memakai `withQueryString()`.

## Status Implementasi dan Keterbatasan

### Sudah berjalan

| Modul | Status |
| --- | --- |
| Registrasi, login, logout, remember me | Selesai |
| Rate limiting autentikasi | Selesai |
| Dashboard folder | Selesai |
| CRUD folder | Selesai |
| CRUD resource | Selesai, termasuk halaman detail |
| Conditional upload field resource/materi | Selesai |
| Pratinjau PDF materi inline | Selesai |
| Ubah status baca resource | Selesai |
| Pindah resource antar folder | Selesai |
| Pencarian dan filter resource | Selesai |
| CRUD catatan | Selesai, termasuk halaman daftar dan detail |
| CRUD foto | Selesai, termasuk halaman daftar dan detail |
| Penyajian foto privat | Selesai |
| Classroom: materi, tugas, submission, penilaian | Selesai |
| Co-teacher bergabung/keluar/dikeluarkan | Selesai |
| Submission multi-berkas | Selesai |
| Ganti kata sandi | Selesai, dengan verifikasi kata sandi saat ini |
| Pembersihan file foto saat folder dihapus | Selesai |
| Isolasi data per pengguna | Selesai |
| Dashboard admin dan detail pengguna | Selesai |
| Suspend/reactivate + auto-logout sesi | Selesai |
| Aksi massal dengan laporan terproses/dilewati | Selesai |
| Export CSV pengguna dan audit log | Selesai |
| Audit log tindakan admin | Selesai |
| Dark mode dan responsivitas | Selesai |
| Optimasi query, pagination, dan indeks database | Selesai |
| Build asset Tailwind + DaisyUI via Vite | Selesai |
| Test suite (70 test / 380 assertion) | Tersedia |

### Keterbatasan

1. **Autentikasi lanjutan belum tersedia**
   - Verifikasi email.
   - Lupa/reset password mandiri.
   - Ubah profil.
   - Hapus akun mandiri.
   - Two-factor authentication.

2. **Ketergantungan CDN untuk ikon**
   - Tailwind CSS dan DaisyUI sudah dikompilasi melalui Vite.
   - Font Awesome masih dimuat dari CDN sehingga ikon membutuhkan koneksi internet.

3. **Pencarian sederhana**
   - Pencarian memakai `LIKE`, belum memakai full-text search.
   - Performa dapat menurun pada data yang sangat besar.

4. **Belum ada API**
   - Aplikasi hanya menyediakan antarmuka web berbasis Blade.

5. **Audit log terbatas pada tindakan admin**
   - Aktivitas umum pengguna (misalnya login, pengubahan data) belum dicatat; audit saat ini hanya mencakup tindakan administrasi akun.

6. **Penangguhan akun belum ada notifikasi**
   - Akun ditangguhkan tanpa notifikasi email; pengguna baru mengetahui statusnya saat mencoba login.

7. **Test environment perlu ekstensi atau MySQL**
   - `phpunit.xml` default memakai SQLite in-memory, sedangkan environment pengembangan belum memuat `pdo_sqlite`.
   - Suite diverifikasi memakai database MySQL test terpisah; CI perlu salah satu dari kedua jalur tersebut.

8. **Tidak ada backup dan monitoring otomatis**
   - Backup database/storage dan pembersihan file orphan lama masih manual.

## Roadmap

### Fase 1 — Stabilisasi LMS

- Menambahkan smoke test untuk seluruh route yang memakai view.
- Menambahkan pembersihan file orphan secara terjadwal untuk data lama.
- Menata test agar dapat memakai MySQL test database tanpa mengganggu data pengembangan.
- Integrasi CI untuk menjalankan suite (MySQL test database atau `pdo_sqlite`).

### Fase 2 — Peningkatan Produk

- Tag dan filter lintas folder.
- Favorit/pin resource.
- Import/export bookmark (HTML, JSON, CSV).
- Pencarian global.
- Statistik keterbacaan yang lebih lengkap.
- Reset password dan verifikasi email.
- Halaman profil dan hapus akun.
- Notifikasi status akun (misalnya penangguhan) melalui email.
- PWA/mobile-friendly enhancement.

### Fase 3 — Skala dan Kolaborasi

- Berbagi folder dengan tautan atau kolaborator.
- Komentar dan activity log pengguna umum.
- Penyimpanan object storage (S3-compatible).
- Full-text search.
- Audit log aktivitas umum dan backup otomatis.
- Two-factor authentication dan manajemen sesi aktif per pengguna.
- API publik.

## Lisensi

Kode aplikasi ini dikembangkan untuk kebutuhan proyek USK. Framework Laravel sendiri dirilis dengan lisensi MIT. Belum ada file `LICENSE` khusus untuk aplikasi ini; tentukan lisensi proyek sebelum dipublikasikan secara terbuka.
