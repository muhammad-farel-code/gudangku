@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('page_actions')
    <a href="{{ url('/products') }}" class="btn-gk-dark">
        <span>Tambah Produk</span>
        <div class="btn-icon-circle">
            <i class="bi bi-plus-lg"></i>
        </div>
    </a>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- BARIS 1: 4 STAT CARDS (Sesuai gaya visual referensi) --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Produk (Dark Card + Lime Icon) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                variant="dark"
                icon="bi-box-seam-fill"
                label="Total Produk"
                value="40"
                trend="+12%"
                :trendUp="true"
                trendText="Sejak bulan lalu"
            />
        </div>

        {{-- Card 2: Total Unit Stok (Light Card) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                variant="light"
                icon="bi-layers-fill"
                iconBg="#eef9f1"
                iconColor="var(--gk-success)"
                label="Total Unit Stok"
                value="812"
                trend="+15%"
                :trendUp="true"
                trendText="Stok mencukupi"
            />
        </div>

        {{-- Card 3: Nilai Persediaan (Admin/Manager) atau Transaksi Hari Ini (Staff) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            @if(Auth::check() && !Auth::user()->isStaff())
                <x-stat-card
                    variant="light"
                    icon="bi-cash-stack"
                    iconBg="#fef7e6"
                    iconColor="#d97706"
                    label="Nilai Persediaan"
                    value="Rp 42.85M"
                    trend="+8%"
                    :trendUp="true"
                    trendText="Estimasi aset"
                />
            @else
                <x-stat-card
                    variant="light"
                    icon="bi-arrow-left-right"
                    iconBg="#eff6ff"
                    iconColor="var(--gk-info)"
                    label="Aktivitas Transaksi"
                    value="24"
                    trend="+5"
                    :trendUp="true"
                    trendText="Pergerakan barang"
                />
            @endif
        </div>

        {{-- Card 4: Stok Menipis dengan SVG Half-Circle Gauge --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                variant="light"
                icon="bi-exclamation-triangle-fill"
                iconBg="#fdeced"
                iconColor="var(--gk-danger)"
                label="Stok Menipis"
                value="10 Item"
                trend="25%"
                :trendUp="false"
                trendText="Perlu Restock"
                :gauge="25"
                gaugeSub="Menipis"
            />
        </div>
    </div>

    {{-- BARIS 2: DUA PANEL KONTEN (Grafik Volume & Tabel Mini Kategori) --}}
    <div class="row g-3 mb-4">
        {{-- Kiri: Placeholder Grafik Stok Masuk vs Keluar --}}
        <div class="col-12 col-xl-7">
            <div class="gk-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="gk-card-title">Volume Stok Masuk vs Keluar</h2>
                        <span class="text-muted small">Tren pergerakan unit 14 hari terakhir</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--gk-accent);"></span>
                            <span class="small text-muted">Stok Masuk</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #27272a;"></span>
                            <span class="small text-muted">Stok Keluar</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas Chart.js -->
                <div style="height: 260px; position: relative;">
                    <canvas id="stockTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Kanan: Preview Tabel & Chips Filter --}}
        <div class="col-12 col-xl-5">
            <div class="gk-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="gk-card-title">Kategori & Peringatan Stok</h2>
                        <a href="{{ url('/products') }}" class="text-decoration-none small text-muted">Lihat Semua</a>
                    </div>

                    <!-- Filter Chips / Tabs -->
                    <div class="gk-chips-container mb-3">
                        <button type="button" class="gk-chip active">Semua</button>
                        <button type="button" class="gk-chip">Elektronik</button>
                        <button type="button" class="gk-chip">ATK</button>
                        <button type="button" class="gk-chip">Kemasan</button>
                    </div>

                    <!-- Mini Preview Table -->
                    <div class="table-gk-wrapper">
                        <table class="table-gk">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gk-thumb-avatar">EL</div>
                                            <div>
                                                <div class="fw-semibold text-dark small">Timbangan Digital 150kg</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">ELK-0002</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold">4 unit</td>
                                    <td><x-status-badge status="Menipis" /></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gk-thumb-avatar">MK</div>
                                            <div>
                                                <div class="fw-semibold text-dark small">Biskuit Roma Kelapa 300g</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">MKN-0003</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger">0 kaleng</td>
                                    <td><x-status-badge status="Habis" /></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gk-thumb-avatar">PK</div>
                                            <div>
                                                <div class="fw-semibold text-dark small">Lakban Bening 2 Inch</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">PKG-0001</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold">120 roll</td>
                                    <td><x-status-badge status="Aman" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-3 border-top mt-3 text-end">
                    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Data riil terhubung di Fase 6</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('stockTrendChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['16 Okt', '18 Okt', '20 Okt', '22 Okt', '24 Okt', '26 Okt', '28 Okt'],
                    datasets: [
                        {
                            label: 'Stok Masuk',
                            data: [45, 60, 52, 78, 65, 92, 85],
                            borderColor: '#b5cd28',
                            backgroundColor: 'rgba(216, 242, 58, 0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#b5cd28',
                        },
                        {
                            label: 'Stok Keluar',
                            data: [30, 42, 38, 45, 55, 60, 50],
                            borderColor: '#27272a',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            borderDash: [4, 4],
                            pointRadius: 3,
                            pointBackgroundColor: '#27272a',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Outfit', size: 11 }, color: '#71717a' }
                        },
                        y: {
                            grid: { color: '#f1f1f0' },
                            ticks: { font: { family: 'Outfit', size: 11 }, color: '#71717a' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
