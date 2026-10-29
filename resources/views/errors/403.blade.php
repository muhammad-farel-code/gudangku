<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | GudangKu</title>
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/gudangku.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: var(--gk-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .gk-error-card {
            background: #ffffff;
            border-radius: var(--gk-radius-panel);
            box-shadow: var(--gk-shadow-panel);
            max-width: 480px;
            width: 100%;
            text-align: center;
            padding: 3rem 2rem;
        }
        .gk-error-code {
            font-size: 5rem;
            font-weight: 800;
            color: var(--gk-danger);
            line-height: 1;
        }
    </style>
</head>
<body>
    <main class="gk-error-card">
        <div style="font-size: 3.5rem; color: var(--gk-danger);" class="mb-2">
            <i class="bi bi-shield-slash-fill"></i>
        </div>
        <div class="gk-error-code mb-2">403</div>
        <h1 class="h4 fw-bold text-dark mb-2">Akses Ditolak (Forbidden)</h1>
        <p class="text-muted small mb-4">
            {{ $exception->getMessage() ?: 'Akun Anda tidak memiliki izin atau peran (role) yang sesuai untuk mengakses modul ini.' }}
        </p>

        @auth
            <div class="p-2 px-3 rounded-3 small text-muted mb-4" style="background-color: var(--gk-surface); border: 1px solid var(--gk-border);">
                Masuk sebagai: <strong>{{ Auth::user()->name }}</strong> (Role: <span class="badge bg-dark text-uppercase ms-1">{{ Auth::user()->role }}</span>)
            </div>
        @endauth

        <div class="d-flex justify-content-center gap-2">
            <a href="javascript:history.back()" class="btn btn-gk-outline px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-gk-dark px-4">
                <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
            </a>
        </div>
    </main>
</body>
</html>
