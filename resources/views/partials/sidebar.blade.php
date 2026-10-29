<aside class="gk-sidebar">
    <div>
        <!-- Brand Logo & Name -->
        <a href="{{ route('dashboard') }}" class="gk-sidebar-brand">
            <div class="gk-brand-logo">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <span class="gk-brand-title">GudangKu</span>
        </a>

        <!-- Menu Section: Main / Menu -->
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

        <!-- General Section -->
        <p class="gk-menu-group-label mt-3">General</p>
        <ul class="gk-sidebar-nav">
            <li class="nav-item">
                <a href="{{ url('/profile') }}" class="gk-sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                    @csrf
                    <button type="submit" class="gk-sidebar-link text-start w-100 bg-transparent border-0">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- User Profile Footer -->
    <div class="gk-sidebar-footer">
        @auth
            <div class="gk-user-card">
                <div class="gk-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="gk-user-info">
                    <div class="gk-user-name">{{ Auth::user()->name }}</div>
                    <div class="gk-user-role">{{ Auth::user()->role }}</div>
                </div>
                <i class="bi bi-chevron-right text-secondary small"></i>
            </div>
        @endauth
    </div>
</aside>
