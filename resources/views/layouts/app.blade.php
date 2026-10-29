<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GudangKu - Sistem Manajemen Inventaris Gudang">
    <title>@yield('title', 'Dashboard') - GudangKu</title>

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- GudangKu Custom Modern Theme -->
    <link href="{{ asset('css/gudangku.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">
        <!-- Desktop Sidebar -->
        @include('partials.sidebar')

        <!-- Mobile Offcanvas Sidebar (<992px) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" style="background-color: var(--gk-bg); width: 280px;">
            <div class="offcanvas-header border-bottom border-dark">
                <div class="d-flex align-items-center gap-2">
                    <div class="gk-brand-logo">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <span class="gk-brand-title">GudangKu</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('partials.sidebar')
            </div>
        </div>

        <!-- Main Floating White Panel -->
        <main class="gk-main-panel">
            <!-- Topbar Header -->
            <header class="gk-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="gk-mobile-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-label="Toggle navigation">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="gk-page-title">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div class="gk-topbar-actions">
                    <!-- Search Box Pill -->
                    @hasSection('search_action')
                        @yield('search_action')
                    @else
                        <div class="gk-search-box d-none d-md-block">
                            <i class="bi bi-search"></i>
                            <input type="text" class="gk-search-input" placeholder="Cari di sini...">
                        </div>
                    @endif

                    <!-- Filter Icon Button -->
                    <button type="button" class="gk-btn-icon" title="Filter Data">
                        <i class="bi bi-sliders2"></i>
                    </button>

                    <!-- Notifications Button -->
                    <button type="button" class="gk-btn-icon" title="Notifikasi Stok">
                        <i class="bi bi-bell"></i>
                        <span class="gk-notification-dot"></span>
                    </button>

                    <!-- Page Actions Slot (e.g. Primary Dark Pill Button) -->
                    @yield('page_actions')
                </div>
            </header>

            <!-- Flash Alert Messages -->
            @include('partials.flash')

            <!-- Main Page Content -->
            <section class="gk-content-body flex-grow-1">
                @yield('content')
            </section>
        </main>
    </div>

    <!-- Global Delete Confirm Modal -->
    @include('partials.confirm-delete')

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>
</html>
