# Panduan Deployment GudangKu ke Clever Cloud

Dokumen ini memuat panduan lengkap, arsitektur kesiapan cloud, checklist variabel lingkungan (*environment variables*), dan troubleshooting untuk men-deploy aplikasi **GudangKu** ke platform **Clever Cloud** menggunakan runtime **PHP (Apache + PHP-FPM)** dan **MySQL Add-on**.

---

## 1. Arsitektur Kesiapan Cloud (*Cloud Architecture*)

Aplikasi GudangKu dirancang mengikuti prinsip **12-Factor App** dan siap beroperasi di atas infrastruktur *stateless container* Clever Cloud:

| Komponen Cloud | Konfigurasi di GudangKu | Status |
| :--- | :--- | :--- |
| **Web Server & Document Root** | Berkas `clevercloud/php.json` mengonfigurasi `{"deploy": {"webroot": "/public"}}` sehingga Apache & PHP-FPM menyajikan `public/index.php`. Berkas inti Laravel tidak terekspos ke publik. | ✅ Terkonfigurasi |
| **MySQL Add-on Integration** | `config/database.php` mengimplementasikan rantai fallback (*fallback chain*) membaca environment variable bawaan MySQL Add-on (`MYSQL_ADDON_HOST`, `MYSQL_ADDON_PORT`, `MYSQL_ADDON_DB`, `MYSQL_ADDON_USER`, `MYSQL_ADDON_PASSWORD`). | ✅ Terintegrasi Otomatis |
| **Reverse Proxy & HTTPS Trust** | `bootstrap/app.php` mengonfigurasi `trustProxies` membaca `CC_REVERSE_PROXY_IPS` yang disuntikkan Clever Cloud (dengan fallback `'*'`). Menjamin deteksi HTTPS, IP klien (`X-Forwarded-For`), dan redirect berjalan aman tanpa risiko IP spoofing. | ✅ Terkonfigurasi |
| **Logging ke Syslog** | `config/logging.php` mendukung driver `syslog` bawaan Monolog (`LOG_CHANNEL=syslog`). Log aplikasi diteruskan langsung ke sistem syslog yang dikumpulkan secara *real-time* di Clever Cloud Console Logs. | ✅ Siap (`LOG_CHANNEL=syslog`) |
| **Stateless Container (Nir-State)** | - Session tersimpan di database (`SESSION_DRIVER=database` via tabel `sessions`).<br>- Cache tersimpan di database (`CACHE_STORE=database` via tabel `cache`).<br>- Ekspor laporan (CSV) langsung dialirkan melalui HTTP stream `php://output` tanpa menulis file temporer ke disk kontainer.<br>- Tidak ada fitur upload file ke disk lokal. | ✅ 100% Stateless |
| **Cookie Aman & HSTS** | `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`, dan middleware `SecurityHeaders` mengaktifkan `Strict-Transport-Security` (HSTS) pada production. | ✅ Aman |
| **Audit Seeder (Production-Safe)** | Seluruh seeder (`UserSeeder`, `CategorySeeder`, `SupplierSeeder`, `ProductSeeder`, `TransactionSeeder`) tidak menggunakan class dev-only (`Faker` / `Factory`), murni memakai data manual dan `StockService`, serta bersifat **idempotent**. | ✅ Aman untuk `--no-dev` |

---

## 2. Panduan Tahapan Deployment di Clever Cloud (Step-by-Step)

### Langkah 1: Push Repositori ke GitHub
Pastikan seluruh source code aplikasi GudangKu sudah berada di branch utama (`main`):
```bash
git add .
git commit -m "Siapkan konfigurasi deployment Clever Cloud"
git push origin main
```

