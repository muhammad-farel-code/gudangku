\---------- FILE 2: .agents/rules/01-coding-standards.md ----------

\# Coding Standards GudangKu



\## Arsitektur

\- MVC + FormRequest + Service + Policy. Controller TIPIS (maks \~25 baris/method).

\- Logika stok HANYA di App\\Services\\StockService. Controller tidak mengubah

&#x20; kolom products.stock secara langsung.

\- Validasi di FormRequest (bukan di controller). Pakai $request->validated().

\- Otorisasi: middleware `role` untuk group route + Policy via authorize().

\- Route model binding. Resource controller bila cocok. Nama route konsisten

&#x20; (products.index, stock.out.store, dst.).



\## Database

\- Migration satu tabel per file, FK eksplisit (constrained + restrictOnDelete /

&#x20; nullOnDelete sesuai rencana). Index pada kolom yang difilter.

\- Eloquent untuk CRUD \& relasi (hasMany/belongsTo, eager loading with()

&#x20; untuk mencegah N+1). Query Builder boleh untuk agregasi laporan/dashboard.

\- Setiap perubahan stok: DB::transaction + lockForUpdate. Stok tidak boleh

&#x20; negatif. Transaksi stok immutable (tanpa update/delete).

\- Seeder idempotent (firstOrCreate/updateOrCreate atau guard exists()).



\## View

\- Blade layout + partials. Semua output pakai {{ }}. JANGAN {!! !!} untuk

&#x20; data user. Data ke JS pakai @json / Js::from.

\- Setiap form: @csrf (+ @method). Error per field + old().

\- Bahasa UI: Indonesia. Format Rupiah \& tanggal Indonesia lewat helper/accessor.



\## Gaya

\- PSR-12, type hint \& return type, nama variabel Inggris untuk kode, teks UI

&#x20; Indonesia. Tidak ada kode mati / debug (dd, dump, console.log) di commit.

\- Tidak ada query di Blade. Tidak ada logika bisnis di Blade.





