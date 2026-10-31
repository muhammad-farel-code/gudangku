# GudangKu - Sistem Manajemen Inventaris Gudang

Aplikasi web *full-stack* berbasis *server-side rendering* untuk mengelola inventaris gudang secara komprehensif: master produk barang, kategori, pemasok, transaksi stok masuk/keluar, *stock opname* (penyesuaian stok), laporan mutasi, jejak audit aktivitas (*activity log*), dan dasbor analitik dengan kontrol akses berbasis peran (*Role-Based Access Control* - RBAC).

Dibuat untuk memenuhi tugas **UAS Web Application Development** - Universitas Cakrawala.  
**Nama**: Muhammad Farel | **NIM**: [TODO: Isi NIM Anda]

- **Live Application**: [TODO: https://gudangku-production.up.railway.app](https://railway.app)
- **Repository GitHub**: [https://github.com/muhammad-farel-code/gudangku](https://github.com/muhammad-farel-code/gudangku)
- **Video Demo & Presentasi**: [TODO: Tautan Video YouTube / Google Drive]

---

## 1. Fitur Utama

- **Autentikasi & Sesi Aman**: Registrasi akun baru (otomatis peran *Staff*), login manual dengan pembatasan laju (*rate limiting* 5x/menit), regenerasi ID sesi, serta penyimpanan sesi berbasis database MySQL.
- **Role-Based Access Control (RBAC 3 Peran)**: Pemisahan hak akses ketat antara **Admin**, **Manager**, dan **Staff** pada tingkat *middleware group* dan *Laravel Policy*.
- **Master Data (CRUD)**:
  - **Kategori**: Pengelompokan produk dengan proteksi pencegahan hapus jika masih memiliki produk terkait (*Constraint BR5*).
  - **Pemasok (Supplier)**: Manajemen kontak vendor rekanan dengan penghapusan aman berbasis *Set Null* (*Constraint BR7*).
  - **Produk**: Manajemen barang dengan SKU unik (*BR4*), validasi harga jual $\ge$ harga beli (*BR10*), penetapan stok minimum (*BR11*), dan isolasi stok fisik agar tidak dapat dimanipulasi via form edit biasa (*BR2*).
- **Logika Transaksi Stok (*Immutable Ledger*)**:
  - **Stok Masuk (`IN`)**: Penambahan stok fisik disertai nomor referensi unik harian (`IN-YYYYMMDD-XXXX`) dan pencatatan pemasok asal.
  - **Stok Keluar (`OUT`)**: Pengeluaran barang dengan validasi ketersediaan stok fisik secara atomik (*Pessimistic Locking* `lockForUpdate()`), melempar `InsufficientStockException` bila kuantitas melebihi stok (*Constraint BR1*).
  - **Penyesuaian Stok (`ADJUSTMENT`)**: Pencatatan *stock opname* selisih fisik vs sistem oleh Manager/Admin.
- **Dasbor Analitik Interaktif**:
  - Ringkasan KPI: Total produk aktif, total unit barang, produk stok menipis, dan nilai kapital persediaan (disembunyikan dari peran *Staff*).
  - Grafik tren transaksi 14 hari terakhir (Chart.js) dengan penanganan hari kosong (*zero-filling*).
  - Rekomendasi *restock* berbasis aturan kuantitas minimum ($2 \times \text{min\_stock} - \text{stock}$).
- **Laporan & Ekspor Data**:
  - Laporan stok terkini dan riwayat transaksi dengan filter rentang tanggal, kategori, dan jenis transaksi.
  - Ekspor berkas CSV dengan *HTTP stream download* serta netralisasi *CSV/Formula Injection*.
  - Mode tampilan cetak (*print-optimized view*).
- **Audit Trail (Activity Log)**: Pencatatan jejak seluruh aktivitas CUD, mutasi stok, login, dan perubahan hak akses pengguna bagi Admin.
- **Hardening Keamanan Lengkap**: Token CSRF di seluruh form mutasi, *XSS Output Escaping* universal Blade `{{ }}`, sanitasi input `strip_tags()`, query terparameter PDO (anti-SQLi), dan injeksi *Security Headers* (HSTS, X-Frame-Options, CSP, nosniff).

---

## 2. Tech Stack

- **Framework**: Laravel 12 (PHP 8.2+)
- **Database Engine**: MySQL 8+ / MariaDB 10.4+ (Collation: `utf8mb4_unicode_ci`)
- **Template Engine**: Laravel Blade (Server-Side Rendering)
- **UI Framework & Styling**: Bootstrap 5.3 + Bootstrap Icons + Custom Design Tokens (`public/css/gudangku.css`, Font: Outfit via Google Fonts)
- **Visualisasi Grafik**: Chart.js v4.4 via CDN
- **Testing Suite**: PHPUnit / Laravel Test Runner (MySQL `gudangku_test` via `RefreshDatabase`)

---

## 3. Prasyarat Sistem

Sebelum menjalankan aplikasi di lingkungan lokal, pastikan perangkat telah terpasang:
- **PHP** versi 8.2 atau lebih baru dengan ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.
- **Composer** 2.x
- **MySQL Server** 8.0+ atau **MariaDB** 10.4+ (misalnya melalui XAMPP, Laragon, atau Docker)
- **Git**

---

## 4. Panduan Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal:

### 1. Kloning Repository
```bash
git clone https://github.com/muhammad-farel-code/gudangku.git
cd gudangku
```

### 2. Pasang Dependensi Composer
```bash
composer install
```

### 3. Salin Berkas Konfigurasi Environment & Generate Key
```bash
# Untuk Windows (Command Prompt / PowerShell):
copy .env.example .env

# Untuk Linux / macOS:
cp .env.example .env

# Generate Application Key:
php artisan key:generate
```

### 4. Buat Basis Data MySQL
Buka MySQL client (CLI / phpMyAdmin / DBeaver) lalu buat database aplikasi dan database pengujian:
```sql
CREATE DATABASE gudangku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE gudangku_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Sesuaikan Kredensial Database di `.env`
Buka berkas `.env` dan pastikan konfigurasi koneksi sesuai dengan kredensial MySQL lokal Anda:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gudangku
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Siapkan Skema & Data Database (Pilih Salah Satu Cara)

#### Opsi A: Migrasi & Seeding Otomatis (Disarankan)
Menjalankan migrasi terstruktur beserta seeder data demo realistis (~40 produk, 8 kategori, 6 supplier, 3 akun inti, dan ~120 riwayat transaksi historis):
```bash
php artisan migrate --seed
```

#### Opsi B: Impor Berkas SQL Dump
Jika Anda ingin langsung mengimpor data siap pakai dari dump database:
```bash
# Melalui Command Line (MySQL CLI):
mysql -u root -p gudangku < database/gudangku.sql
```

### 7. Jalankan Server Pengembangan
```bash
php artisan serve
```
Akses aplikasi melalui peramban web pada alamat: **`http://127.0.0.1:8000`**

---

## 5. Akun Demo Bawaan

Aplikasi dilengkapi dengan 3 akun *seed* dengan hak akses berbeda untuk pengujian:

| Peran (*Role*) | Alamat Email | Kata Sandi (*Password*) | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@gudangku.test` | `Admin@12345` | Akses penuh ke seluruh sistem, manajemen pengguna, dan *activity log*. |
| **Manager** | `manager@gudangku.test` | `Manager@12345` | CRUD Master Data, penyesuaian stok (*opname*), laporan & ekspor CSV, metrik finansial dasbor. |
| **Staff** | `staff@gudangku.test` | `Staff@12345` | Input transaksi stok masuk & stok keluar, melihat katalog produk (harga beli disembunyikan). |

> *Catatan*: Setiap akun baru yang mendaftar melalui formulir registrasi publik otomatis berstatus aktif dengan peran **Staff** (Aturan *BR8*).

---

## 6. Hak Akses (Matriks Otorisasi RBAC)

| Modul / Tindakan | Tamu (*Guest*) | Staff | Manager | Admin |
| :--- | :---: | :---: | :---: | :---: |
| Halaman Login & Registrasi | Ya | - | - | - |
| Dashboard Ringkasan & Grafik | - | Ya (*) | Ya | Ya |
| Lihat Daftar Produk / Kategori / Pemasok | - | Ya | Ya | Ya |
| Tambah / Ubah / Hapus Produk | - | - | Ya | Ya |
| Tambah / Ubah / Hapus Kategori | - | - | Ya | Ya |
| Tambah / Ubah / Hapus Pemasok | - | - | Ya | Ya |
| Transaksi Stok Masuk (`IN`) | - | Ya | Ya | Ya |
| Transaksi Stok Keluar (`OUT`) | - | Ya | Ya | Ya |
| Transaksi Penyesuaian Stok (`ADJUSTMENT`) | - | - | Ya | Ya |
| Riwayat Transaksi Stok (*Immutable*) | - | Ya | Ya | Ya |
| Laporan Stok & Transaksi + Ekspor CSV | - | - | Ya | Ya |
| Manajemen Pengguna & Penetapan Peran | - | - | - | Ya |
| Audit Trail (*Activity Log*) | - | - | - | Ya |
| Ubah Profil & Ganti Kata Sandi Sendiri | - | Ya | Ya | Ya |

> (\*) Akun **Staff** tidak dapat melihat kartu indikator "Nilai Persediaan" (informasi finansial/HPP disembunyikan).

---

## 7. Skema Basis Data & Arsitektur

Dokumentasi lengkap mengenai rancangan struktur basis data, tipe data, pengindeksan, serta arsitektur siklus request dapat dibaca pada direktori `docs/`:
- **[docs/ERD.md](docs/ERD.md)**: Diagram relasi entitas Mermaid, kardinalitas relasi, aturan *foreign key* (`RESTRICT` vs `SET NULL`), dan kamus data 6 tabel inti.
- **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)**: Diagram alur siklus hidup request (*request lifecycle*), diagram urutan mutasi stok atomik, struktur direktori lengkap, serta tabel pemetaan rute aplikasi.

---

## 8. Menjalankan Pengujian Otomatis (*Feature Testing*)

Seluruh logika bisnis, otorisasi peran, validasi transaksi stok, sanitasi XSS, dan penegakan header keamanan telah diuji secara komprehensif menggunakan PHPUnit dan database MySQL `gudangku_test`:

```bash
php artisan test
```

### Ringkasan Hasil Pengujian:
```text
   PASS  Tests\Feature\ActivityLogTest (1 test)
   PASS  Tests\Feature\AuthTest (10 tests)
   PASS  Tests\Feature\CategoryCrudTest (7 tests)
   PASS  Tests\Feature\DashboardTest (5 tests)
   PASS  Tests\Feature\ExampleTest (1 test)
   PASS  Tests\Feature\ProductCrudTest (10 tests)
   PASS  Tests\Feature\ProfileTest (4 tests)
   PASS  Tests\Feature\ReportTest (4 tests)
   PASS  Tests\Feature\RoleAccessTest (4 tests)
   PASS  Tests\Feature\SecurityHardeningTest (5 tests)
   PASS  Tests\Feature\StockServiceTest (6 tests)
   PASS  Tests\Feature\StockTransactionTest (8 tests)
   PASS  Tests\Feature\SupplierCrudTest (6 tests)
   PASS  Tests\Feature\UserManagementTest (6 tests)
   PASS  Tests\Feature\XssEscapingTest (3 tests)

  Tests:    81 passed (365 assertions)
  Duration: 8.87s
```

---

## 9. Panduan Deployment (Railway Cloud)

Aplikasi telah disiapkan untuk proses *deployment* instan ke platform **Railway** dengan konfigurasi kontainer nir-state (*stateless*):

1. **Push Repository ke GitHub**: Pastikan branch `main` telah memuat seluruh pembaruan terkini.
2. **Buat Project Baru di Railway**: Pilih menu *New Project* $\rightarrow$ *Deploy from GitHub repo* $\rightarrow$ pilih repositori `gudangku`.
3. **Tambahkan Plugin MySQL**: Di dashboard project Railway, klik *+ New* $\rightarrow$ *Database* $\rightarrow$ *Add MySQL*.
4. **Konfigurasi Environment Variables di Service Web App**:
   ```ini
   APP_NAME=GudangKu
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:YOUR_GENERATED_APP_KEY
   APP_URL=https://your-app-name.up.railway.app
   APP_LOCALE=id
   APP_TIMEZONE=Asia/Jakarta

   LOG_CHANNEL=stderr
   LOG_LEVEL=info

   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

   SESSION_DRIVER=database
   SESSION_LIFETIME=120
   SESSION_SECURE_COOKIE=true
   SESSION_HTTP_ONLY=true
   SESSION_SAME_SITE=lax

   CACHE_STORE=database
   QUEUE_CONNECTION=sync
   BCRYPT_ROUNDS=12
   ```
5. **Konfigurasi Domain & Pre-Deploy Command**:
   - Di tab *Settings* $\rightarrow$ *Networking*, klik *Generate Domain* lalu salin URL yang diberikan ke variabel `APP_URL`.
   - Di tab *Deploy* $\rightarrow$ *Pre-deploy Command*, atur:
     ```bash
     php artisan migrate --force
     ```
   - Untuk inisialisasi data demo pertama kali, jalankan perintah `php artisan db:seed --force` melalui Railway CLI atau tab *One-off Command*.
6. **Health Check Path**: Konfigurasikan health check endpoint ke `/up`.

---

## 10. Implementasi Keamanan Aplikasi

- **Perlindungan CSRF**: Seluruh formulir bermetode `POST`, `PUT`, dan `DELETE` diverifikasi dengan token `@csrf`.
- **Sanitasi & Escaping XSS**:
  - Seluruh nilai variabel yang dirender di Blade menggunakan sintaks `{{ }}` (`htmlspecialchars`).
  - Input teks pengguna disanitasi secara otomatis menggunakan `strip_tags()` dan `trim()` di setiap `FormRequest` sebelum validasi.
- **Pencegahan SQL Injection**: Penggunaan Eloquent ORM dan PDO *parameterized binding* secara eksklusif di seluruh controller dan query builder.
- **Pencegahan Mass Assignment**: Penguncian kolom sensitif (`role`, `is_active`) di luar `$fillable` Model `User`.
- **Perlindungan Formula Injection CSV**: Karakter berbahaya (`=`, `+`, `-`, `@`, `\t`, `\r`) pada sel ekspor CSV otomatis dinetralkan dengan awalan tanda kutip tunggal (`'`).
- **Keamanan Sesi**: Regenerasi session ID saat autentikasi & perubahan kata sandi untuk mencegah *Session Fixation*, serta *rate limiting* untuk mencegah serangan *brute force*.
- **Security Response Headers**: Injeksi header keamanan `X-Frame-Options: SAMEORIGIN` (anti-Clickjacking), `X-Content-Type-Options: nosniff` (anti-MIME sniffing), `Referrer-Policy`, `Permissions-Policy`, dan `Strict-Transport-Security` (HSTS) pada lingkungan produksi HTTPS.

---

## 11. Panduan Troubleshooting

| Gejala Masalah | Penyebab Umum | Solusi Perbaikan |
| :--- | :--- | :--- |
| **`No application encryption key has been specified`** | Nilai `APP_KEY` pada file `.env` masih kosong. | Jalankan `php artisan key:generate`. |
| **`SQLSTATE[HY000] [2002] Connection refused`** | MySQL Server belum berjalan atau port salah. | Pastikan service MySQL/MariaDB aktif di port 3306. |
| **`SQLSTATE[HY000] [1045] Access denied for user`** | `DB_USERNAME` atau `DB_PASSWORD` di `.env` tidak cocok. | Periksa username dan password akun MySQL lokal Anda. |
| **`Base table or view not found`** | Tabel basis data belum dimigrasikan. | Jalankan `php artisan migrate --seed`. |
| **`419 Page Expired`** | Token CSRF kedaluwarsa atau sesi habis. | Muat ulang halaman formulir lalu coba kirim ulang. |
| **Perubahan konfigurasi `.env` tidak diterapkan** | Cache konfigurasi Laravel aktif. | Jalankan `php artisan config:clear`. |

---

## 12. Lisensi & Catatan Akademik

Proyek ini dikembangkan secara mandiri sebagai karya proyek akademik individual untuk Ujian Akhir Semester (UAS) mata kuliah **Web Application Development**, Universitas Cakrawala. Seluruh hak cipta kode program mengikuti lisensi sumber terbuka MIT License.
