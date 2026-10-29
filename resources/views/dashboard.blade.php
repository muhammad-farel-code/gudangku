<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GudangKu</title>
    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="bi bi-box-seam-fill text-info"></i> GudangKu
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light small">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                    <span class="badge bg-primary text-uppercase ms-1">{{ Auth::user()->role }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 fw-bold text-dark mb-2">Dashboard Operasional</h1>
                <p class="text-muted">Selamat datang di Sistem Manajemen Inventaris GudangKu.</p>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Status Autentikasi:</span>
                            <div class="fw-bold text-success mt-1"><i class="bi bi-shield-check me-1"></i> Terotentikasi Aman</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Hak Akses (Role):</span>
                            <div class="fw-bold text-dark mt-1 text-uppercase">{{ Auth::user()->role }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Driver Sesi:</span>
                            <div class="fw-bold text-dark mt-1">Database (MySQL)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
