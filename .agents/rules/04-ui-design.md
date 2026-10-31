# UI Design Rules GudangKu (ALWAYS FOLLOW untuk semua pekerjaan tampilan)

Referensi visual: docs/design/reference-dashboard.jpeg (dashboard inventori
bergaya modern: sidebar gelap, panel konten putih membulat, aksen hijau lime).
Tiru GAYA dan struktur layout-nya, BUKAN merek/teks/logonya. Nama aplikasi:
GudangKu. Jangan memakai kata "vault", jangan membuat kartu "Upgrade Pro".

## Batasan teknis
- Bootstrap 5.3 (CDN) hanya untuk grid, utilitas, modal, dropdown, form.
- Tampilan khas ditulis di SATU file: public/css/gudangku.css (CSS variables
  + override). Dilarang template admin siap pakai atau package UI.
- Ikon: Bootstrap Icons (CDN). Font: Outfit (Google Fonts CDN), fallback
  system-ui, sans-serif. Grafik: Chart.js (CDN). Tanpa @vite.
- Gauge setengah lingkaran dibuat sendiri dengan SVG sederhana (stroke-dasharray),
  bukan library.

## Design tokens (taruh sebagai CSS variables di :root)
Warna (perkiraan dari gambar, boleh disetel mendekati):
- --gk-bg:            #17171a   (latar luar / body)
- --gk-sidebar:       #1d1d20   (sidebar)
- --gk-sidebar-text:  #a6a6ad ; --gk-sidebar-active: #ffffff
- --gk-panel:         #ffffff   (panel konten utama)
- --gk-surface:       #f6f6f3   (latar kartu sekunder / input)
- --gk-border:        #ececea
- --gk-text:          #18181b ; --gk-muted: #7a7a83
- --gk-accent:        #d8f23a   (lime, aksen utama) ; --gk-accent-dark: #b9d11f
- --gk-dark-card:     #1d1d20   (kartu gelap pertama / tombol primer)
- --gk-success: #2f9e5b ; --gk-warning: #f2b630 ; --gk-danger: #e5484d
- Status chip memakai latar pastel (hijau/kuning/merah muda) + teks lebih gelap.
Bentuk:
- Radius: panel 24px, card 16px, tombol & chip & input search = pill (999px).
- Shadow sangat halus (0 1px 2px rgba(0,0,0,.04)); utamakan border 1px.
- Spasi lega: padding card 20-24px, gap grid 16px.
Tipografi:
- Judul halaman 28px/600; judul card 15px/600; angka statistik 28-32px/600;
  label kecil 12-13px warna muted.

## Struktur layout (layouts/app.blade.php)
1. Body gelap (--gk-bg). Sidebar kiri gelap, lebar 250px, position sticky, tinggi 100vh, flex column.
   - Atas: logo kotak lime + teks "GudangKu" (flex-shrink: 0).
   - Tengah: daftar menu scrollable sendiri (`.gk-sidebar-nav-container`, overflow-y: auto, scrollbar tipis). Tinggi tiap item menu sekitar 42-44px dengan spasi rapat agar menu admin (11 item) muat pada layar tinggi ~880px tanpa scroll.
   - Item aktif: teks putih + latar #232328 + penanda vertikal/indikator lime di kiri.
   - Bawah: Blok GENERAL menempel di bawah sidebar (`margin-top: auto; flex-shrink: 0`), berisi label "GENERAL", menu "Profil Saya", dan kartu user tepat di bawahnya.
   - Kartu User & Logout: avatar inisial, nama (dengan ellipsis), dan role tampil UTUH (tidak boleh terpotong). Menu "Keluar" DITIADAKAN dari daftar link teks GENERAL; saat kartu user diklik, muncul dropdown/dropup kecil (`data-bs-popper-config='{"strategy":"fixed"}'`, buka ke atas/samping). Dropdown berisi info akun, menu profil, divider, dan form POST ke `route('logout')` dengan `@csrf` dan tombol submit merah. Dropdown menutup saat klik di luar atau tekan Esc, serta keyboard-accessible. Berlaku juga pada sidebar mobile offcanvas.
2. Konten: SATU panel putih besar di sebelah sidebar. Panel menempel ke tepi kanan, atas, dan bawah viewport; radius kanan 0; radius kiri dipertahankan (24px, `border-radius: 24px 0 0 24px`). Panel memenuhi tinggi layar (`min-height: 100vh; margin: 0`) sehingga tidak ada area gelap di bawah panel pada halaman pendek.
3. Topbar: judul halaman dinamis sesuai konteks halaman (kiri, dikirim lewat `@section('page_title')`), search pill (tengah, opsional), tombol filter ikon, ikon notifikasi, dan tombol aksi / tombol kembali di kanan atas (`@section('page_actions')`).
4. Mobile (<992px): sidebar jadi offcanvas, panel konten penuh lebar (radius 0).
5. Flash message: alert membulat di atas konten, ikon + warna sesuai tipe.

