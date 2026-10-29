@extends('layouts.app')

@section('title', 'Dashboard Inventaris')
@section('page_title', 'Dashboard')

@section('page_actions')
    @if(Auth::check() && Auth::user()->hasRole('admin', 'manager'))
        <a href="{{ route('products.create') }}" class="btn btn-dark gk-btn-pill">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    @endif
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- BARIS 1: 4 STAT CARDS (Sesuai Gaya Visual Referensi) --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Produk (Dark Card + Lime Icon) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 p-4 h-100 position-relative overflow-hidden" 
                 style="background: #18181b; color: #ffffff;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="small text-uppercase fw-semibold" style="letter-spacing: 0.5px; color: #a1a1aa;">
                        Total Produk Aktif
                    </span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" 
                         style="width: 42px; height: 42px; background: rgba(216, 242, 58, 0.15); color: #d8f23a;">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                </div>
                <div class="fs-2 fw-bold mb-1 text-white">
                    {{ number_format($totalProducts) }}
                </div>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="badge bg-light text-dark rounded-pill" style="font-size: 0.75rem;">Operasional</span>
                    <span style="color: #a1a1aa;">Siap ditransaksikan</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Unit Stok (Light Card) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="small text-uppercase fw-semibold text-secondary" style="letter-spacing: 0.5px;">
                        Total Unit Stok
                    </span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" 
                         style="width: 42px; height: 42px; background: #eef9f1; color: #16a34a;">
                        <i class="bi bi-layers-fill fs-5"></i>
                    </div>
                </div>
                <div class="fs-2 fw-bold mb-1 text-dark">
                    {{ number_format($totalStockUnits) }}
                </div>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.75rem;">Fisik</span>
                    <span>Total seluruh barang</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Nilai Persediaan (Admin/Manager) ATAU Transaksi Hari Ini (Staff) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                @if($isManagerOrAdmin)
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="small text-uppercase fw-semibold text-secondary" style="letter-spacing: 0.5px;">
                            Nilai Persediaan
                        </span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 42px; height: 42px; background: #fef7e6; color: #d97706;">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold mb-1 text-dark">
                        Rp {{ number_format($inventoryValue ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted d-flex align-items-center gap-1">
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill" style="font-size: 0.75rem;">Aset</span>
                        <span>Stok &times; harga beli</span>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="small text-uppercase fw-semibold text-secondary" style="letter-spacing: 0.5px;">
                            Transaksi Hari Ini
                        </span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 42px; height: 42px; background: #eff6ff; color: #2563eb;">
                            <i class="bi bi-arrow-left-right fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold mb-1 text-dark">
                        {{ number_format($todayTransactionsCount ?? 0) }}
                    </div>
                    <div class="small text-muted d-flex align-items-center gap-1">
                        <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.75rem;">Aktivitas</span>
                        <span>Mutasi hari ini</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 4: Stok Menipis dengan SVG Half-Circle Gauge --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="small text-uppercase fw-semibold text-secondary" style="letter-spacing: 0.5px;">
                        Stok Menipis
                    </span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" 
                         style="width: 42px; height: 42px; background: #fdeced; color: #dc2626;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fs-2 fw-bold text-dark">
                            {{ number_format($lowStockCount) }} <span class="fs-6 fw-normal text-secondary">Item</span>
                        </div>
                        <div class="small text-danger fw-medium">
                            {{ $outOfStockCount }} produk habis
                        </div>
                    </div>

                    {{-- SVG Half-Circle Gauge --}}
                    <div style="width: 76px; height: 46px; position: relative;">
                        @php
                            $circumference = 125.6; // pi * radius (40)
                            $dashOffset = $circumference - (($lowStockPercentage / 100) * $circumference);
                            $strokeColor = $lowStockPercentage > 30 ? '#dc2626' : ($lowStockPercentage > 15 ? '#f59e0b' : '#16a34a');
                        @endphp
                        <svg viewBox="0 0 100 55" width="76" height="46">
                            <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#f1f1f0" stroke-width="8" stroke-linecap="round" />
                            <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="{{ $strokeColor }}" stroke-width="8" 
                                  stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ max(0, $dashOffset) }}" />
                        </svg>
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; text-align: center; font-size: 0.72rem; font-weight: 700; color: #27272a;">
                            {{ $lowStockPercentage }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 2: GRAFIK TREN & TABEL PRODUK TAB CHIP --}}
    <div class="row g-3 mb-4">
        {{-- Kiri: Grafik Stok Masuk vs Keluar 14 Hari --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-0 text-dark">Volume Stok Masuk vs Keluar</h2>
                        <span class="text-secondary small">Tren mutasi barang dalam 14 hari terakhir</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #b5cd28;"></span>
                            <span class="small text-secondary">Masuk</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #27272a;"></span>
                            <span class="small text-secondary">Keluar</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas Chart.js -->
                <div style="height: 270px; position: relative;">
                    <canvas id="stockTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Kanan: Preview Tabel dengan Tab Chips --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 fw-bold mb-0 text-dark">Katalog Produk</h2>
                        <a href="{{ route('products.index') }}" class="text-decoration-none small text-secondary">Lihat Semua &rarr;</a>
                    </div>

                    <!-- Filter Chips / Tabs -->
                    <ul class="nav nav-pills gap-1 mb-3" id="productTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-3 py-1 text-dark small" 
                                    id="all-tab" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab"
                                    style="background-color: #f4f4f5; font-weight: 500;">
                                Semua
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-1 text-dark small" 
                                    id="low-tab" data-bs-toggle="pill" data-bs-target="#tab-low" type="button" role="tab"
                                    style="background-color: #f4f4f5; font-weight: 500;">
                                Menipis ({{ $lowStockProducts->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-1 text-dark small" 
                                    id="out-tab" data-bs-toggle="pill" data-bs-target="#tab-out" type="button" role="tab"
                                    style="background-color: #f4f4f5; font-weight: 500;">
                                Habis ({{ $outOfStockProducts->count() }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="productTabContent">
                        {{-- Tab 1: Semua Produk --}}
                        <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody class="small">
                                        @forelse($allProducts as $p)
                                            <tr>
                                                <td class="ps-0 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                            {{ strtoupper(substr($p->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $p->name }}</div>
                                                            <div class="text-secondary" style="font-size: 0.72rem;">{{ $p->sku }} &bull; {{ $p->category->name ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold py-2.5">
                                                    {{ number_format($p->stock) }} <span class="fw-normal text-secondary">{{ $p->unit }}</span>
                                                </td>
                                                <td class="text-end pe-0 py-2.5">
                                                    @if($p->stock_status === 'Habis')
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill">Habis</span>
                                                    @elseif($p->stock_status === 'Menipis')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Menipis</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success rounded-pill">Aman</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-3 text-secondary">Belum ada produk.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 2: Stok Menipis --}}
                        <div class="tab-pane fade" id="tab-low" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody class="small">
                                        @forelse($lowStockProducts as $p)
                                            <tr>
                                                <td class="ps-0 py-2.5">
                                                    <div class="fw-semibold text-dark">{{ $p->name }}</div>
                                                    <div class="text-secondary" style="font-size: 0.72rem;">Min: {{ $p->min_stock }} {{ $p->unit }}</div>
                                                </td>
                                                <td class="text-end fw-bold text-warning-emphasis py-2.5">
                                                    {{ number_format($p->stock) }} {{ $p->unit }}
                                                </td>
                                                <td class="text-end pe-0 py-2.5">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Menipis</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-3 text-secondary">Semua stok produk dalam kondisi aman.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 3: Stok Habis --}}
                        <div class="tab-pane fade" id="tab-out" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody class="small">
                                        @forelse($outOfStockProducts as $p)
                                            <tr>
                                                <td class="ps-0 py-2.5">
                                                    <div class="fw-semibold text-dark">{{ $p->name }}</div>
                                                    <div class="text-secondary" style="font-size: 0.72rem;">{{ $p->sku }}</div>
                                                </td>
                                                <td class="text-end fw-bold text-danger py-2.5">
                                                    0 {{ $p->unit }}
                                                </td>
                                                <td class="text-end pe-0 py-2.5">
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill">Habis</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-3 text-secondary">Tidak ada produk dengan stok habis.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top mt-3 text-end">
                    <span class="text-secondary small"><i class="bi bi-shield-check me-1"></i> Data sinkron dengan ledger inventaris</span>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 3: TIGA CARD GRID (Transaksi Terkini | Rekomendasi Restock | Stok Habis) --}}
    <div class="row g-3">
        {{-- Card 1: Transaksi Terkini --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">Transaksi Terkini</h3>
                    <a href="{{ route('transactions.index') }}" class="text-decoration-none small text-secondary">Semua &rarr;</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentTransactions as $tx)
                        <div class="list-group-item px-0 py-2.5 border-light d-flex align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 32px; height: 32px; background: {{ $tx->type === 'in' ? '#eef9f1' : ($tx->type === 'out' ? '#fdeced' : '#fef7e6') }}; color: {{ $tx->type === 'in' ? '#16a34a' : ($tx->type === 'out' ? '#dc2626' : '#d97706') }}; font-size: 0.85rem;">
                                    <i class="bi {{ $tx->type === 'in' ? 'bi-arrow-down-left' : ($tx->type === 'out' ? 'bi-arrow-up-right' : 'bi-sliders') }}"></i>
                                </div>
                                <div class="text-truncate">
                                    <a href="{{ route('transactions.show', $tx) }}" class="fw-semibold text-dark text-decoration-none small d-block text-truncate">
                                        {{ $tx->product->name ?? 'Produk Dihapus' }}
                                    </a>
                                    <span class="text-secondary" style="font-size: 0.72rem;">
                                        {{ $tx->transaction_date->format('d M') }} &bull; {{ $tx->user->name ?? 'Sistem' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="fw-bold small {{ $tx->type === 'in' ? 'text-success' : ($tx->type === 'out' ? 'text-danger' : 'text-warning-emphasis') }}">
                                    {{ $tx->type === 'in' ? '+' : ($tx->type === 'out' ? '-' : '±') }}{{ number_format($tx->quantity) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-secondary small">Belum ada transaksi mutasi.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Card 2: Rekomendasi Restock (Berbasis Aturan: 2x min_stock - stock) --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">Rekomendasi Restock</h3>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill" style="font-size: 0.72rem;">Kalkulasi Buffer</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($restockRecommendations as $rp)
                        <div class="list-group-item px-0 py-2.5 border-light d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold text-dark small">{{ $rp->name }}</div>
                                <div class="text-secondary" style="font-size: 0.72rem;">
                                    Sisa: <strong class="text-danger">{{ $rp->stock }}</strong> &bull; Min: {{ $rp->min_stock }} {{ $rp->unit }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-dark rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    +{{ $rp->suggested_order }} {{ $rp->unit }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-secondary small">Seluruh stok produk berada di atas batas minimum.</div>
                    @endforelse
                </div>
                <div class="mt-auto pt-2 text-muted" style="font-size: 0.7rem;">
                    *Saran order dihitung berbasis aturan: target 2&times; stok minimum dikurangi stok saat ini.
                </div>
            </div>
        </div>

        {{-- Card 3: Stok Habis & Tombol Aksi Cepat --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">Perhatian Stok Habis</h3>
                    <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 0.72rem;">Kritis</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($outOfStockProducts as $op)
                        <div class="list-group-item px-0 py-2.5 border-light d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold text-dark small">{{ $op->name }}</div>
                                <div class="text-secondary" style="font-size: 0.72rem;">{{ $op->sku }}</div>
                            </div>
                            <div>
                                <a href="{{ route('stock.in', ['product_id' => $op->id]) }}" class="btn btn-sm btn-dark gk-btn-pill px-3 py-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-secondary small">
                            <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>
                            Tidak ada produk dengan stok 0 unit.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const chartData = @json($chartData);
        const ctx = document.getElementById('stockTrendChart');

        if (ctx && chartData) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Stok Masuk',
                            data: chartData.stockIn,
                            borderColor: '#b5cd28',
                            backgroundColor: 'rgba(216, 242, 58, 0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#b5cd28',
                        },
                        {
                            label: 'Stok Keluar',
                            data: chartData.stockOut,
                            borderColor: '#27272a',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            borderDash: [4, 4],
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#27272a',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#18181b',
                            titleFont: { family: 'Outfit', size: 12 },
                            bodyFont: { family: 'Outfit', size: 12 },
                            cornerRadius: 8,
                            padding: 10,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Outfit', size: 11 }, color: '#71717a' }
                        },
                        y: {
                            grid: { color: '#f1f1f0' },
                            ticks: { 
                                font: { family: 'Outfit', size: 11 }, 
                                color: '#71717a',
                                precision: 0 
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
