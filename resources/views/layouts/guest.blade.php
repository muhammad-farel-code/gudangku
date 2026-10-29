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

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- GudangKu Custom Styles -->
    <link href="{{ asset('css/gudangku.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--gk-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .gk-auth-container {
            width: 100%;
            max-width: 450px;
        }
        .gk-auth-card {
            background: #ffffff;
            border-radius: var(--gk-radius-panel);
            box-shadow: var(--gk-shadow-panel);
            padding: 2.5rem 2.25rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .gk-auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 1.5rem;
        }
        .gk-auth-brand .gk-brand-logo {
            width: 44px;
            height: 44px;
            font-size: 1.4rem;
        }
        .gk-auth-brand .gk-brand-title {
            font-size: 1.6rem;
            color: var(--gk-text);
        }
    </style>
</head>
<body>

    <main class="gk-auth-container">
        <div class="gk-auth-card">
            <!-- Brand Logo Header -->
            <div class="gk-auth-brand">
                <div class="gk-brand-logo">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <span class="gk-brand-title">GudangKu</span>
            </div>

            <!-- Flash Alert -->
            @include('partials.flash')

            <!-- Form Content -->
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