## Komponen
- Tombol "Kembali" Konsisten: Semua halaman detail (show), edit, dan create (produk, kategori, pemasok, pengguna, detail transaksi, form stok masuk/keluar/penyesuaian, profil) WAJIB memiliki tombol "Kembali" seragam menggunakan komponen `<x-back-button :route="..." />` (pill outline, ikon `bi-arrow-left`) di kanan atas area judul (`@section('page_actions')`), menuju route eksplisit index induknya (bukan `history.back`).
- Stat card: ikon dalam kotak kecil membulat (lime pada kartu gelap), label
  muted, angka besar, chip tren kecil ("+12% sejak bulan lalu" hanya bila
  data nyata dihitung; jika tidak, tampilkan keterangan netral). Kartu
  PERTAMA bergaya gelap (latar --gk-dark-card, teks putih, ikon lime).
- Tombol primer = pill gelap teks putih; sekunder = pill outline border;
  aksen lime dipakai untuk hover/fokus, ikon aktif, dan highlight data.
- Tabel: tanpa garis vertikal, header muted 12px kapital ringan, baris tinggi
  nyaman, hover lembut, kolom aksi berisi ikon (lihat/ubah/hapus), avatar/thumb
  inisial kecil untuk nama produk.
- Tab filter berbentuk chip pill di atas tabel (Semua | Menipis | Habis): Chip yang sedang dipilih/aktif WAJIB berwarna HITAM/gelap (`var(--gk-dark-card)`) dengan teks putih, chip lain outline transparan dengan border halus. Dilengkapi atribut aksesibilitas `role="tab"` dan `aria-selected` / `aria-controls`.
- Badge status stok: Aman (hijau pastel), Menipis (kuning pastel), Habis
  (merah muda). Selalu teks + warna (bukan warna saja).
- Form: input membulat 12px, label di atas, error merah di bawah field,
  tombol simpan pill gelap + tombol batal outline.
- Grafik Chart.js: garis halus (tension .4) berwarna lime-gelap dengan
  gradient area lime pucat di bawahnya; grid horizontal tipis; tooltip
  gelap membulat; dua seri (Stok Masuk = lime, Stok Keluar = abu gelap).
- Empty state: ikon besar pucat + teks singkat + tombol aksi.
- Modal konfirmasi hapus: bersih, tombol Hapus merah pill.

## Pemetaan gambar -> GudangKu (JANGAN menambah fitur di luar ini)
Menu sidebar (sesuai role): Dashboard, Produk, Kategori, Pemasok,
Stok Masuk, Stok Keluar, Penyesuaian (manager/admin), Riwayat Transaksi,
Laporan (manager/admin), Pengguna (admin), Activity Log (admin).
GENERAL: Profil Saya, Kartu User (dengan dropdown Logout).

Dashboard (mengikuti susunan gambar):
- Baris 1 (4 stat card): [gelap] Total Produk | Total Unit Stok |
  Nilai Persediaan (disembunyikan untuk staff, ganti "Transaksi Hari Ini") |
  Stok Menipis (boleh pakai gauge setengah lingkaran: persen produk menipis).
- Baris 2 kiri (lebar): grafik area "Stok Masuk vs Keluar" 14 hari, dengan
  tooltip. Kanan: tabel "Produk per Kategori / Stok Menipis" dengan tab chip.
- Baris 3 (3 card): "Transaksi Terbaru" (tabel kecil + tipe IN/OUT berwarna) |
  "Rekomendasi Restock" = daftar produk dengan stock <= min_stock beserta
  saran jumlah (min_stock*2 - stock); BERBASIS ATURAN, jangan diberi label
  AI | "Stok Habis" (produk stock = 0) dengan tombol "Stok Masuk".
Halaman Produk meniru "Inventory" di gambar: header + search + filter +
tombol "Tambah Produk", tabel produk dengan thumb inisial, kolom SKU,
kategori, stok, harga, status, aksi.

## Hal yang DILARANG
- Menyalin logo, nama, atau teks merek dari gambar referensi.
- Menambah modul yang tidak ada di scope (Shipment, Customers, Store,
  AI Assistant, upgrade/premium).
- Data palsu hardcode di view. Semua angka dari database (placeholder hanya
  di Fase 3, wajib diganti di Fase 6).
- Logika bisnis atau query di Blade. Output user tetap {{ }} (tanpa {!! !!}).
- Menurunkan keterbacaan: kontras teks minimal AA, ukuran teks isi >= 14px.
- Menghabiskan waktu pada animasi/efek. Prioritaskan konsistensi komponen.
