\---------- FILE 3: .agents/rules/02-security.md ----------

\# Security Rules GudangKu (NON-NEGOTIABLE)



1\. CSRF: semua form non-GET punya @csrf. Jangan menambah pengecualian CSRF.

2\. XSS: escape default Blade; strip\_tags + trim di prepareForValidation()

&#x20;  untuk field teks bebas. Tidak ada {!! !!} untuk input user.

3\. SQL Injection: hanya Eloquent/Query Builder dengan binding. Raw SQL hanya

&#x20;  dengan binding atau ekspresi statis; sorting dinamis lewat whitelist.

4\. Auth manual: Auth::attempt dengan kondisi is\_active=1; pesan gagal generik;

&#x20;  session()->regenerate() saat login; invalidate() + regenerateToken() saat

&#x20;  logout (logout via POST).

5\. Password: Hash (cast 'hashed'), Password::defaults() min 8 + mixedCase +

&#x20;  numbers. Jangan pernah log/echo password.

6\. Mass assignment: $fillable eksplisit. `role` \& `is\_active` TIDAK boleh

&#x20;  masuk dari registrasi publik. Registrasi publik = role staff.

7\. RBAC: terapkan di server (middleware + Policy). Menyembunyikan tombol di

&#x20;  Blade BUKAN keamanan. Admin tidak boleh hapus/nonaktifkan/ubah role diri

&#x20;  sendiri; minimal 1 admin aktif.

8\. Rate limit login: 5 percobaan/menit per email+IP.

9\. Header keamanan via middleware SecurityHeaders.

10\. Production: APP\_DEBUG=false, APP\_ENV=production, cookie secure, log ke stderr.

11\. Export CSV: netralkan formula injection (= + - @ di awal sel).

12\. Jangan commit .env / kredensial. Hanya .env.example.



