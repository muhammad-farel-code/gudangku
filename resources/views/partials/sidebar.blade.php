@php
    $currentSidebarId = $sidebarId ?? 'desktop';
@endphp
<aside class="gk-sidebar">
    <!-- Brand Logo & Name -->
    <a href="{{ route('dashboard') }}" class="gk-sidebar-brand">
        <div class="gk-brand-logo">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <span class="gk-brand-title">GudangKu</span>
    </a>

    <!-- Menu Section: Scrollable Middle Container -->
    <div class="gk-sidebar-nav-container">
        <p class="gk-menu-group-label">Menu Utama</p>
        <ul class="gk-sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="gk-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/products') }}" class="gk-sidebar-link {{ request()->is('products*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Produk</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/categories') }}" class="gk-sidebar-link {{ request()->is('categories*') ? 'active' : '' }}">
                    <i class="bi bi-tag-fill"></i>
                    <span>Kategori</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/suppliers') }}" class="gk-sidebar-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Pemasok</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/stock/in') }}" class="gk-sidebar-link {{ request()->is('stock/in*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-in-down"></i>
                    <span>Stok Masuk</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/stock/out') }}" class="gk-sidebar-link {{ request()->is('stock/out*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-up"></i>
                    <span>Stok Keluar</span>
                </a>
            </li>

            {{-- Khusus Manager & Admin: Penyesuaian Stok --}}
            @if(Auth::check() && Auth::user()->hasRole('manager', 'admin'))
                <li class="nav-item">
                    <a href="{{ url('/stock/adjustment') }}" class="gk-sidebar-link {{ request()->is('stock/adjustment*') ? 'active' : '' }}">
                        <i class="bi bi-sliders"></i>
                        <span>Penyesuaian</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a href="{{ url('/transactions') }}" class="gk-sidebar-link {{ request()->is('transactions*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </li>

            {{-- Khusus Manager & Admin: Laporan --}}
            @if(Auth::check() && Auth::user()->hasRole('manager', 'admin'))
                <li class="nav-item">
                    <a href="{{ url('/reports/stock') }}" class="gk-sidebar-link {{ request()->is('reports*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span>Laporan</span>
                    </a>
                </li>
            @endif

            {{-- Khusus Admin: Manajemen Pengguna & Activity Log --}}
            @if(Auth::check() && Auth::user()->hasRole('admin'))
                <li class="nav-item">
                    <a href="{{ url('/users') }}" class="gk-sidebar-link {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/activity-logs') }}" class="gk-sidebar-link {{ request()->is('activity-logs*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Activity Log</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    <!-- General Section & User Card: Pinned to bottom -->
    <div class="gk-sidebar-bottom mt-auto flex-shrink-0 pt-2">
        <p class="gk-menu-group-label">General</p>
        <ul class="gk-sidebar-nav mb-2">
            <li class="nav-item">
                <a href="{{ url('/profile') }}" class="gk-sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
        </ul>

        <!-- User Profile Dropup with Logout -->
        @auth
            <div class="dropup position-relative w-100">
                <button type="button" 
                        class="gk-user-card w-100 text-start border-0" 
                        id="userCardDropdown-{{ $currentSidebarId }}" 
                        data-bs-toggle="dropdown" 
                        data-bs-popper-config='{"strategy":"fixed"}' 
                        aria-expanded="false"
                        aria-haspopup="true">
                    <div class="gk-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="gk-user-info">
                        <div class="gk-user-name" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                        <div class="gk-user-role">{{ Auth::user()->role }}</div>
                    </div>
                    <i class="bi bi-chevron-up text-secondary small ms-auto flex-shrink-0"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark gk-user-dropdown shadow" aria-labelledby="userCardDropdown-{{ $currentSidebarId }}">
                    <li>
                        <div class="px-3 py-2 border-bottom border-dark">
                            <div class="fw-semibold text-white small text-truncate">{{ Auth::user()->name }}</div>
                            <div class="text-secondary" style="font-size: 0.72rem;">{{ Auth::user()->email }}</div>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ url('/profile') }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Pengaturan Akun</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0" id="logout-form-{{ $currentSidebarId }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</aside>
