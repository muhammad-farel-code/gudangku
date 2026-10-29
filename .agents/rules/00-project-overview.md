\---------- FILE 1: .agents/rules/00-project-overview.md ----------

\# GudangKu - Project Overview (ALWAYS FOLLOW)



Ini proyek UAS kuliah (Web Application Development, individual). Tujuan:

aplikasi Sistem Manajemen Inventaris Gudang, full-stack server-side,

Laravel + MySQL, MVC, autentikasi + RBAC aman, siap deploy ke Railway.

Pemilik proyek HARUS memahami dan bisa menjelaskan setiap baris kode.



\## Stack (WAJIB, jangan diganti)

\- Laravel versi yang terbaru (Laravel 13).

\- PHP sesuai composer.json. Database: MySQL SAJA (bukan SQLite/PostgreSQL).

\- View: Blade. UI: Bootstrap 5 + Bootstrap Icons + Chart.js lewat CDN.

\- Session driver: database. Test DB: MySQL `gudangku\_test`.



\## DILARANG KERAS

\- Memasang package composer/npm baru TANPA izin eksplisit pemilik.

\- Memakai starter kit / boilerplate / generator: Breeze, Jetstream, Fortify

&#x20; starter, Filament, Backpack, Voyager, Livewire starter, `make:auth`

&#x20; dari pihak ketiga, template admin siap pakai. Auth ditulis manual.

\- Mengubah database ke selain MySQL.

\- Menghapus/menimpa file di luar scope tugas fase saat ini.

\- Menjalankan `migrate:fresh`, `db:wipe`, `rm -rf`, `git reset --hard`,

&#x20; `git push --force` tanpa meminta konfirmasi lebih dulu.

\- Commit atau menampilkan isi .env / secret.



\## Sintaks Laravel

\- Pakai sintaks sesuai versi terpasang. Laravel 11+ TIDAK punya

&#x20; app/Http/Kernel.php: middleware/alias/exception diatur di bootstrap/app.php.

&#x20; Cek `composer.json` dan dokumentasi versi tsb sebelum menulis kode.

\- Jangan mengarang method/facade. Jika ragu, baca vendor/ atau tanyakan.



\## Cara kerja

\- Kerjakan HANYA fase yang diminta. Tulis rencana singkat dulu, tunggu OK.

\- Setelah selesai: ringkas file yang dibuat/diubah + cara mengujinya.

\- Kode harus mudah dijelaskan: sederhana, bernama jelas, ada komentar singkat

&#x20; (Bahasa Indonesia) pada logika penting (transaksi DB, keamanan, otorisasi).



