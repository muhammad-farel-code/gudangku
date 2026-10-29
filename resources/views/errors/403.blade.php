<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | GudangKu</title>
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            padding: 1.5rem;
        }
        .error-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 520px;
            width: 100%;
            text-align: center;
            padding: 3rem 2rem;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #e11d48;
            line-height: 1;
        }
        .error-icon {
            font-size: 4rem;
            color: #f43f5e;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <main class="error-card">
        <div class="error-icon">
            <i class="bi bi-shield-slash-fill"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="h4 fw-bold text-dark mt-2 mb-2">Akses Dilarang (Forbidden)</h1>
        <p class="text-secondary mb-4">
            {{ $exception->getMessage() ?: 'Maaf, akun Anda tidak memiliki hak akses atau izin yang cukup untuk membuka halaman ini.' }}
        </p>

        @auth
            <div class="alert alert-light border small text-muted mb-4 py-2">
                Masuk sebagai: <strong>{{ Auth::user()->name }}</strong> (Role: <span class="badge bg-secondary text-uppercase">{{ Auth::user()->role }}</span>)
            </div>
        @endauth

        <div class="d-flex justify-content-center gap-2">
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                <i class="bi bi-speedometer2 me-1"></i> Ke Dashboard
            </a>
        </div>
    </main>
</body>
</html>