### Langkah 2: Buat Aplikasi PHP di Clever Cloud
1. Masuk ke [Clever Cloud Console](https://console.clever-cloud.com/).
2. Klik tombol **Create** $\rightarrow$ Pilih **an application**.
3. Pilih sumber kode: **GitHub** $\rightarrow$ Hubungkan akun GitHub Anda dan pilih repositori `gudangku`.
4. Pilih tipe aplikasi: **PHP**.
5. Pilih ukuran instance (misalnya: **Nano** atau **XS** untuk kebutuhan evaluasi/kuliah).
6. Tentukan nama aplikasi (misal: `gudangku-app`) dan zona server (misal: **Paris** atau yang terdekat).
7. Klik **Next**.

### Langkah 3: Buat dan Sambungkan (*Link*) MySQL Add-on
1. Pada menu pembuatan Add-on berikutnya (atau via menu **Create** $\rightarrow$ **an add-on**):
   - Pilih **MySQL**.
   - Pilih paket (misalnya: paket **Free/Dev** atau **Shared**).
   - Tentukan nama database (misal: `gudangku-db`).
2. Sambungkan (*Link*) MySQL Add-on tersebut ke aplikasi PHP `gudangku-app`.
3. *Secara otomatis, Clever Cloud akan menyuntikkan variabel lingkungan `MYSQL_ADDON_*` ke dalam aplikasi PHP Anda.*

### Langkah 4: Konfigurasi Environment Variables di Aplikasi
Buka aplikasi Anda di Clever Cloud Console $\rightarrow$ Menu **Environment variables**. Masukkan variabel berikut:

```env
APP_NAME=GudangKu
APP_ENV=production
APP_KEY=base64:SALIN_DARI_PHP_ARTISAN_KEY_GENERATE_SHOW
APP_DEBUG=false
APP_URL=https://<app-id>.cleverapps.io
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=syslog
LOG_LEVEL=info

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=sync
BCRYPT_ROUNDS=12

CC_POST_BUILD_HOOK=php artisan migrate --force
```

> **Catatan `APP_KEY`**: Jika belum memiliki key production, generate melalui terminal lokal dengan perintah:
> ```bash
> php artisan key:generate --show
> ```
> Salin string `base64:...` tersebut ke nilai `APP_KEY`.

> **Catatan `APP_URL`**: Salin domain bawaan aplikasi Anda yang berakhiran `.cleverapps.io` (atau domain kustom Anda) dengan awalan `https://`.

### Langkah 5: Inisialisasi Database (Migrasi & Seed Data Demo)

1. **Migrasi Otomatis Saat Build**:
   Variabel `CC_POST_BUILD_HOOK="php artisan migrate --force"` yang sudah disetel pada Langkah 4 akan otomatis menjalankan migrasi tabel setiap kali deployment/re-deploy berlangsung.

2. **Seeding Data Demo Sekali Saja**:
   Untuk mengisi data awal (akun Admin/Manager/Staff, master produk, kategori, pemasok, dan transaksi awal):
   - Buka Clever Cloud Console $\rightarrow$ Pilih aplikasi `gudangku-app` $\rightarrow$ Menu **Environment variables**.
   - Tambahkan sementara hook seed ke dalam `CC_POST_BUILD_HOOK`:
     ```env
     CC_POST_BUILD_HOOK=php artisan migrate --force && php artisan db:seed --force
     ```
   - Klik **Restart** aplikasi untuk memicu build dan seeding.
   - Setelah deploy sukses dan data terisi, kembalikan `CC_POST_BUILD_HOOK` ke semula (`php artisan migrate --force`) agar seed tidak dijalankan ulang pada deploy berikutnya.
   - *(Catatan: Seeder GudangKu bersifat idempotent sehingga aman jika tidak sengaja terpanggil kembali).*

---

## 3. Checklist Environment Variables (Clever Cloud)

Gunakan checklist ini sebelum merilis aplikasi ke publik:

- [ ] `APP_NAME=GudangKu`
- [ ] `APP_ENV=production` *(Wajib production agar stack trace tidak bocor)*
- [ ] `APP_KEY=base64:...` *(Wajib diisi string 32-byte valid)*
- [ ] `APP_DEBUG=false` *(Wajib false)*
- [ ] `APP_URL=https://<nama-app>.cleverapps.io` *(Wajib diawali `https://`)*
- [ ] `APP_LOCALE=id`
- [ ] `APP_TIMEZONE=Asia/Jakarta`
- [ ] `LOG_CHANNEL=syslog` *(Tersalurkan ke agregator log Clever Cloud)*
- [ ] `LOG_LEVEL=info`
- [ ] `SESSION_DRIVER=database` *(Wajib database untuk arsitektur stateless)*
- [ ] `SESSION_LIFETIME=120`
- [ ] `SESSION_SECURE_COOKIE=true` *(Wajib true untuk enkripsi cookie via HTTPS)*
- [ ] `SESSION_HTTP_ONLY=true` *(Mencegah pencurian session via JavaScript)*
- [ ] `SESSION_SAME_SITE=lax` *(Mitigasi CSRF)*
- [ ] `CACHE_STORE=database`
- [ ] `QUEUE_CONNECTION=sync`
- [ ] `BCRYPT_ROUNDS=12`
- [ ] `CC_POST_BUILD_HOOK=php artisan migrate --force`
- [ ] `MYSQL_ADDON_*` *(Otomatis disuntikkan oleh Clever Cloud saat Add-on MySQL di-link)*
- [ ] `CC_REVERSE_PROXY_IPS` *(Otomatis disuntikkan oleh infrastruktur proxy Clever Cloud)*

---

## 4. Verifikasi dan Pengujian Aplikasi

### A. Uji Caching Artisan
Seluruh mekanisme caching produksi telah diuji dan terverifikasi bersih tanpa error:
- `php artisan route:cache` $\rightarrow$ Sukses (`Routes cached successfully.`)
- `php artisan config:cache` $\rightarrow$ Sukses (`Configuration cached successfully.`)
- `php artisan view:cache` $\rightarrow$ Sukses (`Blade templates cached successfully.`)

### B. Uji Instalasi Tanpa Paket Dev (`--no-dev`)
Simulasi `composer install --no-dev --dry-run` berhasil mengevaluasi pelepasan 35 dev-packages (Faker, PHPUnit, Mockery, Pint, Sail) tanpa merusak autoloader aplikasi maupun seeder produksi.

---

## 5. Panduan Troubleshooting Kendala Umum di Clever Cloud

| Gejala Error | Kemungkinan Penyebab | Tindakan Solusi |
| :--- | :--- | :--- |
| **HTTP 403 Forbidden / Directory Listing** | Document root mengarah ke root repositori, bukan `/public`. | Pastikan berkas `clevercloud/php.json` ada di git dengan isi `{"deploy": {"webroot": "/public"}}`. |
| **HTTP 500 saat membuka aplikasi** | `APP_KEY` kosong atau koneksi database gagal. | Buka tab **Logs** di Clever Cloud Console. Periksa apakah `APP_KEY` sudah disetel di tab *Environment variables*. |
| **`SQLSTATE[HY000] [2002] Connection refused`** | MySQL Add-on belum terhubung (*unlinked*) ke aplikasi. | Buka menu **Service Dependencies** di aplikasi Clever Cloud, pastikan MySQL Add-on sudah di-*link*. Periksa apakah variabel `MYSQL_ADDON_HOST` muncul di tab *Environment variables*. |
| **Loop Redirect HTTP ke HTTPS / Asset CSS tidak termuat** | Laravel tidak mendeteksi request HTTPS dari load balancer Clever Cloud. | Pastikan `bootstrap/app.php` mengonfigurasi `trustProxies(at: $proxies)` dengan membaca `CC_REVERSE_PROXY_IPS`, dan pastikan `APP_URL` menggunakan skema `https://`. |
| **Tabel `sessions` tidak ditemukan** | `CC_POST_BUILD_HOOK` belum disetel atau migrasi gagal berjalan. | Tambahkan variabel `CC_POST_BUILD_HOOK="php artisan migrate --force"` di tab *Environment variables* dan lakukan *Rebuild*. |
| **Data hilang / reset setelah deploy baru** | Data disimpan di disk kontainer yang bersifat ephemeral. | GudangKu sepenuhnya *stateless*: seluruh session, cache, dan data inventaris disimpan di MySQL Add-on, bukan di disk lokal kontainer. |
