<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GudangKu - Sistem Manajemen Inventaris Gudang">
    <title>@yield('title', 'Autentikasi') - GudangKu</title>

    <!-- Bootstrap 5.3 CSS & Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .auth-header {
            background: #0f172a;
            color: #ffffff;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }
        .auth-logo {
            font-size: 2.2rem;
            color: #38bdf8;
            margin-bottom: 0.5rem;
        }
        .auth-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.2);
        }
        .btn-primary-custom {
            background-color: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-primary-custom:hover {
            background-color: #0369a1;
            border-color: #0369a1;
            color: #ffffff;
        }
        .badge-role {
            font-size: 0.75rem;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <main class="auth-card">
        <header class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">GudangKu</h1>
            <p class="text-secondary small mb-0">Sistem Manajemen Inventaris Gudang</p>
        </header>

        <section class="auth-body">
            {{-- Flash Message Success --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Flash Message Error --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </section>
    </main>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
